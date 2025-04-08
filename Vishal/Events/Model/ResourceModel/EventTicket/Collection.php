<?php
/**
 * Collection.php
 * Path: app/code/Vishal/Events/Model/ResourceModel/EventTicket/Collection.php
 */

declare(strict_types=1);

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
     * Add filter by event id
     *
     * @param int $eventId
     * @return $this
     */
    public function addEventFilter($eventId)
    {
        $this->addFieldToFilter('event_id', $eventId);
        return $this;
    }

    /**
     * Add order by position
     *
     * @param string $dir
     * @return $this
     */
    public function addPositionOrder($dir = 'ASC')
    {
        $this->addOrder('position', $dir);
        return $this;
    }

    /**
     * Filter collection by manual tickets (no product_id)
     *
     * @return $this
     */
    public function addManualTicketsFilter()
    {
        $this->addFieldToFilter('product_id', ['null' => true]);
        return $this;
    }

    /**
     * Filter collection by product tickets (with product_id)
     *
     * @return $this
     */
    public function addProductTicketsFilter()
    {
        $this->addFieldToFilter('product_id', ['notnull' => true]);
        return $this;
    }
}