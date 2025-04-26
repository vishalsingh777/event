<?php
namespace Insead\Events\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class EventRegistration extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('insead_event_registrations', 'registration_id');
    }
}