<?php

namespace Insead\Stripe\Plugin;

use StripeIntegration\Payments\Helper\Subscriptions;
use StripeIntegration\Payments\Model\Subscription\StartDateFactory;
use StripeIntegration\Payments\Model\Subscription\ScheduleFactory;
use StripeIntegration\Payments\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Insead\Stripe\Model\Subscription\SubscriptionFactory;
use Insead\Stripe\Logger\CustomLogger;

class SubscriptionsPlugin
{
    const XML_PATH_CANCEL_SUBSCRIPTION_DAYS = 'payment/stripe_payments/subscriptions/cancel_subscription_days';

    protected $scopeConfig;
    protected $startDateFactory;
    protected $subscriptionScheduleFactory;
    protected $config;
    protected $subscriptionFactory;
    protected $customLogger;
    
    public function __construct(
        StartDateFactory $startDateFactory,
        ScheduleFactory $subscriptionScheduleFactory,
        Config $config,
        ScopeConfigInterface $scopeConfig,
        SubscriptionFactory $subscriptionFactory,
        CustomLogger $customLogger
    ) {
        $this->startDateFactory = $startDateFactory;
        $this->subscriptionScheduleFactory = $subscriptionScheduleFactory;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->subscriptionFactory = $subscriptionFactory;
        $this->customLogger = $customLogger;
    }

