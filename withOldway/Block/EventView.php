<?php
namespace Vishal\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory;
use Vishal\Events\Helper\Data as EventHelper;

class EventView extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var CollectionFactory
     */
    protected $ticketCollectionFactory;

    /**
     * @var EventHelper
     */
    protected $eventHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $ticketCollectionFactory
     * @param EventHelper $eventHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $ticketCollectionFactory,
        EventHelper $eventHelper,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->eventHelper = $eventHelper;
        parent::__construct($context, $data);
    }


    /**
     * Retrieve current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('current_event');
    }

    /**
     * Get tickets for current event
     *
     * @return \Vishal\Events\Model\ResourceModel\EventTicket\Collection
     */
    public function getTickets()
    {
        $event = $this->getEvent();
        $collection = $this->ticketCollectionFactory->create();
        $collection->addEventFilter($event->getId())
            ->setOrder('position', 'ASC');
        
        return $collection;
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

    /**
     * Get add to cart URL
     *
     * @param int $ticketId
     * @return string
     */
    public function getAddToCartUrl($ticketId)
    {
        $event = $this->getEvent();
        return $this->getUrl('events/ticket/add', [
            'event_id' => $event->getId(),
            'ticket_id' => $ticketId
        ]);
    }
}