<?php

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Api\AdyenPaymentsDetailsInterface;
use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Helper\Util\CheckoutStateDataValidator;
use Exception;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magewirephp\Magewire\Component;

abstract class AdyenPaymentComponent extends Component implements EvaluationInterface
{
    public ?string $orderId = null;
    public ?string $paymentResponse = null;
    public ?string $paymentStatus = null;
    public ?string $paymentDetails = null;
    public ?string $installmentOptions = null;
    public bool $requiresShipping = true;

    protected CheckoutStateDataValidator $checkoutStateDataValidator;
    protected Configuration $configuration;
    protected Session $session;
    protected StateData $stateData;
    protected PaymentMethods $paymentMethods;
    protected PaymentInformationManagementInterface $paymentInformationManagement;
    protected AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus;
    protected AdyenPaymentsDetailsInterface $adyenPaymentsDetails;

    public function __construct(
        CheckoutStateDataValidator $checkoutStateDataValidator,
        Configuration $configuration,
        Session $session,
        StateData $stateData,
        PaymentMethods $paymentMethods,
        PaymentInformationManagementInterface $paymentInformationManagement,
        AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus,
        AdyenPaymentsDetailsInterface $adyenPaymentsDetails
    ) {
        $this->checkoutStateDataValidator = $checkoutStateDataValidator;
        $this->configuration = $configuration;
        $this->session = $session;
        $this->stateData = $stateData;
        $this->paymentMethods = $paymentMethods;
        $this->paymentInformationManagement = $paymentInformationManagement;
        $this->adyenOrderPaymentStatus = $adyenOrderPaymentStatus;
        $this->adyenPaymentsDetails = $adyenPaymentsDetails;
    }

    /**
     * @return string
     */
    abstract function getMethodCode(): string;

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @return bool
     */
    public function userIsGuest(): bool
    {
        try {
            $customerId = $this->session->getQuote()->getCustomerId();

            if ($customerId) {
                return false;
            }
        } catch (Exception $exception) {
            return true;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function mount(): void
    {
        try {
            $this->processIsShippingRequired();
            $this->paymentResponse = $this->paymentMethods->getData((int) $this->session->getQuoteId());
        } catch (Exception $exception) {
            $this->paymentResponse = '{}';
        }
    }

    /**
     * @param array $data
     * @throws Exception
     */
    public function placeOrder(array $data) {
        try {
            $this->handleSessionVariables($data);
            $quoteId = $this->session->getQuoteId();
            $payment = $this->session->getQuote()->getPayment();
            $stateDataReceived = $this->collectValidatedStateData($data);
            //Temporary (per request) storage of state data
            $this->stateData->setStateData($stateDataReceived, (int) $quoteId);
            $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                $quoteId,
                $payment
            );
            $this->orderId = $orderId;
            $this->paymentStatus = $this->adyenOrderPaymentStatus->getOrderPaymentStatus($this->orderId);
            $this->session->setStateData($stateDataReceived);
        } catch (Exception $exception) {
            $this->paymentStatus = json_encode(['isRefused' => true]);
        }
    }

    /**
     * @param array $data
     */
    public function collectPaymentDetails(array $data)
    {
        try {
            $this->paymentDetails = $this->adyenPaymentsDetails->initiate(json_encode($data), $this->session->getLastRealOrder()->getId());
        } catch (Exception $exception) {
            $this->paymentDetails = json_encode(['isRefused' => true]);
        }
    }

    /**
     * @param array $data
     * @return array
     */
    protected function collectValidatedStateData(array $data): array
    {
        if (isset($data[ProcessingMetadataInterface::POST_KEY_STATE_DATA])) {
            return $this->checkoutStateDataValidator->getValidatedAdditionalData(
                $data[ProcessingMetadataInterface::POST_KEY_STATE_DATA]
            );
        }

        return [];
    }

    public function processQuoteParameters(array $allInstallments)
    {
        $quote = $this->session->getQuote();

        $precision = 2; //@TODO: replace with configuration variable
        $grandTotal = $quote->getGrandTotal();
        $currencyCode = $quote->getQuoteCurrencyCode();
        $installmentsData = [];

        $installmentsData[] = [
            'key' => __('Do not use installments'),
            'value' => ''
        ];

        foreach ($allInstallments as $amount => $installmentOptions) {
            foreach ($installmentOptions as $key => $installment) {
                if ($grandTotal >= $amount) {
                    $dividedAmount = round($grandTotal / $installment, $precision);
                    $dividedString = $installment . " x " . $dividedAmount . " " . $currencyCode;
                    $installmentsData[] = [
                        'key' => $dividedString,
                        'value' => $installment
                    ];

                }
            }
        }

        $this->installmentOptions = json_encode($installmentsData);
    }

    public function processIsShippingRequired()
    {
        if ($this->session->getQuote()->isVirtual()) {
            $this->requiresShipping = false;
        } else {
            if ($shippingMethod = $this->getCurrentShippingMethod()) {
                $this->requiresShipping = false;
            } else {
                $this->requiresShipping = true;
            }
        }
    }

    /**
     * @return string|null
     */
    private function getCurrentShippingMethod(): ?string
    {
        try {
            $shippingAddress = $this->session->getQuote()->getShippingAddress();

            if ($shippingAddress && $shippingAddress->getShippingMethod()) {
                return $shippingAddress->getShippingMethod();
            }
        } catch (\Exception $exception) {
            return null;
        }

        return null;
    }

    /**
     * @param array $data
     */
    private function handleSessionVariables(array $data)
    {
        $this->session->setStateData(null);
        $this->session->setNumberOfInstallments(null);
        $this->session->setCcType(null);
        $this->processInstallmentsData($data);
    }

    /**
     * @param array $data
     */
    private function processInstallmentsData(array $data)
    {
        if (isset($data[ProcessingMetadataInterface::POST_KEY_NUMBER_OF_INSTALLMENTS])
            && $data[ProcessingMetadataInterface::POST_KEY_NUMBER_OF_INSTALLMENTS] != ""
        ) {
            $this->session->setNumberOfInstallments(
                $data[ProcessingMetadataInterface::POST_KEY_NUMBER_OF_INSTALLMENTS]
            );
            $this->session->setCcType($data[ProcessingMetadataInterface::POST_KEY_CC_TYPE]);
        }
    }
}
