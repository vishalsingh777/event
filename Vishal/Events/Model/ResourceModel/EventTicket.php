<?php
/**
 * EventTicket.php (Resource Model)
 * Path: app/code/Vishal/Events/Model/ResourceModel/EventTicket.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EventTicket extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('vishal_event_tickets', 'ticket_id');
    }
}

