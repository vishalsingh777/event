<?php
namespace Insead\Events\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Insead\Events\Model\EventRegistrationFactory;
use Insead\Events\Model\ResourceModel\EventRegistration as EventRegistrationResource;
use Insead\Events\Model\EventRegistration;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;
use Insead\Events\Helper\Data as DataHelper;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Message\ManagerInterface;

class OrderPlacedObserver implements ObserverInterface
{
    /**
     * @var EventRegistrationFactory
     */
    protected $registrationFactory;

    /**
     * @var EventRegistrationResource
     */
    protected $registrationResource;
    
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;
    
    /**
     * @var DataHelper
     */
    protected $dataHelper;
    
    /**
     * @var OrderSender
     */
    protected $orderSender;
    
    /**
     * @var ResponseFactory
     */
    protected $responseFactory;
    
    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @var ManagerInterface
    */
    protected $messageManager;

    /**
     * @param EventRegistrationFactory $registrationFactory
     * @param EventRegistrationResource $registrationResource
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     * @param DataHelper $dataHelper
     * @param OrderSender $orderSender
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     */
    public function __construct(
        EventRegistrationFactory $registrationFactory,
        EventRegistrationResource $registrationResource,
        EventFactory $eventFactory,
        EventResource $eventResource,
        Json $jsonSerializer,
        LoggerInterface $logger,
        DataHelper $dataHelper,
        OrderSender $orderSender,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        ManagerInterface $messageManager
    ) {
        $this->registrationFactory = $registrationFactory;
        $this->registrationResource = $registrationResource;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->orderSender = $orderSender;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->messageManager = $messageManager;
    }

