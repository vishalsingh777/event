<?php
namespace Vishal\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EventTicket extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vishal_event_tickets', 'ticket_id');
    }
}