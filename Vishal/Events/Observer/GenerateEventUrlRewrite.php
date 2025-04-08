<?php
/**
 * Observer/GenerateEventUrlRewrite.php
 * Path: app/code/Vishal/Events/Observer/GenerateEventUrlRewrite.php
 */

declare(strict_types=1);

namespace Vishal\Events\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory;
use Vishal\Events\Helper\Data as EventHelper;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;

class GenerateEventUrlRewrite implements ObserverInterface
{
    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param UrlPersistInterface $urlPersist
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param EventHelper $eventHelper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        UrlPersistInterface $urlPersist,
        UrlRewriteFactory $urlRewriteFactory,
        EventHelper $eventHelper,
        CollectionFactory $collectionFactory
    ) {
        $this->urlPersist = $urlPersist;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->eventHelper = $eventHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Generate URL rewrites for events
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent()->getData('event');
        if ($event) {
            $this->generateForEvent($event);
        } else {
            // If no specific event, regenerate all
            $this->regenerateAllEventRewrites();
        }
    }

    /**
     * Generate URL rewrite for a specific event
     *
     * @param \Vishal\Events\Model\Event $event
     * @return void
     */
    protected function generateForEvent($event)
    {
        $urlPrefix = $this->eventHelper->getUrlPrefix();
        
        // Delete existing rewrites
        $this->deleteEventRewrites($event->getId());
        
        // Create new rewrite
        $urlRewrite = $this->urlRewriteFactory->create()
            ->setEntityType('event')
            ->setEntityId($event->getId())
            ->setRequestPath($urlPrefix . '/' . $event->getUrlKey())
            ->setTargetPath('events/index/view/event_url/' . $event->getUrlKey())
            ->setStoreId(0); // Set 0 for all stores, or loop through store IDs

        $this->urlPersist->replace([$urlRewrite]);
    }

    /**
     * Delete URL rewrites for a specific event
     *
     * @param int $eventId
     * @return void
     */
    protected function deleteEventRewrites($eventId)
    {
        $this->urlPersist->deleteByData([
            UrlRewrite::ENTITY_ID => $eventId,
            UrlRewrite::ENTITY_TYPE => 'event'
        ]);
    }

    /**
     * Regenerate URL rewrites for all events
     *
     * @return void
     */
    protected function regenerateAllEventRewrites()
    {
        $collection = $this->collectionFactory->create();
        foreach ($collection as $event) {
            $this->generateForEvent($event);
        }
    }
}