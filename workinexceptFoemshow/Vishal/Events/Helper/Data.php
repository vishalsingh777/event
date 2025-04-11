<?php
namespace Vishal\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Vishal\Events\Model\ResourceModel\Event\TimeSlot;

class Data extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var TimeSlot
     */
    protected $timeSlotResource;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TimeSlot $timeSlotResource
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TimeSlot $timeSlotResource
    ) {
        $this->storeManager = $storeManager;
        $this->timeSlotResource = $timeSlotResource;
        parent::__construct($context);
    }

    /**
     * Get event URL
     *
     * @param string $urlKey
     * @return string
     */
    public function getEventUrl($urlKey)
    {
        return $this->storeManager->getStore()->getBaseUrl() . 'events/' . $urlKey;
    }

    /**
     * Format date
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatDate($date, $format = 'M d, Y')
    {
        $dateTime = new \DateTime($date);
        return $dateTime->format($format);
    }

    /**
     * Format time
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    public function formatTime($date, $format = 'h:i A')
    {
        $dateTime = new \DateTime($date);
        return $dateTime->format($format);
    }
    
    /**
     * Get time slots for an event
     *
     * @param int $eventId
     * @return array
     */
    public function getTimeSlots($eventId)
    {
        $timeSlots = $this->timeSlotResource->getTimeSlotsByEventId($eventId);
        
        $formattedSlots = [];
        foreach ($timeSlots as $timeSlot) {
            $formattedSlots[] = [
                'time_start' => $timeSlot['time_start'],
                'time_end' => $timeSlot['time_end']
            ];
        }
        
        return $formattedSlots;
    }
    
    /**
     * Format time range
     * 
     * @param string $startTime Format: "HH:MM"
     * @param string $endTime Format: "HH:MM"
     * @return string
     */
    public function formatTimeRange($startTime, $endTime)
    {
        // Convert 24-hour format to 12-hour format with AM/PM
        $formattedStart = date('g:i A', strtotime($startTime));
        $formattedEnd = date('g:i A', strtotime($endTime));
        
        return $formattedStart . ' - ' . $formattedEnd;
    }
}