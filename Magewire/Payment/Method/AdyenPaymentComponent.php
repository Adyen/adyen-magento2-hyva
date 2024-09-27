<?php

declare(strict_types=1);

namespace Adyen\Hyva\Magewire\Payment\Method;

use Adyen\Hyva\Api\ProcessingMetadataInterface;
use Adyen\Hyva\Model\Component\Payment\Context;
use Adyen\Hyva\Model\Configuration;
use Adyen\Hyva\Model\Customer\CustomerGroupHandler;
use Adyen\Hyva\Model\PaymentMethod\PaymentMethods;
use Adyen\Payment\Api\AdyenOrderPaymentStatusInterface;
use Adyen\Payment\Api\AdyenPaymentsDetailsInterface;
use Adyen\Payment\Gateway\Request\HeaderDataBuilder;
use Adyen\Payment\Helper\StateData;
use Adyen\Payment\Helper\Util\CheckoutStateDataValidator;
use Hyva\Checkout\Model\Magewire\Component\Evaluation\EvaluationResult;
use Hyva\Checkout\Model\Magewire\Component\EvaluationInterface;
use Hyva\Checkout\Model\Magewire\Component\EvaluationResultFactory;
use Magento\Checkout\Api\GuestPaymentInformationManagementInterface;
use Magento\Checkout\Api\PaymentInformationManagementInterface;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;
use Magewirephp\Magewire\Component;
use Psr\Log\LoggerInterface;

abstract class AdyenPaymentComponent extends Component implements EvaluationInterface
{
    const REQUIRED_ADDRESS_FIELDS = [
        'city',
        'country_id',
        'postcode',
        'street',
        'firstname',
        'lastname',
        'telephone'
    ];

    public bool $requiresShipping = true;
    public ?string $paymentResponse = null;
    public ?string $paymentStatus = null;
    public ?string $paymentDetails = null;
    public ?string $billingAddress = null;
    public ?string $shippingAddress = null;

    protected CheckoutStateDataValidator $checkoutStateDataValidator;
    protected Configuration $configuration;
    protected Session $session;
    protected StateData $stateData;
    protected PaymentMethods $paymentMethods;
    protected PaymentInformationManagementInterface $paymentInformationManagement;
    protected GuestPaymentInformationManagementInterface $guestPaymentInformationManagement;
    protected QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedQuoteId;
    protected AdyenOrderPaymentStatusInterface $adyenOrderPaymentStatus;
    protected AdyenPaymentsDetailsInterface $adyenPaymentsDetails;
    protected CustomerGroupHandler $customerGroupHandler;
    protected LoggerInterface $logger;

    const FRONTENDTYPE_HYVA = 'hyva';

    public function __construct(
        private readonly Context $context
    ) {
        $this->checkoutStateDataValidator = $context->getCheckoutStateDataValidator();
        $this->configuration = $context->getConfiguration();
        $this->session = $context->getSession();
        $this->stateData = $context->getStateData();
        $this->paymentMethods = $context->getPaymentMethods();
        $this->paymentInformationManagement = $context->getPaymentInformationManagement();
        $this->guestPaymentInformationManagement = $context->getGuestPaymentInformationManagement();
        $this->quoteIdToMaskedQuoteId = $this->context->getQuoteIdToMaskedQuoteId();
        $this->adyenOrderPaymentStatus = $context->getAdyenOrderPaymentStatus();
        $this->adyenPaymentsDetails = $context->getAdyenPaymentsDetails();
        $this->customerGroupHandler = $context->getCustomerGroupHandler();
        $this->logger = $context->getLogger();
    }

    protected $listeners = [
    ];

    /**
     * @return string
     */
    abstract function getMethodCode(): string;

    /**
     * {@inheritDoc}
     */
    public function mount(): void
    {
        $this->refreshProperties();
    }

