<?php
namespace Vishal\Events\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Vishal\Events\Helper\Data as EventHelper;
use Magento\Framework\App\ResourceConnection;

class EventView extends Template
{
    /**
     * @var Registry
     */
    protected $coreRegistry;
    
    /**
     * @var EventHelper
     */
    protected $eventHelper;
    
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    
    /**
     * @param Context $context
     * @param Registry $registry
     * @param EventHelper $eventHelper
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        EventHelper $eventHelper,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eventHelper = $eventHelper;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }
    
    /**
     * Retrieve current event
     *
     * @return \Vishal\Events\Model\Event
     */
    public function getEvent()
    {
        return $this->coreRegistry->registry('current_event');
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
        if ($date !== null) {
            return $this->eventHelper->formatDate($date);
        }
        return parent::formatDate($date, $format, $showTime, $timezone);
    }
    
    /**
     * Format time
     *
     * @param string $date
     * @return string
     */
    public function formatEventTime($date)
    {
        return $this->eventHelper->formatTime($date);
    }
    
    /**
     * Get event time zone
     * 
     * @return string
     */
    public function getEventTimeZone()
    {
        $event = $this->getEvent();
        if ($event && $event->getEventTimezone()) {
            return $event->getEventTimezone();
        }
        
        // Default to UTC if not set
        return 'UTC';
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
    
    /**
     * Get formatted time slots for the event
     * 
     * @return array
     */
    public function getFormattedTimeSlots()
    {
        $event = $this->getEvent();
        if (!$event) {
            return [];
        }
        
        // For non-recurring events, use single time slot
        if ((int)$event->getRecurring() === 0) {
            if ($event->getSingleStartTime() && $event->getSingleEndTime()) {
                return [$this->formatTimeRange($event->getSingleStartTime(), $event->getSingleEndTime())];
            }
        }
        
        $timeSlots = $this->getTimeSlots();
        if (empty($timeSlots)) {
            return [];
        }
        
        $formattedSlots = [];
        foreach ($timeSlots as $slot) {
            if (isset($slot['time_start']) && isset($slot['time_end'])) {
                $formattedSlots[] = $this->formatTimeRange($slot['time_start'], $slot['time_end']);
            }
        }
        
        return $formattedSlots;
    }
    
    /**
     * Get time slots from the time_slots table or JSON
     * 
     * @return array
     */
    public function getTimeSlots()
    {
        $event = $this->getEvent();
        if (!$event) {
            return [];
        }
        
        // Try to get from date_time data
        if ($event->getData('date_time') && isset($event->getData('date_time')['data'])) {
            return $event->getData('date_time')['data'];
        }
        
        // Try to get from time_slots field
        $timeSlots = $event->getTimeSlots();
        if (is_string($timeSlots) && !empty($timeSlots)) {
            try {
                $decoded = json_decode($timeSlots, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return $decoded;
                }
            } catch (\Exception $e) {
                // Silent catch - we'll try other methods
            }
        }
        
        // Fetch from database directly if needed
        return $this->getTimeSlotsFromDatabase($event->getId());
    }
    
    /**
     * Get time slots from database
     *
     * @param int $eventId
     * @return array
     */
    private function getTimeSlotsFromDatabase($eventId)
    {
        try {
            $connection = $this->resourceConnection->getConnection();
            $tableName = $this->resourceConnection->getTableName('vishal_event_times');
            
            $select = $connection->select()
                ->from($tableName)
                ->where('event_id = ?', $eventId)
                ->order('sort_order ASC');
            
            $result = $connection->fetchAll($select);
            
            $timeSlots = [];
            foreach ($result as $row) {
                $timeSlots[] = [
                    'time_start' => $row['time_start'],
                    'time_end' => $row['time_end']
                ];
            }
            
            return $timeSlots;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Get raw time slots array
     * 
     * @return array
     */
    public function getRawTimeSlots()
    {
        return $this->getTimeSlots();
    }
    
    /**
     * Get recurrence text description
     *
     * @param \Vishal\Events\Model\Event $event
     * @return string
     */
    public function getRecurrenceText($event)
    {
        if (!$event->getRecurring()) {
            return '';
        }
        
        $repeatType = $event->getRepeat();
        $repeatEvery = (int)$event->getRepeatEvery() ?: 1;
        
        switch ($repeatType) {
            case 'daily':
                return $repeatEvery === 1 
                    ? __('Every day') 
                    : __('Every %1 days', $repeatEvery);
                
            case 'weekly':
                $availableDays = $event->getAvailableDays();
                $dayNames = $this->getDayNames($availableDays);
                
                if (count($dayNames) === 7) {
                    return $repeatEvery === 1 
                        ? __('Every week') 
                        : __('Every %1 weeks', $repeatEvery);
                } else {
                    $daysText = implode(', ', $dayNames);
                    return $repeatEvery === 1 
                        ? __('Weekly on %1', $daysText) 
                        : __('Every %1 weeks on %2', $repeatEvery, $daysText);
                }
                
            case 'monthly':
                return $repeatEvery === 1 
                    ? __('Every month') 
                    : __('Every %1 months', $repeatEvery);
                
            case 'yearly':
                return $repeatEvery === 1 
                    ? __('Every year') 
                    : __('Every %1 years', $repeatEvery);
                
            default:
                return __('Recurring');
        }
    }
    
    /**
     * Get day names from day numbers
     *
     * @param array $dayNumbers
     * @return array
     */
    private function getDayNames($dayNumbers)
    {
        $dayMap = [
            '0' => __('Sunday'),
            '1' => __('Monday'),
            '2' => __('Tuesday'),
            '3' => __('Wednesday'),
            '4' => __('Thursday'),
            '5' => __('Friday'),
            '6' => __('Saturday')
        ];
        
        $names = [];
        foreach ($dayNumbers as $dayNumber) {
            if (isset($dayMap[$dayNumber])) {
                $names[] = $dayMap[$dayNumber];
            }
        }
        
        return $names;
    }
    
    /**
     * Get add to cart URL
     *
     * @param int $productSku
     * @return string
     */
    public function getAddToCartUrl($productSku)
    {
        return $this->getUrl('events/product/addtocart', [
            'product_sku' => $productSku
        ]);
    }
    
    /**
     * Process content HTML
     *
     * @param string $content
     * @return string
     */
    public function getContentHtml($content)
    {
        // This method allows WYSIWYG editor content to be properly displayed
        return $content;
    }
    
    /**
     * Get event price formatted
     *
     * @return string
     */
    public function getFormattedEventPrice()
    {
        $event = $this->getEvent();
        if (!$event || !$event->getEventPrice()) {
            return '';
        }
        
        return $this->priceCurrency->format($event->getEventPrice());
    }
    
    /**
     * Check if event is paid
     *
     * @return bool
     */
    public function isPaidEvent()
    {
        $event = $this->getEvent();
        return $event && $event->getRegistrationType() == '0' && $event->getEventPrice();
    }
    
    /**
     * Get event quantity
     *
     * @return int
     */
    public function getEventQuantity()
    {
        $event = $this->getEvent();
        return $event ? (int)$event->getQty() : 0;
    }
}