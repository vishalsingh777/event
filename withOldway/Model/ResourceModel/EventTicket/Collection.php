<?php
namespace Vishal\Events\Model\ResourceModel\EventTicket;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vishal\Events\Model\EventTicket;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'ticket_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EventTicket::class, EventTicketResource::class);
    }

    /**
     * Add filter by event
     *
     * @param int $eventId
     * @return $this
     */
    public function addEventFilter($eventId)
    {
        if (!empty($eventId)) {
            $this->addFieldToFilter('event_id', $eventId);
        }
        return $this;
    }
}