<?php
/**
 * EventList.php
 * Path: app/code/Vishal/Events/Block/EventList.php
 */

declare(strict_types=1);

namespace Vishal\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Helper\Data as EventHelper;
use Vishal\Events\Model\EventRepository;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;

class EventList extends Template
{
    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @var CollectionFactory
     */
    protected $eventCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param EventHelper $eventHelper
     * @param EventRepository $eventRepository
     * @param CollectionFactory $eventCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        EventHelper $eventHelper,
        EventRepository $eventRepository,
        CollectionFactory $eventCollectionFactory,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->eventHelper = $eventHelper;
        $this->eventRepository = $eventRepository;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Get event helper
     *
     * @return EventHelper
     */
    public function getEventHelper(): EventHelper
    {
        return $this->eventHelper;
    }

    /**
     * Get events
     *
     * @return \Vishal\Events\Model\ResourceModel\Event\Collection
     */
    public function getEvents()
    {
        if (!$this->hasData('events')) {
            $storeId = $this->storeManager->getStore()->getId();
            $collection = $this->eventCollectionFactory->create();
            $collection->addStoreFilter($storeId);
            $collection->addActiveFilter();
            $collection->addFutureEventsFilter();
            $collection->addStartDateOrder();
            
            // Add pagination
            $pageSize = $this->eventHelper->getListItemsPerPage($storeId);
            if ($pageSize > 0) {
                $collection->setPageSize($pageSize);
                $collection->setCurPage($this->getCurrentPage());
            }
            
            $this->setData('events', $collection);
        }
        
        return $this->getData('events');
    }

    /**
     * Get current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return (int)$this->getRequest()->getParam('p', 1);
    }

    /**
     * Get pager HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        $pagerBlock = $this->getChildBlock('pager');
        if ($pagerBlock instanceof \Magento\Theme\Block\Html\Pager) {
            $eventsPerPage = $this->eventHelper->getListItemsPerPage();
            
            $pagerBlock->setAvailableLimit([$eventsPerPage => $eventsPerPage]);
            $pagerBlock->setCollection($this->getEvents());
            $pagerBlock->setShowPerPage(false);
            
            return $pagerBlock->toHtml();
        }
        
        return '';
    }

    /**
     * Get events page URL
     *
     * @return string
     */
    public function getEventsUrl()
    {
        return $this->getUrl($this->eventHelper->getUrlPrefix());
    }
}

