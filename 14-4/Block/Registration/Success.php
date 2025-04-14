<?php
namespace Vishal\Events\Block\Registration;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Vishal\Events\Helper\Data as EventHelper;
use Magento\Framework\Intl\DateFormatter;

class Success extends Template  
{
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @var \Vishal\Events\Model\EventRegistration
     */
    protected $registration;
    
    /**
     * @var \Vishal\Events\Model\Event
     */
    protected $event;
    
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
     * Set registration
     *
     * @param \Vishal\Events\Model\EventRegistration $registration
     * @return $this
     */
    public function setRegistration($registration)
    {
        $this->registration = $registration;
        return $this;
    }
    
    /**
     * Get registration
     *
     * @return \Vishal\Events\Model\EventRegistration
     */
    public function getRegistration()
    {
        return $this->registration;
    }
    
    /**
     * Set event
     *
     * @param \Vishal\Events\Model\Event $event
     * @return $this
     */
    public function setEvent($event)
    {
        $this->event = $event;
        return $this;
    }
    
    /**
     * Get event
     *
     * @return \Vishal\Events\Model\Event
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
        if ($date && is_string($date)) {
            return $this->eventHelper->formatDate($date);
        }
        return parent::formatDate($date, $format, $showTime, $timezone);
    }
    
    /**
     * Format time
     *
     * @param string|null $time
     * @param int $format
     * @param bool $showDate
     * @return string
     */
    public function formatTime($time = null, $format = \IntlDateFormatter::SHORT, $showDate = false)
    {
        if ($time && is_string($time) && !strtotime($time)) {
            // If it's just a time string like "14:30" without a date
            return $this->eventHelper->formatTime($time);
        }
        return parent::formatTime($time, $format, $showDate);
    }
    
    /**
     * Get event url
     *
     * @return string
     */
    public function getEventUrl()
    {
        if ($this->event && $this->event->getId()) {
            return $this->getUrl('events/event/view', ['id' => $this->event->getId()]);
        }
        return $this->getUrl('events');
    }
    
    /**
     * Get events list url
     *
     * @return string
     */
    public function getEventsUrl()
    {
        return $this->getUrl('events');
    }
    
    /**
     * Get current timestamp for auto-close
     *
     * @return int
     */
    public function getCurrentTimestamp()
    {
        return time();
    }
}