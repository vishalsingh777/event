<?php
namespace Vishal\Events\Controller\Registration;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Vishal\Events\Model\EventRegistrationFactory;
use Vishal\Events\Model\ResourceModel\EventRegistration as EventRegistrationResource;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\OrderService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Message\ManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Process extends Action
{
    /**
     * @var FormKeyValidator
     */
    protected $formKeyValidator;

    /**
     * @var EventRegistrationFactory
     */
    protected $eventRegistrationFactory;

    /**
     * @var EventRegistrationResource
     */
    protected $eventRegistrationResource;

    /**
     * @var EventFactory
     */
    protected $eventFactory;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param Context $context
     * @param FormKeyValidator $formKeyValidator
     * @param EventRegistrationFactory $eventRegistrationFactory
     * @param EventRegistrationResource $eventRegistrationResource
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderService $orderService
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        FormKeyValidator $formKeyValidator,
        EventRegistrationFactory $eventRegistrationFactory,
        EventRegistrationResource $eventRegistrationResource,
        EventFactory $eventFactory,
        EventResource $eventResource,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
        OrderService $orderService,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository
    ) {
        $this->formKeyValidator = $formKeyValidator;
        $this->eventRegistrationFactory = $eventRegistrationFactory;
        $this->eventRegistrationResource = $eventRegistrationResource;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
        $this->orderService = $orderService;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $isAjax = $this->getRequest()->isXmlHttpRequest();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        
        if ($isAjax) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        }
        
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            if ($isAjax) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('Invalid form key. Please try again.')
                ]);
            }
            
            $this->messageManager->addErrorMessage(__('Invalid form key. Please try again.'));
            return $resultRedirect->setPath('*/*/');
        }
        
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            if ($isAjax) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('No data provided.')
                ]);
            }
            
            $this->messageManager->addErrorMessage(__('No data provided.'));
            return $resultRedirect->setPath('*/*/');
        }
        
        try {
            // Get event information
            $eventId = $data['event_id'] ?? 0;
            $event = $this->eventFactory->create();
            $this->eventResource->load($event, $eventId);
            
            if (!$event->getId()) {
                if ($isAjax) {
                    return $resultJson->setData([
                        'success' => false,
                        'message' => __('Event not found.')
                    ]);
                }
                
                $this->messageManager->addErrorMessage(__('Event not found.'));
                return $resultRedirect->setPath('events');
            }
            
            // Check event status
            if ($event->getStatus() != 1) {
                if ($isAjax) {
                    return $resultJson->setData([
                        'success' => false,
                        'message' => __('Registration is not available for this event.')
                    ]);
                }
                
                $this->messageManager->addErrorMessage(__('Registration is not available for this event.'));
                return $resultRedirect->setPath('events/event/view', ['id' => $eventId]);
            }
            
            // Check if sold out
            if ($event->getQty() !== null && $event->getQty() <= 0) {
                if ($isAjax) {
                    return $resultJson->setData([
                        'success' => false,
                        'message' => __('Sorry, this event is sold out.')
                    ]);
                }
                
                $this->messageManager->addErrorMessage(__('Sorry, this event is sold out.'));
                return $resultRedirect->setPath('events/event/view', ['id' => $eventId]);
            }
            
            // Get registration type
            $registrationType = $data['registration_type'] ?? 0;
            
            // Create registration record
            $registration = $this->eventRegistrationFactory->create();
            $registration->setEventId($eventId);
            $registration->setFirstName($data['first_name']);
            $registration->setLastName($data['last_name']);
            $registration->setEmail($data['email']);
            $registration->setStreet($data['street']);
            $registration->setCity($data['city']);
            $registration->setCountry($data['country']);
            $registration->setZipcode($data['zipcode']);
            $registration->setCreatedAt(date('Y-m-d H:i:s'));
            
            // Set approval status based on registration type
            if ($registrationType == 2) { // Register with Approval
                $registration->setStatus('pending');
            } else { // Register Only
                $registration->setStatus('approved');
            }
            
            // Save selected time slot information
            if (isset($data['selected_date'])) {
                $registration->setSelectedDate($data['selected_date']);
            } 
            
            // Get time slot information from form
            $selectedTimeSlot = isset($data['selected_time_slot']) ? $data['selected_time_slot'] : null;
            if ($selectedTimeSlot) {
                $registration->setSelectedTimeSlot($selectedTimeSlot);
                
                // Get time slot details if available
                $timeSlots = $this->getTimeSlots($event, $data['selected_date'] ?? null);
                if (!empty($timeSlots) && isset($timeSlots[$selectedTimeSlot])) {
                    $slot = $timeSlots[$selectedTimeSlot];
                    if (isset($slot['time_start'])) {
                        $registration->setSelectedTimeStart($slot['time_start']);
                    }
                    if (isset($slot['time_end'])) {
                        $registration->setSelectedTimeEnd($slot['time_end']);
                    }
                }
            }
            
            $this->eventRegistrationResource->save($registration);
            
            // Create order programmatically for "Register Only" type
            $orderId = null;
            $orderIncrementId = null;
            
            if ($registrationType == 1) {
                try {
                    $order = $this->createOrderProgrammatically($event, $registration);
                    if ($order && $order->getId()) {
                        $orderId = $order->getId();
                        $orderIncrementId = $order->getIncrementId();
                        
                        // Update registration with order ID
                        $registration->setOrderId($order->getId());
                        $this->eventRegistrationResource->save($registration);
                    }
                    
                    // Decrement event quantity
                    if ($event->getQty() !== null) {
                        $event->setQty($event->getQty() - 1);
                        $this->eventResource->save($event);
                    }
                } catch (\Exception $e) {
                    $this->logger->critical('Order creation failed: ' . $e->getMessage());
                }
            }
            
            // Prepare success response for AJAX or redirect
            if ($isAjax) {
                $responseData = [
                    'success' => true,
                    'message' => __('Registration successful!'),
                    'registration_id' => $registration->getId(),
                    'redirect' => $this->_url->getUrl('events/registration/success', ['registration_id' => $registration->getId()])
                ];
                
                // Add order information if applicable
                if ($orderId) {
                    $responseData['order_id'] = $orderId;
                    $responseData['order_increment_id'] = $orderIncrementId;
                }
                
                return $resultJson->setData($responseData);
            }
            
            // Standard redirect for non-AJAX requests
            return $resultRedirect->setPath('events/registration/success', ['registration_id' => $registration->getId()]);
            
        } catch (\Exception $e) {
            $this->logger->critical($e);
            
            if ($isAjax) {
                return $resultJson->setData([
                    'success' => false,
                    'message' => __('An error occurred during registration. Please try again or contact us.')
                ]);
            }
            
            $this->messageManager->addErrorMessage(
                __('An error occurred during registration. Please try again or contact us.')
            );
            return $resultRedirect->setPath('events/event/view', ['id' => $eventId]);
        }
    }
    