    /**
     * @param array $data
     */
    public function placeOrder(array $data): void
    {
        try {
            $this->handleSessionVariables($data);
            $quoteId = $this->session->getQuoteId();
            $payment = $this->session->getQuote()->getPayment();
            $payment->setAdditionalInformation(HeaderDataBuilder::FRONTENDTYPE, self::FRONTENDTYPE_HYVA);
            $stateDataReceived = $this->collectValidatedStateData($data);
            //Temporary (per request) storage of state data
            $this->stateData->setStateData($stateDataReceived, (int) $quoteId);

            if ($this->userIsGuest()) {
                $email = $this->session->getQuote()->getCustomerEmail();
                $maskedQuoteId = $this->quoteIdToMaskedQuoteId->execute((int) $quoteId);
                $orderId = $this->guestPaymentInformationManagement->savePaymentInformationAndPlaceOrder(
                    $maskedQuoteId,
                    $email,
                    $payment
                );
            } else {
                $orderId = $this->paymentInformationManagement->savePaymentInformationAndPlaceOrder(
                    $quoteId,
                    $payment
                );
            }
            $this->paymentStatus = $this->adyenOrderPaymentStatus->getOrderPaymentStatus(strval($orderId));
        } catch (\Exception $exception) {
            $this->paymentStatus = json_encode(['isRefused' => true]);
            $this->logger->error('Could not place the Adyen order: ' . $exception->getMessage());
        }
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function evaluateCompletion(EvaluationResultFactory $resultFactory): EvaluationResult
    {
        return $resultFactory->createSuccess();
    }

    /**
     * @return bool
     */
    public function userIsGuest(): bool
    {
        return $this->customerGroupHandler->userIsGuest();
    }

    /**
     * @return void
     */
    public function refreshProperties(): void
    {
        $this->processRequiresShipping();
        $this->processPaymentResponse();
        $this->processQuoteAddresses();
    }

    /**
     * @param array $data
     */
    public function collectPaymentDetails(array $data): void
    {
        try {
            $this->paymentDetails = $this->adyenPaymentsDetails->initiate(json_encode($data), $this->session->getLastRealOrder()->getId());
        } catch (\Exception $exception) {
            $this->paymentDetails = json_encode(['isRefused' => true]);
            $this->logger->error('Could not collect payment details: ' . $exception->getMessage());
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
    private function handleSessionVariables(array $data): void
    {
        $this->session->setNumberOfInstallments(null);
        $this->session->setCcType(null);
        $this->processInstallmentsData($data);
    }

    /**
     * @param array $data
     */
    private function processInstallmentsData(array $data): void
    {
        if (isset($data[ProcessingMetadataInterface::POST_KEY_STATE_DATA][ProcessingMetadataInterface::POST_KEY_NUMBER_OF_INSTALLMENTS]['value'])) {
            $this->session->setNumberOfInstallments(
                $data[ProcessingMetadataInterface::POST_KEY_STATE_DATA][ProcessingMetadataInterface::POST_KEY_NUMBER_OF_INSTALLMENTS]['value']
            );
            $this->session->setCcType($data[ProcessingMetadataInterface::POST_KEY_CC_TYPE]);
        }
    }

    private function processRequiresShipping(): void
    {
        try {
            $this->requiresShipping = !$this->session->getQuote()->isVirtual() && !$this->getCurrentShippingMethod();
        } catch (\Exception $e) {
            $this->logger->error('Could not detect if shipping is required: ' . $e->getMessage());
        }
    }

    private function processPaymentResponse(): void
    {
        try {
            $this->paymentResponse = $this->paymentMethods->getData((int) $this->session->getQuoteId());
        } catch (\Exception $e) {
            $this->paymentResponse = '{}';
            $this->logger->error('Could not collect Adyen payment methods response: ' . $e->getMessage());
        }
    }

    private function processQuoteAddresses(): void
    {
        $billingAddress = $this->session->getQuote()->getBillingAddress();
        if (isset($billingAddress)) {
            $this->billingAddress = json_encode($this->filterAddressFields($billingAddress->toArray()));
        }

        $shippingAddress = $this->session->getQuote()->getShippingAddress();
        if (isset($shippingAddress)) {
            $this->shippingAddress = json_encode($this->filterAddressFields($shippingAddress->toArray()));
        }
    }

    private function filterAddressFields(array $address): array
    {
        $filteredAddress = [];

        foreach($address as $fieldName => $value) {
            if (in_array($fieldName, self::REQUIRED_ADDRESS_FIELDS)) {
                $filteredAddress[$fieldName] = $value;
            }
        }

        return $filteredAddress;
    }
}
