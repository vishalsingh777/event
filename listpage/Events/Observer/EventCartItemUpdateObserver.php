<?php
namespace Insead\Events\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Registry;
use Insead\Events\Helper\Data as EventHelper;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Serialize\Serializer\Json;

class EventCartItemUpdateObserver implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $registry;
    
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
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
     * @param Registry $registry
     * @param EventHelper $eventHelper
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param Json $jsonSerializer
     */
    public function __construct(
        Registry $registry,
        EventHelper $eventHelper,
        EventFactory $eventFactory,
        EventResource $eventResource,
        Json $jsonSerializer
    ) {
        $this->registry = $registry;
        $this->eventHelper = $eventHelper;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->jsonSerializer = $jsonSerializer;
    }
    
    /**
     * Observer for checkout_cart_product_add_after
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $product = $observer->getEvent()->getProduct();
        $buyRequest = $item->getBuyRequest();
        // Check if this is an event product
        if ($buyRequest->getData('event_id')) {
            $eventId = $buyRequest->getData('event_id');
            
            try {
                $event = $this->eventFactory->create();
                $this->eventResource->load($event, $eventId);
                
                if ($event && $event->getId()) {
                    $productTypeInstance = $product->getTypeInstance();
                    $eventName = $event->getEventTitle();
                    $quoteItem = $observer->getQuoteItem();
                    $item->setName($eventName);
                    
                    // Set event information as custom options
                    $additionalOptions = [];
                    
                    // Add event name
                    $additionalOptions[] = [
                        'label' => __('Event'),
                        'value' => $event->getEventTitle(),
                    ];
                    
                    // Add event date
                    if ($buyRequest->getSelectedDate()) {
                        $selectedDate = $buyRequest->getSelectedDate();
                        $formattedDate = $this->eventHelper->formatDate($selectedDate);
                        $additionalOptions[] = [
                            'label' => __('Date'),
                            'value' => $formattedDate,
                        ];
                    } elseif ($event->getStartDate()) {
                        $formattedDate = $this->eventHelper->formatDate($event->getStartDate());
                        $additionalOptions[] = [
                            'label' => __('Date'),
                            'value' => $formattedDate,
                        ];
                    }
                    
                    // Add event time slot if selected
                    if ($buyRequest->getSelectedTimeSlot() !='') {
                        $slotIndex = $buyRequest->getSelectedTimeSlot();
                        
                        $timeSlots = $this->eventHelper->getTimeSlots($eventId);
                        
                        // If time slots are retrieved
                        if (!empty($timeSlots) && isset($timeSlots[$slotIndex])) {
                            $timeSlot = $timeSlots[$slotIndex];
                            if (isset($timeSlot['time_start']) && isset($timeSlot['time_end'])) {
                                $formattedTimeSlot = $this->eventHelper->formatTimeRange(
                                    $timeSlot['time_start'], 
                                    $timeSlot['time_end']
                                );
                                $additionalOptions[] = [
                                    'label' => __('Time Slot'),
                                    'value' => $formattedTimeSlot,
                                ];
                            }
                        }
                    }
                    
                    // Add venue if available
                    if ($event->getEventVenue()) {
                        $additionalOptions[] = [
                            'label' => __('Venue'),
                            'value' => $event->getEventVenue(),
                        ];
                    }
                    
                    // Set event price
                    if ($event->getEventPrice()) {
                        $item->setCustomPrice($event->getEventPrice());
                        $item->setOriginalCustomPrice($event->getEventPrice());
                        $item->getProduct()->setIsSuperMode(true);
                    }
                    
                    // Set additional options
                    if (!empty($additionalOptions)) {
                        $item->addOption([
                            'product_id' => $product->getId(),
                            'code' => 'additional_options',
                            'value' => $this->jsonSerializer->serialize($additionalOptions),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Log the error but allow the cart operation to continue
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
                $logger->error('Error processing event for cart: ' . $e->getMessage());
            }
        }
    }
}