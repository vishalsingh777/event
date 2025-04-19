<?php

namespace Insead\Stripe\Controller\Webhook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Product;
use Insead\Stripe\Helper\Data as StripeHelper;
use Insead\Stripe\Logger\CustomLogger;
use Insead\Stripe\Helper\Email as EmailHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Index extends Action
{
    protected $stripeHelper;
    protected $customLogger;
    protected $emailHelper;
    protected $dateTime;

    public function __construct(
        Context $context,
        StripeHelper $stripeHelper,
        CustomLogger $customLogger,
        EmailHelper $emailHelper,
        DateTime $dateTime

    ) {
        parent::__construct($context);        
        $this->stripeHelper = $stripeHelper;
        $this->customLogger = $customLogger;
        $this->emailHelper = $emailHelper;
        $this->dateTime = $dateTime;
    }

     /**
     * Execute method for handling Stripe webhook events.
     *
     * This method retrieves the Stripe webhook payload, validates the signature,
     * and processes the subscription update event.
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @throws \UnexpectedValueException
     * @throws \Stripe\Exception\SignatureVerificationException
     */

    public function execute()
    {
        $storeCode = 'give_che';// default "iaa_fra;";
        
        $webhookSecret = $this->stripeHelper->getWebhookSecret($storeCode);

        $payload = @file_get_contents('php://input');
        //$payload = $this->request->getContent();
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        //$signature = $this->request->getHeader('Stripe-Signature') ?? '';
        if (empty($payload) || empty($signature)) {
            throw new \Exception('Missing payload or signature');
        }

        $this->customLogger->info(' *******  Start webhook *********');
        $this->customLogger->info(' Stripe webhookSecret :=> ' . $webhookSecret);
        $this->customLogger->info(' Stripe signature :=> ' . $signature);

        try {            
            
            $event = Webhook::constructEvent($payload, $signature, $webhookSecret);
            $this->customLogger->info('Event Type => ' . $event->type);

            if ($event->type === 'customer.subscription.updated') {
                $subscription = $event->data->object;
                
                if (isset($subscription->items->data[0])) {
                    //$priceId = $subscription->items->data[0]->price->id ?? null;

                    $productId = $subscription->items->data[0]->price->product ?? null;

                    if ($productId) {
                        $product = Product::retrieve($productId);

                        $emailData['product_name'] = $product->name ?? 'N/A';

                        $this->customLogger->info('Product Name: ' . $emailData['product_name']);
                    } else {
                        $this->customLogger->error('Product ID not found in subscription data.');
                    }
                } else {
                    $this->customLogger->error('Subscription items not found.');
                }

                // $this->customLogger->info(' subscription data => '.print_r($subscription,true));

                $this->customLogger->info(' --- Come event :- customer.subscription.updated datetime => '.$this->dateTime->date('Y-m-d H:i:s'));

                if (isset($subscription['customer'])) {

                    $customerId = $subscription['customer'];
                    if (!$customerId) {
                        throw new \Exception('Customer ID not found in subscription data');
                    }
                    $this->customLogger->info('Customer ID => ' . $customerId);

                    $customer = Customer::retrieve($customerId);

                    if ($customer) {
                        $emailData['customer_name'] = $customer->name ?? '';
                        $emailData['email'] = $customer->email ?? '';
                        $this->customLogger->info('Customer Name for stripe : ' . $emailData['customer_name']);
                        $this->customLogger->info('Customer Email for stripe: ' . $emailData['email']);
                    } else {
                        $this->customLogger->error('Customer details not found for ID: ' . $customerId);
                    }
                } else {
                   $this->customLogger->error('Customer ID not found subscription data.');
                }                

                if (isset($subscription['cancel_at'])) {
                   
                    $emailData['cancel_at'] = $this->dateTime->date("Y-m-d", $subscription['cancel_at']);
                    $this->customLogger->error('frequency found => '. $emailData['cancel_at']);
                } else {
                    $this->customLogger->error('cancel_at not found.');
                }

                if (isset($subscription['plan']) && isset($subscription['plan']['interval'])) {
                    
                    $emailData['frequency'] = ucfirst($subscription['plan']['interval']);
                    $this->customLogger->error('frequency found => '. $emailData['frequency']);
                } else {
                    $this->customLogger->error('frequency not found.');
                }

                $this->customLogger->info('Customer data for email => '.print_r($emailData,true));

                 if(!empty($emailData['email'])) {

                    $emailData['giftLink'] = 'https://forceforgood.insead.edu/give';
                    $sendEmail = $this->emailHelper->SendEmail($emailData);
                    $this->customLogger->info('-- Call sendEmail function for customer => ' .$sendEmail);
                 } else {
                    $this->customLogger->info('-- Email not send bcz customer data is empty -- ');
                 } 
            }
            return $this->getResponse()->setBody('Webhook handled successfully');

        } catch (\UnexpectedValueException $e) {
            $this->customLogger->error('Invalid Stripe payload: ' . $e->getMessage());
            return $this->getResponse()->setHttpResponseCode(400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            $this->customLogger->error('Invalid Stripe signature: ' .$e->getMessage());
            return $this->getResponse()->setHttpResponseCode(400);
        }
    }
}
