<?php
namespace Vishal\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\CountryFactory;
use Vishal\Events\Model\ResourceModel\Event\TimeSlot;
use Vishal\Events\Model\EventFactory;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Serialize\Serializer\Json;

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
     * @var ResourceConnection
     */
    protected $resourceConnection;
    
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;
    
    /**
     * @var CountryFactory
     */
    protected $countryFactory;
    
    /**
     * @var EventFactory
     */
    protected $eventFactory;
    
    /**
     * @var EventResource
     */
    protected $eventResource;
    
    /**
     * @var Json
     */
    protected $jsonSerializer;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TimeSlot $timeSlotResource
     * @param ResourceConnection $resourceConnection
     * @param PriceCurrencyInterface $priceCurrency
     * @param CountryFactory $countryFactory
     * @param EventFactory $eventFactory
     * @param EventResource $eventResource
     * @param Json $jsonSerializer
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        TimeSlot $timeSlotResource,
        ResourceConnection $resourceConnection,
        PriceCurrencyInterface $priceCurrency,
        CountryFactory $countryFactory,
        EventFactory $eventFactory,
        EventResource $eventResource,
        Json $jsonSerializer
    ) {
        $this->storeManager = $storeManager;
        $this->timeSlotResource = $timeSlotResource;
        $this->resourceConnection = $resourceConnection;
        $this->priceCurrency = $priceCurrency;
        $this->countryFactory = $countryFactory;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->jsonSerializer = $jsonSerializer;
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
    public function formatDate($date, $format = 'F j, Y')
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }

    /**
     * Format time
     *
     * @param string $time
     * @param string $format
     * @return string
     */
    public function formatTime($time, $format = 'g:i A')
    {
        try {
            // Handle both full datetime strings and time-only strings
            if (strpos($time, ':') !== false) {
                if (strlen($time) <= 8) { // Time only (e.g., "14:30:00")
                    return date($format, strtotime("2000-01-01 $time"));
                } else { // Full datetime string
                    return date($format, strtotime($time));
                }
            }
            return $time;
        } catch (\Exception $e) {
            return $time;
        }
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
    
    /**
     * Get country options
     *
     * @return array
     */
    public function getCountryOptions()
    {
        $countryCollection = $this->countryFactory->create()->getResourceCollection();
        $countries = [];
        
        foreach ($countryCollection as $country) {
            $countries[$country->getCountryId()] = $country->getName();
        }
        
        asort($countries);
        return $countries;
    }
    
    /**
     * Get event by ID
     *
     * @param int $eventId
     * @return \Vishal\Events\Model\Event|null
     */
    public function getEventById($eventId)
    {
        try {
            $event = $this->eventFactory->create();
            $this->eventResource->load($event, $eventId);
            
            if ($event->getId()) {
                return $event;
            }
        } catch (\Exception $e) {
            $this->_logger->error('Error loading event: ' . $e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Format price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->priceCurrency->format($price);
    }
    
    /**
     * Get media URL for event images
     *
     * @return string
     */
    public function getMediaUrl()
    {
        try {
            return $this->storeManager->getStore()->getBaseUrl(
                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
            ) . 'events/';
        } catch (\Exception $e) {
            $this->_logger->error('Error getting media URL: ' . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Get event time slots with enhanced formatting
     *
     * @param int $eventId
     * @return array
     */
    public function getEventTimeSlots($eventId)
    {
        try {
            // First try to get from dedicated time slot table using resource
            $timeSlots = $this->timeSlotResource->getTimeSlotsByEventId($eventId);
            
            $formattedSlots = [];
            foreach ($timeSlots as $key => $slot) {
                $formattedSlots[$key] = [
                    'index' => $key,
                    'time_start' => $slot['time_start'],
                    'time_end' => $slot['time_end'],
                    'formatted' => $this->formatTimeRange($slot['time_start'], $slot['time_end'])
                ];
            }
            
            return $formattedSlots;
        } catch (\Exception $e) {
            $this->_logger->error('Error getting event time slots: ' . $e->getMessage());
            
            // Try fallback to event model
            try {
                $event = $this->getEventById($eventId);
                if ($event) {
                    $timeSlotsData = $event->getTimeSlots();
                    if (is_string($timeSlotsData) && !empty($timeSlotsData)) {
                        try {
                            return $this->jsonSerializer->unserialize($timeSlotsData);
                        } catch (\Exception $jsonException) {
                            $this->_logger->error('Error parsing time slots JSON: ' . $jsonException->getMessage());
                        }
                    }
                }
            } catch (\Exception $eventException) {
                $this->_logger->error('Error in fallback time slots retrieval: ' . $eventException->getMessage());
            }
            
            return [];
        }
    }
    
    /**
     * Get formatted time slots with dates
     * 
     * @param int $eventId
     * @param string $selectedDate Optional date to filter slots
     * @return array
     */
    public function getFormattedTimeSlotsWithDates($eventId = null, $selectedDate = null)
    {
        // If no event ID is provided, try to get from registry
        if (!$eventId) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $registry = $objectManager->get(\Magento\Framework\Registry::class);
            $event = $registry->registry('current_event');
            
            if ($event) {
                $eventId = $event->getId();
            } else {
                return [];
            }
        }
        
        $timeSlots = $this->getEventTimeSlots($eventId);
        $event = $this->getEventById($eventId);
        
        if (!$event) {
            return [];
        }
        
        $formattedSlots = [];
        
        // For recurring events, combine date and time
        if ($event->getRecurring()) {
            // Get or create a date object
            if ($selectedDate) {
                $dateObj = new \DateTime($selectedDate);
            } else {
                $dateObj = new \DateTime($event->getStartDate());
            }
            
            $formattedDate = $this->formatDate($dateObj->format('Y-m-d'));
            $timezone = $event->getEventTimezone() ?: 'UTC';
            
            foreach ($timeSlots as $index => $slot) {
                $formattedTime = isset($slot['formatted']) ? $slot['formatted'] : 
                                (isset($slot['time_start']) && isset($slot['time_end']) ? 
                                    $this->formatTimeRange($slot['time_start'], $slot['time_end']) : 
                                    '');
                                    
                $formattedSlots[] = [
                    'index' => $index,
                    'date' => $dateObj->format('Y-m-d'),
                    'time_start' => $slot['time_start'] ?? '',
                    'time_end' => $slot['time_end'] ?? '',
                    'formatted' => $formattedDate . ' · ' . $formattedTime . ' ' . $timezone
                ];
            }
        } else {
            // For non-recurring events, use the existing slots
            $formattedSlots = $timeSlots;
        }
        
        return $formattedSlots;
    }
    
    /**
     * Get formatted time slots
     * 
     * @return array
     */
    public function getFormattedTimeSlots()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $registry = $objectManager->get(\Magento\Framework\Registry::class);
        $event = $registry->registry('current_event');
        
        if (!$event) {
            return [];
        }
        
        $eventId = $event->getId();
        $timeSlots = $this->getEventTimeSlots($eventId);
        
        $formattedSlots = [];
        foreach ($timeSlots as $index => $slot) {
            if (isset($slot['time_start']) && isset($slot['time_end'])) {
                $formattedSlots[] = $this->formatTimeRange($slot['time_start'], $slot['time_end']);
            } elseif (isset($slot['formatted'])) {
                $formattedSlots[] = $slot['formatted'];
            }
        }
        
        return $formattedSlots;
    }
    
    /**
     * Is event in past
     *
     * @param string $eventDate
     * @return bool
     */
    public function isEventInPast($eventDate)
    {
        try {
            $today = new \DateTime('today');
            $eventDateTime = new \DateTime($eventDate);
            
            return $eventDateTime < $today;
        } catch (\Exception $e) {
            return false;
        }
    }
}