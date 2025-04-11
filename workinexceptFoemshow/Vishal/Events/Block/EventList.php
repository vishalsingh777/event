<?php
namespace Vishal\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Vishal\Events\Model\Event;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Helper\Data as EventHelper;

class EventList extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @param Context $context
     * @param CollectionFactory $eventCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param EventHelper $eventHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $eventCollectionFactory,
        StoreManagerInterface $storeManager,
        EventHelper $eventHelper,
        array $data = []
    ) {
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->storeManager = $storeManager;
        $this->eventHelper = $eventHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get events
     *
     * @return \Vishal\Events\Model\ResourceModel\Event\Collection
     */
    public function getEvents()
    {
        $storeId = $this->storeManager->getStore()->getId();
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('status', Event::STATUS_ENABLED)
            ->addFilter('store_id', ['finset' => $storeId]) // Filter by store ID
            ->setOrder('start_date', 'ASC');
        
        return $collection;
    }

    /**
     * Get event URL
     *
     * @param Event $event
     * @return string
     */
    public function getEventUrl($event)
    {
        return $this->storeManager->getStore()->getBaseUrl() . $event->getUrlKey();
    }

    /**
     * Format date
     *
     * @param string|null $date
     * @param int $format
     * @param bool $showTime
     * @param string|null $timezone
     * @return string
     */
    public function formatDate($date = null, $format = \IntlDateFormatter::SHORT, $showTime = false, $timezone = null)
    {
        if ($date !== null) {
            return $this->eventHelper->formatDate($date);
        }
        return parent::formatDate($date, $format, $showTime, $timezone);
    }

    /**
     * Format time
     *
     * @param string $date
     * @return string
     */
    public function formatEventTime($date)
    {
        return $this->eventHelper->formatTime($date);
    }
}