/**
 * Create an order programmatically
 *
 * @param \Vishal\Events\Model\Event $event
 * @param \Vishal\Events\Model\EventRegistration $registration
 * @return \Magento\Sales\Model\Order|null
 */
private function createOrderProgrammatically($event, $registration)
{
    try {
        // Set status to "processing_order"
        $registration->setStatus('processing_order');
        $this->eventRegistrationResource->save($registration);
        
        // Get Object Manager instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        // Get necessary factories
        $quoteFactory = $objectManager->get('\Magento\Quote\Model\QuoteFactory');
        $paymentFactory = $objectManager->get('\Magento\Quote\Model\Quote\PaymentFactory');
        $addressFactory = $objectManager->get('\Magento\Quote\Model\Quote\AddressFactory');
        $cartManagement = $objectManager->get('\Magento\Quote\Api\CartManagementInterface');
        $cartRepository = $objectManager->get('\Magento\Quote\Api\CartRepositoryInterface');
        
        // Get store info
        $store = $this->storeManager->getStore();
        $storeId = $store->getId();
        
        // Create new quote
        $quote = $quoteFactory->create();
        $quote->setStore($store);
        $quote->setStoreId($storeId);
        $quote->setCurrencyCode($store->getCurrentCurrencyCode());
        
        // Set customer info
        if ($registration->getEmail()) {
            $quote->setCustomerEmail($registration->getEmail());
        }
        $quote->setCustomerFirstname($registration->getFirstName());
        $quote->setCustomerLastname($registration->getLastName());
        $quote->setCustomerIsGuest(true);
        
        // Get product for the event
        $product = null;
        try {
            if ($event->getProductId()) {
                $product = $this->productRepository->getById($event->getProductId());
            } elseif ($event->getProductSku()) {
                $product = $this->productRepository->get($event->getProductSku());
            } else {
                $registration->setStatus('order_failed_no_product');
                $this->eventRegistrationResource->save($registration);
                throw new \Exception('No product associated with this event');
            }
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_product_load');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Create buy request with event details
        $buyRequest = new \Magento\Framework\DataObject([
            'product' => $product->getId(),
            'event_id' => $event->getId(),
            'registration_id' => $registration->getId(),
            'qty' => 1
        ]);
        
        // Add selected date and time if available
        if ($registration->getSelectedDate()) {
            $buyRequest->setData('selected_date', $registration->getSelectedDate());
        }
        
        if ($registration->getSelectedTimeSlot()) {
            $buyRequest->setData('selected_time_slot', $registration->getSelectedTimeSlot());
        }
        
        // Add product to quote
        try {
            $result = $quote->addProduct($product, $buyRequest);
            if (is_string($result)) {
                $registration->setStatus('order_failed_add_product');
                $this->eventRegistrationResource->save($registration);
                throw new \Exception($result);
            }
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_add_product_exception');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Create and set billing/shipping address
        try {
            $address = $addressFactory->create();
            $address->setFirstname($registration->getFirstName())
                ->setLastname($registration->getLastName())
                ->setStreet($registration->getStreet())
                ->setCity($registration->getCity())
                ->setCountryId($registration->getCountry())
                ->setPostcode($registration->getZipcode())
                ->setTelephone($registration->getTelephone() ?? '0000000000') // Fallback if telephone is required
                ->setEmail($registration->getEmail());
            
            // Set addresses to quote
            $quote->setBillingAddress($address);
            
            // Only set shipping address for non-virtual products
            if (!$product->getIsVirtual()) {
                $quote->setShippingAddress($address);
            }
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_address');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Set payment method
        try {
            $payment = $paymentFactory->create();
            
            // Check payment methods availability
            $paymentMethod = 'free';
            $paymentMethodInstance = $objectManager->get('\Magento\Payment\Helper\Data')->getMethodInstance($paymentMethod);
            if (!$paymentMethodInstance || !$paymentMethodInstance->isAvailable()) {
                $paymentMethod = 'checkmo'; // Try money order as fallback
                
                $paymentMethodInstance = $objectManager->get('\Magento\Payment\Helper\Data')->getMethodInstance($paymentMethod);
                if (!$paymentMethodInstance || !$paymentMethodInstance->isAvailable()) {
                    $paymentMethod = 'cashondelivery'; // Try cash on delivery as second fallback
                }
            }
            
            $payment->setMethod($paymentMethod);
            $quote->setPayment($payment);
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_payment_method');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Make sure totals are collected
        try {
            $quote->collectTotals();
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_collect_totals');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Enable order confirmation
        $quote->setSendConfirmation(1);
        
        // Save quote
        try {
            $cartRepository->save($quote);
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_save_quote');
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Make sure quote is active
        if (!$quote->getIsActive()) {
            $quote->setIsActive(true);
            $cartRepository->save($quote);
        }
        
        // Place order
        try {
            $orderId = $cartManagement->placeOrder($quote->getId());
            
            if ($orderId) {
                $order = $objectManager->get('\Magento\Sales\Model\OrderRepository')->get($orderId);
                
                // Get the Order State constants
                $orderStateComplete = \Magento\Sales\Model\Order::STATE_COMPLETE;
                $orderStatusComplete = 'complete'; // This is typically the status code for complete state
                
                // Set order state and status
                $order->setState($orderStateComplete)
                    ->setStatus($orderStatusComplete);
                
                // Create a status history comment
                $statusHistoryFactory = $objectManager->get('\Magento\Sales\Api\Data\OrderStatusHistoryInterfaceFactory');
                $comment = $statusHistoryFactory->create();
                
                // Set the comment details
                $comment->setParentId($orderId)
                    ->setComment(__('Order created and automatically completed for event registration #%1', $registration->getId()))
                    ->setEntityName('order')
                    ->setStatus($orderStatusComplete)
                    ->setIsCustomerNotified(false)
                    ->setIsVisibleOnFront(false);
                
                // Add the comment to the order
                $order->addStatusHistory($comment);
                
                // Save the order
                $objectManager->get('\Magento\Sales\Api\OrderRepositoryInterface')->save($order);
                
                // Update registration with order ID and set status to success
                $registration->setOrderId($orderId);
                $registration->setStatus('approved_with_order');
                $this->eventRegistrationResource->save($registration);
                
                return $order;
            } else {
                $registration->setStatus('order_failed_null_id');
                $this->eventRegistrationResource->save($registration);
            }
        } catch (\Exception $e) {
            $registration->setStatus('order_failed_place_order');
            $registration->setOrderFailReason($e->getMessage());
            $this->eventRegistrationResource->save($registration);
            throw $e;
        }
        
        // Set status to indicate order creation failed but registration was successful
        $registration->setStatus('registration_only_order_failed');
        $this->eventRegistrationResource->save($registration);
        
        return null;
    } catch (\Exception $e) {
        // Set final failure status if not already set
        if ($registration->getStatus() !== 'processing_order') {
            $registration->setStatus('order_creation_failed');
            $registration->setOrderFailReason($e->getMessage());
            $this->eventRegistrationResource->save($registration);
        }
        
        // Continue with registration even if order creation fails
        throw $e;
    }
}
    
    /**
     * Get time slots for an event
     *
     * @param \Vishal\Events\Model\Event $event
     * @param string|null $selectedDate
     * @return array
     */
    private function getTimeSlots($event, $selectedDate = null)
    {
        $timeSlots = [];
        try {
            $timeSlotData = $event->getTimeSlots();
            
            // Check if timeSlots is already an array
            if (is_array($timeSlotData)) {
                $timeSlots = $timeSlotData;
            } 
            // Check if it's a JSON string that needs to be decoded
            else if (is_string($timeSlotData) && !empty($timeSlotData)) {
                $decoded = json_decode($timeSlotData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $timeSlots = $decoded ?: [];
                }
            }
        } catch (\Exception $e) {
            // Silently handle error
        }
        
        return $timeSlots;
    }
}