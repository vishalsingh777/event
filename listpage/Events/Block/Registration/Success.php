<?php
namespace Insead\Events\Block\Registration;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Insead\Events\Model\EventRegistration;
use Insead\Events\Model\Event;
use Insead\Events\Helper\Data as EventHelper;

class Success extends Template
{
    /**
     * @var EventRegistration
     */
    protected $registration;
    
    /**
     * @var Event
     */
    protected $event;
    
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @param Context $context
     * @param EventHelper $eventHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        EventHelper $eventHelper,
        array $data = []
    ) {
        $this->eventHelper = $eventHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * Set registration data
     *
     * @param EventRegistration $registration
     * @return $this
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
        return $this;
    }
    
    /**
     * Get registration data
     *
     * @return EventRegistration
     */
    public function getRegistration()
    {
        return $this->registration;
    }
    
    /**
     * Set event data
     *
     * @param Event $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }
    
    /**
     * Get event data
     *
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
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
        // You can now call your helper method or parent method as needed
        return $this->eventHelper->formatDate($date, $format, $showTime, $timezone);
    }

    
    /**
     * Format time slot
     *
     * @param string $timeSlot
     * @return string
     */
    public function formatTimeSlot($timeSlot)
    {
        // Get time slots from the event
        $event = $this->getEvent();
        if ($event && $event->getId()) {
            $timeSlots = $this->eventHelper->getTimeSlots($event->getId());
            
            if (!empty($timeSlots) && isset($timeSlots[$timeSlot])) {
                $slot = $timeSlots[$timeSlot];
                if (isset($slot['time_start']) && isset($slot['time_end'])) {
                    return $this->eventHelper->formatTimeRange(
                        $slot['time_start'],
                        $slot['time_end']
                    );
                }
            }
        }
        
        // Fallback if time slot details not found
        return $timeSlot;
    }
}