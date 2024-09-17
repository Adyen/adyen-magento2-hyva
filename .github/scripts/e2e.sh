#!/bin/bash

# Base configuration and installation
set -euo pipefail
cd /tmp
git clone https://github.com/Adyen/adyen-integration-tools-tests.git
cd adyen-integration-tools-tests
git checkout "$INTEGRATION_TESTS_BRANCH"
rm -rf package-lock.json
npm i
npx playwright install

echo "Running HYVA E2E Tests."
npm run test:ci:hyva
