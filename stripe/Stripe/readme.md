# Insead Stripe Subscriptions Module for Magento 2

Stripe Subscriptions module is used for automatically end recurring subscriptions in Stripe after a specified duration,
So that customers do not continue to be billed once their subscription period has ended.

Namely, it enables the following payment methods:
* StripeIntegration - Payments

## Installation & upgrade

- This module depends on stripe/module-payments, so please ensure that this module is installed first.
- Create a new app/code/Insead/Stripe folder.
- Unzip module in your Magento 2 app/code/Insead/Stripe folder.
- Enable module: php bin/magento module:enable Insead_Stripe
- Upgrade database: php bin/magento setup:upgrade
- Re-run compile command: php bin/magento setup:di:compile
- Update static files by: php bin/magento setup:static-content:deploy [locale]

In order to deactivate the module: php bin/magento module:disable Insead_Stripe

## Configuration

- In Magento 2 administration interface, browse to "STORES > Configuration" menu.
- Click on "Payment Methods" link under the "SALES" section.
- Expand STRIPE payment method and open General Setting tab to enter your Publishable API key and Secret API key credentials.
- Expand the Subscriptions section under the Stripe Billing tab, set the Enabled dropdown to Yes, and select a value for Automatic Cancel Subscription After Days.
- Refresh invalidated Magento cache after config saved.

## Admin Functionality
- This module maintains a record of all Stripe subscription history under the "SALES > Stripe Subscription Status" menu.
- We also display the subscription ID on the sales order grid page.