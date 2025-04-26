<?php
namespace Insead\Events\Model\Event;

use Magento\Framework\Model\AbstractModel;

class TimeSlot extends AbstractModel
{
    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Insead\Events\Model\ResourceModel\Event\TimeSlot::class);
    }
    
    /**
     * Get event ID
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->getData('event_id');
    }
    
    /**
     * Set event ID
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        return $this->setData('event_id', $eventId);
    }
    
    /**
     * Get start time
     *
     * @return string
     */
    public function getTimeStart()
    {
        return $this->getData('time_start');
    }
    
    /**
     * Set start time
     *
     * @param string $timeStart
     * @return $this
     */
    public function setTimeStart($timeStart)
    {
        return $this->setData('time_start', $timeStart);
    }
    
    /**
     * Get end time
     *
     * @return string
     */
    public function getTimeEnd()
    {
        return $this->getData('time_end');
    }
    
    /**
     * Set end time
     *
     * @param string $timeEnd
     * @return $this
     */
    public function setTimeEnd($timeEnd)
    {
        return $this->setData('time_end', $timeEnd);
    }
    
    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder()
    {
        return $this->getData('sort_order');
    }
    
    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData('sort_order', $sortOrder);
    }
}