    public function aroundCreateSubscription(
        Subscriptions $subject,
        \Closure $proceed,
        $subscriptionCreationParams,
        $order,
        $profile
    ) { 
        $this->customLogger->info(' **** Start createSubscription around Plugin **** ');
        $recDurationYear = $order->getData('rec_duration_year');
        $recInstallmentFrequency = $order->getData('rec_installment_frequency');

        $this->customLogger->info(' === RecInstallmentFrequency => ' . $recInstallmentFrequency);
        $this->customLogger->info(' === recDurationYear => ' . $recDurationYear);

        /*---- This code for testing purpose remove after some time ---*/
        $recInstallmentFrequency = 'Daily';
        $order->setData('rec_installment_frequency',$recInstallmentFrequency);

        $this->customLogger->info(' === recInstallmentFrequency for testing => ' . $recInstallmentFrequency);

        /*------ End testing purpose code --------------*/

        $subDays = $this->getCancelSubscriptionDays();

        $cancelAtTime = "";

        $this->customLogger->info(' === Subscription Days From admin config --------'.$subDays);

        if ($recInstallmentFrequency == "Yearly") {

            $cancelAtTime = strtotime("+{$recDurationYear} year +{$subDays} days");
            $this->customLogger->info(' === Yearly === => '.$recDurationYear);

        } else if ($recInstallmentFrequency == "Quarterly") {
            
            $recMonths = ($recDurationYear*3*12);
            $cancelAtTime = strtotime("+{$recMonths} months +{$subDays} days");
            $this->customLogger->info(' === Quarterly === => '.$recMonths);

        } else if ($recInstallmentFrequency == "Monthly") {

            $recMonths = ($recDurationYear*12);
            $cancelAtTime = strtotime("+{$recMonths} months +{$subDays} days");
            $this->customLogger->info(' === Monthly === => '.$recDurationYear);

        } else if ($recInstallmentFrequency == "Daily") {
            
            //$recMonths = ($recDurationYear*12);
            $recMonths = ($recDurationYear);
            $totalDays = $recMonths + $subDays;
            $cancelAtTime = strtotime("+{$totalDays} days");
            $this->customLogger->info(' === Daily === => '.$recDurationYear);

        } else {
            $todayDate = date('Y-m-d H:i:s');
            $cancelAtTime = strtotime("+{$todayDate} +{$subDays} days");
            $this->customLogger->info(' -- Else come need to check frquency not set --');
        }

        $hasOneTimePayment = !empty($subscriptionCreationParams['add_invoice_items']);
        $startDateModel = $this->startDateFactory->create()->fromProfile($profile);
        $startDateParams = $startDateModel->getParams($hasOneTimePayment);

        if ($startDateModel->hasPhases()) {
            $schedule = $this->subscriptionScheduleFactory->create([
                'subscriptionCreateParams' => $subscriptionCreationParams,
                'startDate' => $startDateModel,
            ]);

            $this->customLogger->info('----- hasPhases plugin --------');
            $subscription = $schedule->create()->finalize()->getSubscription();
            $order->getPayment()->setAdditionalInformation('subscription_schedule_id', $schedule->getId());
        } else if (!empty($startDateParams)) {
            // Merge start date parameters if available
            $subscriptionCreationParams = array_merge_recursive($subscriptionCreationParams, $startDateParams);
            $subscriptionCreationParams['cancel_at'] = $cancelAtTime;
            $this->customLogger->info(' === array_merge_recursive  ===>'.$cancelAtTime);
            $subscription = $this->config->getStripeClient()->subscriptions->create($subscriptionCreationParams);
        } else {

            if($cancelAtTime) {
                $subscriptionCreationParams['cancel_at'] = $cancelAtTime;
                $this->customLogger->info(' === Else condition here cancel_at set time===> '.$cancelAtTime);
            }            
            $this->customLogger->info(' === Else condition here cancel_at time not set ===> '.$cancelAtTime);
            $subscription = $this->config->getStripeClient()->subscriptions->create($subscriptionCreationParams);            
        }
        
        // Call the original method and perform any additional logic if needed
        $subject->updateSubscriptionEntry($subscription, $order);
        $this->customLogger->info(' --- after all origina updateSubscriptionEntry ----');

        $order->setData('stripe_subscription_id',$subscription['id']); 
        //$liveMode = ($subscription['livemode']) ?  $subscription['livemode'] : '0'; 
        $liveMode = isset($subscription['livemode']) ? '' : 'test';
        $this->customLogger->info(' --- get data from subscription from stripe ----');
        
        // Prepare data to save 
        if($cancelAtTime) {
            $data = [
                'order_id' => $order->getData('increment_id'),
                'customer_id' => $order->getData('customer_id'),
                'subscription_id' => $subscription['id'],
                'customer_fname' => $order->getData('customer_firstname'),
                'customer_lname' => $order->getData('customer_lastname'),
                'customer_email' => $order->getData('customer_email'),
                'rec_installment_frequency' => $recInstallmentFrequency,
                'rec_duration_year' => $order->getData('rec_duration_year'),
                'rec_nb_installment' => $order->getData('rec_nb_installment'),           
                'subscription_startdatetime' => date('Y-m-d H:i:s'),
                'subscription_enddatetime' => date('Y-m-d H:i:s',$cancelAtTime),
                'subscription_status' => $subscription['status'],
                'send_email' => 0
            ];
            $this->customLogger->info(' --- create subscription with end date -- ');
        } else {
            $data = [
                'order_id' => $order->getData('increment_id'),
                'customer_id' => $order->getData('customer_id'),
                'subscription_id' => $subscription['id'],
                'customer_fname' => $order->getData('customer_firstname'),
                'customer_lname' => $order->getData('customer_lastname'),
                'customer_email' => $order->getData('customer_email'),
                'rec_installment_frequency' => $recInstallmentFrequency,
                'rec_duration_year' => $order->getData('rec_duration_year'),
                'rec_nb_installment' => $order->getData('rec_nb_installment'),           
                'subscription_startdatetime' => date('Y-m-d H:i:s'),
                'subscription_status' => $subscription['status'],
                'send_email' => 0
            ];
           $this->customLogger->info(' --- create subscription without end date ---');
        }    

        try {
            $subscriptionStatus = $this->subscriptionFactory->create();
            $subscriptionStatus->setData($data);
            $subscriptionStatus->save();
            $this->customLogger->info(' --- Save data in subscription tables --- ');
        } catch (\Exception $e) {
            $this->customLogger->error(' ### Error saving subscription :- ' . $e->getMessage());
        }
        return $subscription;
    }

    /**
     * Get cancel subscription days from system config
     *
     * @param null|int $storeId
     * @return string|null
     */
    public function getCancelSubscriptionDays($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CANCEL_SUBSCRIPTION_DAYS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
