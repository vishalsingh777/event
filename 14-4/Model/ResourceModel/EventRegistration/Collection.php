<?php
namespace Vishal\Events\Model\ResourceModel\EventRegistration;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Vishal\Events\Model\EventRegistration as EventRegistrationModel;
use Vishal\Events\Model\ResourceModel\EventRegistration as EventRegistrationResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'registration_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            EventRegistrationModel::class,
            EventRegistrationResourceModel::class
        );
    }
    
    /**
     * Add event filter to collection
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
     * Add status filter to collection
     *
     * @param string|array $status
     * @return $this
     */
    public function addStatusFilter($status)
    {
        $this->addFieldToFilter('status', $status);
        return $this;
    }
    
    /**
     * Add email filter to collection
     *
     * @param string $email
     * @return $this
     */
    public function addEmailFilter($email)
    {
        $this->addFieldToFilter('email', $email);
        return $this;
    }
    
    /**
     * Add date filter to collection
     *
     * @param string $date
     * @return $this
     */
    public function addDateFilter($date)
    {
        $this->addFieldToFilter('selected_date', $date);
        return $this;
    }
}