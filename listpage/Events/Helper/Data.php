<?php
namespace Insead\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\CountryFactory;
use Insead\Events\Model\ResourceModel\Event\TimeSlot;
use Insead\Events\Model\EventFactory;
use Insead\Events\Model\ResourceModel\Event as EventResource;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    // Email template config paths
    const XML_PATH_PAYMENT_EMAIL_TEMPLATE = 'insead_events/email_settings/payment_email_template';
    const XML_PATH_FREE_REGISTRATION_EMAIL_TEMPLATE = 'insead_events/email_settings/free_registration_email_template';
    const XML_PATH_REGISTRATION_APPROVAL_EMAIL_TEMPLATE = 'insead_events/email_settings/registration_approval_email_template';
    const XML_PATH_REGISTRATION_REJECTION_EMAIL_TEMPLATE = 'insead_events/email_settings/registration_rejection_email_template';
    const XML_PATH_SENDER_NAME = 'insead_events/email_settings/sender_name';
    const XML_PATH_SENDER_EMAIL = 'insead_events/email_settings/sender_email';
    const XML_PATH_CC_EMAILS = 'insead_events/email_settings/cc_emails';
    const XML_PATH_MODULE_ENABLED = 'insead_events/general/enabled';
    
    // General configuration paths
    const XML_PATH_LIST_TITLE = 'insead_events/general/list_title';
    const XML_PATH_HERO_TITLE = 'insead_events/general/hero_title';
    const XML_PATH_HERO_SUBTITLE = 'insead_events/general/hero_subtitle';

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
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var StateInterface
     */
    protected $inlineTranslation;
    
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var LoggerInterface
     */
    protected $logger;

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
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param TimezoneInterface $timezone
     * @param LoggerInterface $logger
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
        Json $jsonSerializer,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        TimezoneInterface $timezone,
        LoggerInterface $logger
    ) {
        $this->storeManager = $storeManager;
        $this->timeSlotResource = $timeSlotResource;
        $this->resourceConnection = $resourceConnection;
        $this->priceCurrency = $priceCurrency;
        $this->countryFactory = $countryFactory;
        $this->eventFactory = $eventFactory;
        $this->eventResource = $eventResource;
        $this->jsonSerializer = $jsonSerializer;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->timezone = $timezone;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isModuleEnabled($storeId = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list title
     *
     * @return string
     */
    public function getEventsListTitle()
    {
        $title = $this->scopeConfig->getValue(
            self::XML_PATH_LIST_TITLE,
            ScopeInterface::SCOPE_STORE
        );
        
        return $title ?: __('INSEAD Events & Programmes');
    }
    
    /**
     * Get hero title
     *
     * @return string
     */
    public function getHeroTitle()
    {
        $title = $this->scopeConfig->getValue(
            self::XML_PATH_HERO_TITLE,
            ScopeInterface::SCOPE_STORE
        );
        
        return $title ?: __('INSEAD Events & Programmes');
    }
    
    /**
     * Get hero subtitle
     *
     * @return string
     */
    public function getHeroSubtitle()
    {
        $subtitle = $this->scopeConfig->getValue(
            self::XML_PATH_HERO_SUBTITLE,
            ScopeInterface::SCOPE_STORE
        );
        
        return $subtitle ?: __('Connect, Learn, and Grow with the INSEAD Community');
    }

    /**
     * Get event URL
     *
     * @param string $urlKey
     * @return string
     */
    public function getEventUrl($urlKey)
    {
        return $this->_urlBuilder->getUrl('events/event/view', ['url_key' => $urlKey]);
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
                    // Don't use strtotime as it applies server timezone conversion
                    // Instead, parse the time components directly
                    $timeParts = explode(':', $time);
                    $hour = (int)$timeParts[0];
                    $minute = isset($timeParts[1]) ? (int)$timeParts[1] : 0;
                    
                    if ($format === 'g:i A') {
                        // Convert to 12-hour format with AM/PM
                        $ampm = $hour >= 12 ? 'PM' : 'AM';
                        $hour = $hour % 12;
                        $hour = $hour ? $hour : 12; // Convert 0 to 12 for 12 AM
                        return sprintf('%d:%02d %s', $hour, $minute, $ampm);
                    } elseif ($format === 'H:i') {
                        // Keep 24-hour format
                        return sprintf('%02d:%02d', $hour, $minute);
                    } else {
                        // Handle other formats if needed
                        // For now, default to original format
                        return $time;
                    }
                } else { // Full datetime string
                    // For full datetime strings, create DateTime without timezone conversion
                    $dateObj = new \DateTime($time);
                    return $dateObj->format($format);
                }
            }
            return $time;
        } catch (\Exception $e) {
            return $time;
        }
    }
    
    /**
     * Get calendar month name
     *
     * @param int $month
     * @return string
     */
    public function getMonthName($month)
    {
        $dateObj = \DateTime::createFromFormat('!m', $month);
        return $dateObj->format('F');
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
     * @return \Insead\Events\Model\Event|null
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
            $this->logger->error('Error loading event: ' . $e->getMessage());
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
            );
        } catch (\Exception $e) {
            $this->logger->error('Error getting media URL: ' . $e->getMessage());
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
            $this->logger->error('Error getting event time slots: ' . $e->getMessage());
            
            // Try fallback to event model
            try {
                $event = $this->getEventById($eventId);
                if ($event) {
                    $timeSlotsData = $event->getTimeSlots();
                    if (is_string($timeSlotsData) && !empty($timeSlotsData)) {
                        try {
                            return $this->jsonSerializer->unserialize($timeSlotsData);
                        } catch (\Exception $jsonException) {
                            $this->logger->error('Error parsing time slots JSON: ' . $jsonException->getMessage());
                        }
                    }
                }
            } catch (\Exception $eventException) {
                $this->logger->error('Error in fallback time slots retrieval: ' . $eventException->getMessage());
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

    /**
     * Generate Google Calendar URL for event
     *
     * @param string $eventTitle
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param string $eventVenue
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return string
     */
    protected function generateGoogleCalendarUrl($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours = 1, $timezone = 'UTC')
    {
        $dates = $this->formatCalendarDates($eventDate, $eventTime, $durationHours, $timezone);
        
        $url = 'https://www.google.com/calendar/render?action=TEMPLATE';
        $url .= '&text=' . urlencode($eventTitle);
        $url .= '&dates=' . $dates['start'] . '/' . $dates['end'];
        $url .= '&ctz=' . urlencode($timezone); // Add timezone parameter without Z suffix
        $url .= '&details=' . urlencode('Event at ' . $eventVenue);
        $url .= '&location=' . urlencode($eventVenue);
        
        return $url;
    }

    /**
     * Generate Outlook Calendar URL for event
     *
     * @param string $eventTitle
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param string $eventVenue
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return string
     */
    protected function generateOutlookCalendarUrl($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours = 1, $timezone = 'UTC')
    {
        // Use the exact times from the database
        $startTime = isset($GLOBALS['direct_time_start']) ? $GLOBALS['direct_time_start'] : '';
        $endTime = isset($GLOBALS['direct_time_end']) ? $GLOBALS['direct_time_end'] : '';
        
        // If somehow direct times are not available, try to extract from eventTime
        if (empty($startTime) || empty($endTime)) {
            if (strpos($eventTime, ' - ') !== false) {
                $timeParts = explode(' - ', $eventTime);
                if (count($timeParts) == 2) {
                    $startTime = trim($timeParts[0]);
                    $endTime = trim($timeParts[1]);
                }
            }
        }
        
        // If still no end time, calculate based on duration
        if (empty($endTime) && !empty($startTime)) {
            // Try to extract hours and minutes
            if (preg_match('/(\d{1,2}):(\d{2})/', $startTime, $matches)) {
                $hours = (int)$matches[1] + $durationHours;
                $minutes = (int)$matches[2];
                $endTime = sprintf('%02d:%02d', $hours, $minutes);
            }
        }
        
        // Convert date format for ISO8601 (DD-MM-YYYY to YYYY-MM-DD)
        $dateParts = explode('-', $eventDate);
        if (count($dateParts) === 3) {
            $isoDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0]; 
        } else {
            $isoDate = $eventDate;
        }
        
        // Make sure times are in HH:MM:00 format
        if (!empty($startTime) && preg_match('/^(\d{1,2}):(\d{2})$/', $startTime, $matches)) {
            $startTime = sprintf('%s:%s:00', $matches[1], $matches[2]);
        }
        
        if (!empty($endTime) && preg_match('/^(\d{1,2}):(\d{2})$/', $endTime, $matches)) {
            $endTime = sprintf('%s:%s:00', $matches[1], $matches[2]);
        }
        
        // Build the URL with exact times
        $outlookStart = $isoDate . 'T' . $startTime;
        $outlookEnd = $isoDate . 'T' . $endTime;
        
        $url = 'https://outlook.office.com/calendar/0/deeplink/compose';
        $url .= '?subject=' . urlencode($eventTitle);
        $url .= '&startdt=' . urlencode($outlookStart);
        $url .= '&enddt=' . urlencode($outlookEnd);
        $url .= '&body=' . urlencode('Event at ' . $eventVenue);
        $url .= '&location=' . urlencode($eventVenue);
        
        return $url;
    }

    /**
     * Generate Yahoo Calendar URL for event
     *
     * @param string $eventTitle
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param string $eventVenue
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return string
     */
    protected function generateYahooCalendarUrl($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours = 1, $timezone = 'UTC')
    {
        $dates = $this->formatCalendarDates($eventDate, $eventTime, $durationHours, $timezone);
        
        $duration = $durationHours * 60; // Duration in minutes
        
        $url = 'https://calendar.yahoo.com/?v=60';
        $url .= '&title=' . urlencode($eventTitle);
        $url .= '&st=' . $dates['start'];
        $url .= '&et=' . $dates['end'];
        $url .= '&desc=' . urlencode('Event at ' . $eventVenue);
        $url .= '&in_loc=' . urlencode($eventVenue);
        $url .= '&dur=' . $duration;
        
        return $url;
    }

    /**
     * Generate iCalendar file content for event
     *
     * @param string $eventTitle
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param string $eventVenue
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return string
     */
    protected function generateICalContent($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours = 1, $timezone = 'UTC')
    {
        $dates = $this->formatCalendarDates($eventDate, $eventTime, $durationHours, $timezone);
        
        // For better iCal compatibility, include more detailed timezone info
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//INSEAD Events//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        
        // Simple event without complex timezone info for better compatibility
        $ical .= "BEGIN:VEVENT\r\n";
        $ical .= "SUMMARY:" . $this->escapeIcalText($eventTitle) . "\r\n";
        $ical .= "DTSTART:" . $dates['start'] . "\r\n";
        $ical .= "DTEND:" . $dates['end'] . "\r\n";
        $ical .= "LOCATION:" . $this->escapeIcalText($eventVenue) . "\r\n";
        $ical .= "DESCRIPTION:" . $this->escapeIcalText('Event at ' . $eventVenue) . "\r\n";
        $ical .= "STATUS:CONFIRMED\r\n";
        $ical .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ical .= "UID:" . md5($eventTitle . $dates['start'] . $dates['end']) . "@insead.edu\r\n";
        $ical .= "END:VEVENT\r\n";
        $ical .= "END:VCALENDAR";
        
        return $ical;
    }

    /**
     * Generate iCalendar URL (data URI) for event
     *
     * @param string $eventTitle
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param string $eventVenue
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return string
     */
    protected function generateICalUrl($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours = 1, $timezone = 'UTC')
    {
        $icalContent = $this->generateICalContent($eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours, $timezone);
        return 'data:text/calendar;charset=utf8,' . urlencode($icalContent);
    }

    /**
     * Format calendar dates for use in calendar URLs
     *
     * @param string $eventDate Format: "DD-MM-YYYY"
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM"
     * @param int $durationHours
     * @param string $timezone Event timezone
     * @return array
     */
    protected function formatCalendarDates($eventDate, $eventTime, $durationHours = 1, $timezone = 'UTC')
    {
        // Parse date (convert from DD-MM-YYYY to DateTime)
        $dateParts = explode('-', $eventDate);
        if (count($dateParts) !== 3) {
            // Fallback if date format is not as expected
            $date = new \DateTime($eventDate);
        } else {
            // Create DateTime from DD-MM-YYYY format
            $date = \DateTime::createFromFormat('d-m-Y', $eventDate);
            if (!$date) {
                $date = new \DateTime($eventDate);
            }
        }
        
        // Parse time
        $timeInfo = $this->parseEventTime($eventTime);
        
        // Set the time on the date
        $date->setTime($timeInfo['hours'], $timeInfo['minutes']);
        
        // Create end time
        $endDate = clone $date;
        $endDate->modify('+' . $durationHours . ' hours');
        
        // Format for calendar URLs - without Z suffix to indicate local time
        $start = $date->format('Ymd\THis');
        $end = $endDate->format('Ymd\THis');
        
        return [
            'start' => $start,
            'end' => $end,
            'timezone' => $timezone
        ];
    }

    /**
     * Parse event time string
     *
     * @param string $eventTime Format: "HH:MM" or "HH:MM - HH:MM" or "HH:MM AM/PM"
     * @return array
     */
    protected function parseEventTime($eventTime)
    {
        $hours = 12;
        $minutes = 0;
        
        // Handle time ranges (e.g., "14:00 - 15:00")
        if (strpos($eventTime, ' - ') !== false) {
            $eventTime = explode(' - ', $eventTime)[0];
        }
        
        // Handle AM/PM format
        $isPm = false;
        if (stripos($eventTime, 'pm') !== false) {
            $isPm = true;
            $eventTime = str_ireplace('pm', '', $eventTime);
        } elseif (stripos($eventTime, 'am') !== false) {
            $eventTime = str_ireplace('am', '', $eventTime);
        }
        
        // Parse HH:MM format
        if (preg_match('/(\d{1,2}):(\d{2})/', $eventTime, $matches)) {
            $hours = (int)$matches[1];
            $minutes = (int)$matches[2];
            
            // Adjust for PM
            if ($isPm && $hours < 12) {
                $hours += 12;
            }
            // Adjust for 12 AM
            if (!$isPm && $hours == 12) {
                $hours = 0;
            }
        }
        
        return [
            'hours' => $hours,
            'minutes' => $minutes
        ];
    }

    /**
     * Escape text for iCalendar format
     *
     * @param string $text
     * @return string
     */
    protected function escapeIcalText($text)
    {
        $text = str_replace("\\", "\\\\", $text);
        $text = str_replace("\n", "\\n", $text);
        $text = str_replace(",", "\\,", $text);
        $text = str_replace(";", "\\;", $text);
        return $text;
    }

    /**
     * Add calendar URLs to email template variables
     *
     * @param array $templateVars Existing template variables
     * @return array Updated template variables with calendar URLs
     */
    public function addCalendarUrls($templateVars)
    {
        $eventTitle = $templateVars['event_title'] ?? '';
        $eventDate = $templateVars['event_date'] ?? '';
        $eventTime = $templateVars['event_time'] ?? '';
        $eventVenue = $templateVars['event_venue'] ?? '';
        
        // Get event timezone from template vars or default to UTC
        $timezone = $templateVars['event_timezone'] ?? 'UTC';
        
        // Store the direct time values in globals for use in generateOutlookCalendarUrl
        if (isset($templateVars['event_time_start'])) {
            $GLOBALS['direct_time_start'] = $templateVars['event_time_start'];
        }
        if (isset($templateVars['event_time_end'])) {
            $GLOBALS['direct_time_end'] = $templateVars['event_time_end'];
        }
        
        // Calculate duration based on start and end time if available
        $durationHours = 1; // Default duration
        
        // If we have specific start and end times in the template vars (24-hour format HH:MM or HH:MM:SS)
        if (isset($templateVars['event_time_start']) && isset($templateVars['event_time_end'])) {
            $startTime = $templateVars['event_time_start'];
            $endTime = $templateVars['event_time_end'];
            
            // Extract hours and minutes from the time strings
            $startParts = explode(':', $startTime);
            $endParts = explode(':', $endTime);
            
            if (count($startParts) >= 2 && count($endParts) >= 2) {
                // Calculate hours as decimals (e.g., 1.5 for 1 hour and 30 minutes)
                $startHours = (int)$startParts[0] + ((int)$startParts[1] / 60);
                $endHours = (int)$endParts[0] + ((int)$endParts[1] / 60);
                
                // Calculate the difference
                $durationHours = $endHours - $startHours;
                
                // If duration is negative (e.g., crossing midnight), add 24 hours
                if ($durationHours < 0) {
                    $durationHours += 24;
                }
                
                // If duration is still 0 or negative, fallback to default
                if ($durationHours <= 0) {
                    $durationHours = 1;
                }
            }
        }
        
        // Generate calendar URLs
        $templateVars['calendar_google_url'] = $this->generateGoogleCalendarUrl(
            $eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours, $timezone
        );
        
        $templateVars['calendar_outlook_url'] = $this->generateOutlookCalendarUrl(
            $eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours, $timezone
        );
        
        $templateVars['calendar_yahoo_url'] = $this->generateYahooCalendarUrl(
            $eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours, $timezone
        );
        
        $templateVars['calendar_ical_url'] = $this->generateICalUrl(
            $eventTitle, $eventDate, $eventTime, $eventVenue, $durationHours, $timezone
        );
        
        // Clean up globals
        unset($GLOBALS['direct_time_start']);
        unset($GLOBALS['direct_time_end']);
        
        return $templateVars;
    }

    /**
     * Send payment confirmation email
     *
     * @param \Insead\Events\Model\EventRegistration $registration
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function sendPaymentConfirmationEmail($registration, $order)
    {
        if (!$this->isModuleEnabled()) {
            $this->logger->info('Email Helper: Module is disabled, not sending payment confirmation email');
            return false;
        }

        try {
            $this->logger->info('Email Helper: Sending payment confirmation email for registration #' . $registration->getId());
            
            $templateVars = $this->prepareCommonTemplateVars($registration, $order);
            
            // Add payment-specific variables
            $payment = $order->getPayment();
            $paymentMethod = $payment ? $payment->getMethodInstance()->getTitle() : '';
            if ($payment && $payment->getMethod() == 'sogecommerce') {
                $paymentMethod = 'Payment by credit card (Sogecommerce)';
            }
            
            $transactionId = $payment ? $payment->getLastTransId() : '';
            $transactionUuid = $payment ? $payment->getAdditionalInformation('transaction_uuid') : '';
            if (!$transactionUuid) {
                $transactionUuid = uniqid('', true);
            }
            
            // Format transaction date
            $transactionDate = date('M d, Y, g:i:s A', strtotime($order->getCreatedAt()));
            
            // Add additional variables specific to payment
            $templateVars['payment_method'] = $paymentMethod;
            $templateVars['payment_confirmation_id'] = $order->getIncrementId();
            $templateVars['transaction_date'] = $transactionDate;
            $templateVars['transaction_id'] = $transactionId ?: $order->getIncrementId();
            $templateVars['transaction_uuid'] = $transactionUuid;
            $formattedPrice = '';
            $formattedPrice = $order->formatPrice($order->getGrandTotal());
            $formattedPrice = strip_tags($formattedPrice);
            $templateVars['price'] = $formattedPrice; 
            
            // Add customer email if not already set
            if (!isset($templateVars['customer_email'])) {
                $templateVars['customer_email'] = $registration->getEmail();
            }
            
            // Add support email for contact information
            $templateVars['support_email'] = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_EMAIL,
                ScopeInterface::SCOPE_STORE
            );
            
            // Add calendar URLs
            $templateVars = $this->addCalendarUrls($templateVars);
            
            // Get appropriate template ID
            $templateId = $this->getEmailTemplate(self::XML_PATH_PAYMENT_EMAIL_TEMPLATE);
            
            return $this->sendEmail($templateVars, $templateId, $registration->getEmail(), $registration->getFullName());
        } catch (\Exception $e) {
            $this->logger->error('Email Helper: Failed to send payment confirmation email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send free registration confirmation email
     *
     * @param \Insead\Events\Model\EventRegistration $registration
     * @param \Magento\Sales\Model\Order|null $order
     * @return bool
     */
    public function sendFreeRegistrationEmail($registration, $order = null)
    {
        if (!$this->isModuleEnabled()) {
            $this->logger->info('Email Helper: Module is disabled, not sending free registration email');
            return false;
        }

        try {
            $this->logger->info('Email Helper: Sending free registration email for registration #' . $registration->getId());
            
            $templateVars = $this->prepareCommonTemplateVars($registration, $order);
            
            // Add customer email if not already set
            if (!isset($templateVars['customer_email'])) {
                $templateVars['customer_email'] = $registration->getEmail();
            }
            
            // Add support email for contact information
            $templateVars['support_email'] = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_EMAIL,
                ScopeInterface::SCOPE_STORE
            );
            
            // Add calendar URLs
            $templateVars = $this->addCalendarUrls($templateVars);
            
            // Get appropriate template ID
            $templateId = $this->getEmailTemplate(self::XML_PATH_FREE_REGISTRATION_EMAIL_TEMPLATE);
            
            return $this->sendEmail($templateVars, $templateId, $registration->getEmail(), $registration->getFullName());
        } catch (\Exception $e) {
            $this->logger->error('Email Helper: Failed to send free registration email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send registration approval email
     *
     * @param \Insead\Events\Model\EventRegistration $registration
     * @return bool
     */
    public function sendApprovalEmail($registration)
    {
        if (!$this->isModuleEnabled()) {
            $this->logger->info('Email Helper: Module is disabled, not sending approval email');
            return false;
        }

        try {
            $this->logger->info('Email Helper: Sending approval email for registration #' . $registration->getId());
            
            $templateVars = $this->prepareCommonTemplateVars($registration);
            
            // Add customer email if not already set
            if (!isset($templateVars['customer_email'])) {
                $templateVars['customer_email'] = $registration->getEmail();
            }
            
            // Add support email for contact information
            $templateVars['support_email'] = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_EMAIL,
                ScopeInterface::SCOPE_STORE
            );
            
            // Add calendar URLs
            $templateVars = $this->addCalendarUrls($templateVars);
            
            // Get appropriate template ID
            $templateId = $this->getEmailTemplate(self::XML_PATH_REGISTRATION_APPROVAL_EMAIL_TEMPLATE);
            
            return $this->sendEmail($templateVars, $templateId, $registration->getEmail(), $registration->getFullName());
        } catch (\Exception $e) {
            $this->logger->error('Email Helper: Failed to send approval email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send registration rejection email
     *
     * @param \Insead\Events\Model\EventRegistration $registration
     * @param string $rejectionReason
     * @return bool
     */
    public function sendRejectionEmail($registration, $rejectionReason = '')
    {
        if (!$this->isModuleEnabled()) {
            $this->logger->info('Email Helper: Module is disabled, not sending rejection email');
            return false;
        }

        try {
            $this->logger->info('Email Helper: Sending rejection email for registration #' . $registration->getId());
            
            $templateVars = $this->prepareCommonTemplateVars($registration);
            $templateVars['rejection_reason'] = $rejectionReason;
            
            // Add customer email if not already set
            if (!isset($templateVars['customer_email'])) {
                $templateVars['customer_email'] = $registration->getEmail();
            }
            
            // Add support email for contact information
            $templateVars['support_email'] = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_EMAIL,
                ScopeInterface::SCOPE_STORE
            );
            
            // Note: We don't add calendar URLs for rejection emails
            
            // Get appropriate template ID
            $templateId = $this->getEmailTemplate(self::XML_PATH_REGISTRATION_REJECTION_EMAIL_TEMPLATE);
            
            return $this->sendEmail($templateVars, $templateId, $registration->getEmail(), $registration->getFullName());
        } catch (\Exception $e) {
            $this->logger->error('Email Helper: Failed to send rejection email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Prepare common template variables
     *
     * @param \Insead\Events\Model\EventRegistration $registration
     * @param \Magento\Sales\Model\Order|null $order
     * @return array
     */
    protected function prepareCommonTemplateVars($registration, $order = null)
    {
        // Load event to get more details
        $event = $this->eventFactory->create();
        $this->eventResource->load($event, $registration->getEventId());
        
        $eventTitle = 'Event Registration';
        if ($event && $event->getId()) {
            $eventTitle = $event->getEventTitle();
        } elseif ($order && $order->getAllItems() && count($order->getAllItems()) > 0) {
            $eventTitle = $order->getAllItems()[0]->getName();
        }
        
        // Format date and time
        $eventDate = $registration->getSelectedDate() ?: ($event ? $event->getStartDate() : date('d-m-Y'));
        // Format as DD-MM-YYYY
        if ($eventDate) {
            $dateObj = new \DateTime($eventDate);
            $eventDate = $dateObj->format('d-m-Y');
        }
        
        // Get time slots from registration data
        $eventTimeStart = $registration->getSelectedTimeStart();
        $eventTimeEnd = $registration->getSelectedTimeEnd();
        
        // Store raw database values for direct use in calendar links
        $GLOBALS['direct_time_start'] = $eventTimeStart;
        $GLOBALS['direct_time_end'] = $eventTimeEnd;
        
        $eventTime = 'N/A';
        if ($eventTimeStart && $eventTimeEnd) {
            // Format individual times for display
            $formattedStart = $eventTimeStart;
            $formattedEnd = $eventTimeEnd;
            
            // Try to format as HH:MM AM/PM if they appear to be in 24-hour format
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $eventTimeStart)) {
                $timeObj = \DateTime::createFromFormat('H:i:s', $eventTimeStart);
                if (!$timeObj) {
                    $timeObj = \DateTime::createFromFormat('H:i', $eventTimeStart);
                }
                if ($timeObj) {
                    $formattedStart = $timeObj->format('h:i A');
                }
            }
            
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $eventTimeEnd)) {
                $timeObj = \DateTime::createFromFormat('H:i:s', $eventTimeEnd);
                if (!$timeObj) {
                    $timeObj = \DateTime::createFromFormat('H:i', $eventTimeEnd);
                }
                if ($timeObj) {
                    $formattedEnd = $timeObj->format('h:i A');
                }
            }
            
            $eventTime = $formattedStart . ' - ' . $formattedEnd;
        } elseif ($eventTimeStart) {
            // If only start time is available
            $eventTime = $eventTimeStart;
            
            // Try to format as HH:MM AM/PM
            $timeObj = \DateTime::createFromFormat('H:i:s', $eventTimeStart);
            if (!$timeObj) {
                $timeObj = \DateTime::createFromFormat('H:i', $eventTimeStart);
            }
            if ($timeObj) {
                $eventTime = $timeObj->format('h:i A');
            }
        }
        
        $eventVenue = $event ? $event->getEventVenue() : 'N/A';
        
        // Get event timezone
        $eventTimezone = $event ? $event->getEventTimezone() : 'UTC';
        
        // Get billing address info if order exists
        $street = '';
        $city = '';
        $region = '';
        $postcode = '';
        $country = '';
        
        if ($order && $order->getBillingAddress()) {
            $billingAddress = $order->getBillingAddress();
            $street = $billingAddress->getStreet() ? implode(', ', $billingAddress->getStreet()) : '';
            $city = $billingAddress->getCity() ?: '';
            $region = $billingAddress->getRegion() ?: '';
            $postcode = $billingAddress->getPostcode() ?: '';
            $country = $billingAddress->getCountryId() ?: '';
        }
        
        // Create base template variables
        $templateVars = [
            'customer_name' => $registration->getFullName(),
            'customer_email' => $registration->getEmail(),
            'event_title' => $eventTitle,
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'event_time_start' => $eventTimeStart,
            'event_time_end' => $eventTimeEnd,
            'event_venue' => $eventVenue,
            'event_timezone' => $eventTimezone,
            'registration_id' => $registration->getId(),
            'customer_street' => $street,
            'customer_city' => $city,
            'customer_region' => $region,
            'customer_postal' => $postcode,
            'customer_country' => $country,
            // Default zoom flag to false
            'is_zoom_meeting' => false
        ];
        
        // Add Zoom meeting information if this is a Zoom event
        if ($event && $event->getData('is_zoom') == 1) {
            $templateVars['is_zoom_meeting'] = true;
            
            // Only add fields that have values
            if ($event->getZoomMeetingUrl()) {
                $templateVars['zoom_meeting_url'] = $event->getZoomMeetingUrl();
            }
            
            if ($event->getZoomPassword()) {
                $templateVars['zoom_password'] = $event->getZoomPassword();
            }
            
            if ($event->getZoomVideoConferenceId()) {
                $templateVars['zoom_video_conference_id'] = $event->getZoomVideoConferenceId();
            }
            
            if ($event->getZoomMeetingId()) {
                $templateVars['zoom_meeting_id'] = $event->getZoomMeetingId();
            }
            
            if ($event->getZoomConferencePassword()) {
                $templateVars['zoom_conference_password'] = $event->getZoomConferencePassword();
            }
            
            if ($event->getZoomLocalNumberUrl()) {
                $templateVars['zoom_local_number_url'] = $event->getZoomLocalNumberUrl();
            }
        }
        
        return $templateVars;
    }
    
    /**
     * Get email template ID from configuration
     *
     * @param string $configPath
     * @return string
     */
    protected function getEmailTemplate($configPath)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * Send email with given template and variables
     *
     * @param array $templateVars
     * @param string $templateId
     * @param string $recipientEmail
     * @param string $recipientName
     * @return bool
     */
    protected function sendEmail($templateVars, $templateId, $recipientEmail, $recipientName)
    {
        try {
            $this->inlineTranslation->suspend();
            
            $storeId = $this->storeManager->getStore()->getId();
            
            // Get sender info from configuration
            $senderName = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_NAME,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: $this->scopeConfig->getValue('trans_email/ident_general/name', ScopeInterface::SCOPE_STORE);
            
            $senderEmail = $this->scopeConfig->getValue(
                self::XML_PATH_SENDER_EMAIL,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ) ?: $this->scopeConfig->getValue('trans_email/ident_general/email', ScopeInterface::SCOPE_STORE);
            
            // Use configured sender
            $sender = [
                'name' => $senderName,
                'email' => $senderEmail
            ];
            
            // Add sender name to template vars
            $templateVars['sender_name'] = $senderName;
            
            // Start building transport
            $transportBuilder = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($recipientEmail, $recipientName);
            
            // Add CC recipients if configured
            $ccEmails = $this->scopeConfig->getValue(
                self::XML_PATH_CC_EMAILS,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            
            if ($ccEmails) {
                $ccEmailsArray = array_map('trim', explode(',', $ccEmails));
                foreach ($ccEmailsArray as $ccEmail) {
                    if ($ccEmail && filter_var($ccEmail, FILTER_VALIDATE_EMAIL)) {
                        $transportBuilder->addCc($ccEmail);
                    }
                }
            }
            
            $transport = $transportBuilder->getTransport();
            $transport->sendMessage();
            
            $this->inlineTranslation->resume();
            
            $this->logger->info('Email Helper: Email sent successfully');
            return true;
        } catch (\Exception $e) {
            $this->logger->error('Email Helper: Failed to send email: ' .$templateId . $e->getMessage());
            return false;
        }
    }
}