<?php
namespace Vishal\Events\Plugin;

use Magento\Checkout\Model\Session;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class CheckoutSessionPlugin
{
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var EventResource
     */
    protected $eventResource;
    
    /**
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     */
    public function __construct(
        EventFactory $eventFactory,
        EventResource $eventResource
    ) {
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
    }
    
    /**
     * Update cart prices after the items are loaded for the session
     *
     * @param Session $subject
     * @param Quote $result
     * @return Quote
     */
    public function afterGetQuote(Session $subject, Quote $result)
    {
        foreach ($result->getAllVisibleItems() as $item) {
            // Refresh custom prices from the event
            $this->updateItemFromEvent($item);
        }
        
        return $result;
    }
    
    /**
     * Update quote item from event data
     *
     * @param Item $item
     * @return void
     */
    private function updateItemFromEvent(Item $item)
    {
        $buyRequest = $item->getBuyRequest();
        
        // Only process items with an event ID
        if ($buyRequest && $buyRequest->getEventId()) {
            $eventId = $buyRequest->getEventId();
            
            try {
                $event = $this->eventFactory->create();
                $this->eventResource->load($event, $eventId);
                
                if ($event && $event->getId()) {
                    // Reapply event price to ensure it's always up to date
                    if ($event->getEventPrice()) {
                        $item->setCustomPrice($event->getEventPrice());
                        $item->setOriginalCustomPrice($event->getEventPrice());
                        $item->getProduct()->setIsSuperMode(true);
                    }
                }
            } catch (\Exception $e) {
                // Log error but continue processing
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
                $logger->error('Error updating event item in cart: ' . $e->getMessage());
            }
        }
    }
}