    /**
     * Observer for sales_order_place_after
     * Creates new registration records from order information
     * Prevents standard order email from being sent
     * Redirects to event page if this is an event order
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        // Check if module is enabled
        if ($this->dataHelper->isModuleEnabled() === false) {
            $this->logger->info('OrderPlacedObserver: Module is disabled, skipping');
            return;
        }
        
        try {
            $order = $observer->getEvent()->getOrder();
            if (!$order || !$order->getId()) {
                // For checkout success event, we need to get the order from the last order ID
                $orderIds = $observer->getEvent()->getOrderIds();
                if (empty($orderIds) || !isset($orderIds[0])) {
                    $this->logger->info('OrderPlacedObserver: No order found');
                    return;
                }
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $order = $objectManager->create(\Magento\Sales\Model\Order::class)->load($orderIds[0]);
                
                if (!$order || !$order->getId()) {
                    $this->logger->info('OrderPlacedObserver: Unable to load order');
                    return;
                }
            }
            
            $this->logger->info('OrderPlacedObserver: Processing order #' . $order->getIncrementId());
            
            // Prevent standard order email
            $order->setEmailSent(1);
            
            // Process each order item to find event registrations
            $createdRegistrations = [];
            $hasEventItems = false;
            $latestEventId = null;
            
            foreach ($order->getAllItems() as $item) { 
                $this->logger->info('OrderPlacedObserver: Processing item ' . $item->getSku());
                
                $productOptions = $item->getProductOptions();
                
                $this->logger->info('OrderPlacedObserver: Product options: ' . print_r($productOptions, true));
                
                // Check if this item has an event_id
                $eventId = null;
                $isNewEvent = false;
                
                // Try to find event_id in the options
                if (isset($productOptions['info_buyRequest']['event_id'])) {
                    $eventId = $productOptions['info_buyRequest']['event_id'];
                    $this->logger->info('OrderPlacedObserver: Found event_id in info_buyRequest: ' . $eventId);
                    $hasEventItems = true;
                    $latestEventId = $eventId;
                    
                    // Check if this is a new event
                    if (isset($productOptions['info_buyRequest']['is_new_event']) && 
                        $productOptions['info_buyRequest']['is_new_event'] == true) {
                        $isNewEvent = true;
                        $this->logger->info('OrderPlacedObserver: This is marked as a new event');
                    }
                }
                
                if (!$eventId) {
                    $this->logger->info('OrderPlacedObserver: No event_id found for item, skipping');
                    continue;
                }
                
                // Load or create event
                $event = $this->eventFactory->create();
                $this->eventResource->load($event, $eventId);
                
                if (!$event->getId()) {
                    $this->logger->warning('OrderPlacedObserver: No event, skipping');
                    continue;
                }
                
                // Get time slot and date information
                $selectedTimeStart = null;
                $selectedTimeEnd = null;
                $selectedDate = null;
                
                if (isset($productOptions['info_buyRequest']['selected_time_start'])) {
                    $selectedTimeStart = $productOptions['info_buyRequest']['selected_time_start'];
                }
                if (isset($productOptions['info_buyRequest']['selected_time_end'])) {
                    $selectedTimeEnd = $productOptions['info_buyRequest']['selected_time_end'];
                }
                
                if (isset($productOptions['info_buyRequest']['selected_date'])) {
                    $selectedDate = $productOptions['info_buyRequest']['selected_date'];
                }
                
                // Always create a new registration
                $this->logger->info('OrderPlacedObserver: Creating new registration for event #' . $eventId);
                $registration = $this->createNewRegistration($eventId, $order, $selectedTimeStart, $selectedTimeEnd, $selectedDate);
                
                if ($registration) {
                    $createdRegistrations[] = $registration;
                }
            }
            
            // Send confirmation emails for created registrations
            foreach ($createdRegistrations as $registration) {
                // Use the Data helper to send payment confirmation email
                $this->dataHelper->sendPaymentConfirmationEmail($registration, $order);
            }
            
            // Redirect to the event page if this is an event order and we're in the success page context
            if ($hasEventItems && $latestEventId && $observer->getEvent()->getName() == 'checkout_onepage_controller_success_action') {
                try {
                    $this->logger->info('OrderPlacedObserver: Order #' . $order->getIncrementId() . ' contains event items, preparing redirect');
                    
                    // Double check that payment is complete to ensure we don't interrupt any pending processes
                    $payment = $order->getPayment();
                    if (!$payment || $payment->getIsFraudDetected() || $order->getState() === Order::STATE_PAYMENT_REVIEW) {
                        $this->logger->info('OrderPlacedObserver: Skipping redirect for order #' . $order->getIncrementId() . ' due to payment status');
                        return;
                    }
                    
                    // Make sure the order is already saved before redirecting
                    if (!$order->getId()) {
                        $this->logger->info('OrderPlacedObserver: Skipping redirect for order #' . $order->getIncrementId() . ' as order is not yet saved');
                        return;
                    }
                    $this->messageManager->addSuccessMessage(__('Thank you for your purchase! Your order #%1 has been successfully placed.', $order->getIncrementId()));
                    $eventUrlKey = $event->getUrlKey();
                    $eventUrl = $this->url->getUrl('events/' . $eventUrlKey);
                    $this->logger->info('OrderPlacedObserver: Redirecting to event page: ' . $eventUrl);
                    
                    // Use response interface instead of exit to properly handle the request lifecycle
                    $this->responseFactory->create()->setRedirect($eventUrl)->sendResponse();
                    return;
                } catch (\Exception $e) {
                    // Log error but don't interrupt the checkout process
                    $this->logger->error('OrderPlacedObserver: Error during redirect attempt: ' . $e->getMessage());
                    // Continue with normal checkout flow
                    return;
                }
            }
            
        } catch (\Exception $e) {
            $this->logger->error('OrderPlacedObserver Exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            // Don't throw exception to avoid interrupting the order process
        }
    }
    
    
    /**
     * Create a new registration record
     *
     * @param int $eventId
     * @param \Magento\Sales\Model\Order $order
     * @param string|null $selectedTimeStart
     * @param string|null $selectedTimeEnd
     * @param string|null $selectedDate
     * @return EventRegistration|null
     */
    private function createNewRegistration($eventId, $order, $selectedTimeStart = null, $selectedTimeEnd = null, $selectedDate = null)
    {
        try {
            $this->logger->info('OrderPlacedObserver: Creating new registration for event #' . $eventId);
            
            $registration = $this->registrationFactory->create();
            
            // Get customer name parts
            $billingAddress = $order->getBillingAddress();

            $firstname = $billingAddress->getFirstname();
            $lastname = $billingAddress->getLastname(); 
            
            // Set basic registration information
            $registration->setEventId($eventId);
            $registration->setFirstName($firstname);
            $registration->setLastName($lastname);
            $registration->setEmail($order->getCustomerEmail());
            $registration->setStatus(EventRegistration::STATUS_APPROVED);
            $registration->setPaymentStatus(EventRegistration::PAYMENT_STATUS_PAID);
            $registration->setOrderId($order->getIncrementId());
            
            // Set payment information
            $registration->setPaymentMethod($order->getPayment()->getMethod());
            $registration->setPaymentCurrency($order->getOrderCurrencyCode());
            
            // Add address information from order
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress) {
                $registration->setStreet($billingAddress->getStreetLine(1));
                $registration->setCity($billingAddress->getCity());
                $registration->setZipcode($billingAddress->getPostcode());
                $registration->setCountry($billingAddress->getCountryId());
            }
            
            // Set time slot and date if provided
            if ($selectedTimeStart) {
                $registration->setSelectedTimeStart($selectedTimeStart);
            }

            if ($selectedTimeEnd) {
                $registration->setSelectedTimeEnd($selectedTimeEnd);
            }

            if ($selectedTimeEnd && $selectedTimeStart) {
                $timeSlot = $selectedTimeStart. '-'.$selectedTimeEnd;
                $registration->setTimeSlot($timeSlot);
            }
            
            if ($selectedDate) {
                $registration->setSelectedDate($selectedDate);
            }
            
            // Save registration
            $this->registrationResource->save($registration);
            
            $this->logger->info('OrderPlacedObserver: Successfully created registration #' . $registration->getId());
            
            return $registration;
        } catch (\Exception $e) {
            $this->logger->error('OrderPlacedObserver: Error creating new registration: ' . $e->getMessage());
            return null;
        }
    }
}