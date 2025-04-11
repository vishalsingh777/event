<?php
namespace Vishal\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class Event extends AbstractModel implements EventInterface
{
    /**
     * Status enabled
     */
    const STATUS_ENABLED = 1;

    /**
     * Status disabled
     */
    const STATUS_DISABLED = 0;

    /**
     * Recurring enabled
     */
    const RECURRING_ENABLED = 1;

    /**
     * Recurring disabled
     */
    const RECURRING_DISABLED = 0;

    /**
     * Event properties constants - exactly matching the form fields and database columns
     */
    const EVENT_ID = 'event_id';
    const EVENT_TITLE = 'event_title';
    const EVENT_VENUE = 'event_venue';
    const URL_KEY = 'url_key';
    const COLOR = 'color';
    const START_DATE = 'start_date';
    const END_DATE = 'end_date';
    const CONTENT = 'content';
    const YOUTUBE_VIDEO_URL = 'youtube_video_url';
    const STATUS = 'status';
    const RECURRING = 'recurring';
    const REPEAT = 'repeat';
    const REPEAT_EVERY = 'repeat_every';
    const CONTACT_PERSON = 'contact_person';
    const PHONE = 'phone';
    const FAX = 'fax';
    const EMAIL = 'email';
    const ADDRESS = 'address';
    const PAGE_TITLE = 'page_title';
    const KEYWORDS = 'keywords';
    const DESCRIPTION = 'description';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';
    const PRODUCT_SKU = 'product_sku';
    const EVENT_PRICE = 'event_price';
    const CUSTOMER_GROUP = 'customer_group';

    // Event scheduling fields exactly matching form names
    const AVAILABLE_DAYS = 'available_days';
    const TIME_SLOTS = 'time_slots';
    const BLOCK_DATES = 'block_dates';
    const SELECTED_TIME_SLOTS = 'selected_time_slots';
    const SINGLE_START_TIME = 'single_start_time';
    const SINGLE_END_TIME = 'single_end_time';
    const EVENT_TIMEZONE = 'event_timezone';

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'vishal_events';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'vishal_event';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EventResource::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Event ID
     *
     * @return int|null
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * Set Event ID
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * Get Event Title
     *
     * @return string|null
     */
    public function getEventTitle()
    {
        return $this->getData(self::EVENT_TITLE);
    }

    /**
     * Set Event Title
     *
     * @param string $eventTitle
     * @return $this
     */
    public function setEventTitle($eventTitle)
    {
        return $this->setData(self::EVENT_TITLE, $eventTitle);
    }

    /**
     * Get Event Venue
     *
     * @return string|null
     */
    public function getEventVenue()
    {
        return $this->getData(self::EVENT_VENUE);
    }

    /**
     * Set Event Venue
     *
     * @param string $eventVenue
     * @return $this
     */
    public function setEventVenue($eventVenue)
    {
        return $this->setData(self::EVENT_VENUE, $eventVenue);
    }

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set URL Key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Get Color
     *
     * @return string|null
     */
    public function getColor()
    {
        return $this->getData(self::COLOR);
    }

    /**
     * Set Color
     *
     * @param string $color
     * @return $this
     */
    public function setColor($color)
    {
        return $this->setData(self::COLOR, $color);
    }

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get End Date
     *
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Set Content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get YouTube Video URL
     *
     * @return string|null
     */
    public function getYoutubeVideoUrl()
    {
        return $this->getData(self::YOUTUBE_VIDEO_URL);
    }

    /**
     * Set YouTube Video URL
     *
     * @param string $youtubeVideoUrl
     * @return $this
     */
    public function setYoutubeVideoUrl($youtubeVideoUrl)
    {
        return $this->setData(self::YOUTUBE_VIDEO_URL, $youtubeVideoUrl);
    }

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Recurring
     *
     * @return int|null
     */
    public function getRecurring()
    {
        return $this->getData(self::RECURRING);
    }

    /**
     * Set Recurring
     *
     * @param int $recurring
     * @return $this
     */
    public function setRecurring($recurring)
    {
        return $this->setData(self::RECURRING, $recurring);
    }

    /**
     * Get Repeat
     *
     * @return string|null
     */
    public function getRepeat()
    {
        return $this->getData(self::REPEAT);
    }

    /**
     * Set Repeat
     *
     * @param string $repeat
     * @return $this
     */
    public function setRepeat($repeat)
    {
        return $this->setData(self::REPEAT, $repeat);
    }

    /**
     * Get Repeat Every
     *
     * @return int|null
     */
    public function getRepeatEvery()
    {
        return $this->getData(self::REPEAT_EVERY);
    }

    /**
     * Set Repeat Every
     *
     * @param int $repeatEvery
     * @return $this
     */
    public function setRepeatEvery($repeatEvery)
    {
        return $this->setData(self::REPEAT_EVERY, $repeatEvery);
    }

    /**
     * Get Available Days
     * 
     * @return array
     */
    public function getAvailableDays()
    {
        $availableDays = $this->getData(self::AVAILABLE_DAYS);
        if ($availableDays) {
            return json_decode($availableDays, true) ?: [];
        }
        // Default to all days
        return ['0', '1', '2', '3', '4', '5', '6'];
    }

    /**
     * Set Available Days
     * 
     * @param array|string $availableDays
     * @return $this
     */
    public function setAvailableDays($availableDays)
    {
        if (is_array($availableDays)) {
            $availableDays = json_encode($availableDays);
        }
        return $this->setData(self::AVAILABLE_DAYS, $availableDays);
    }

    /**
     * Get Time Slots
     * 
     * @return array
     */
    public function getTimeSlots()
    {
        $timeSlots = $this->getData(self::TIME_SLOTS);
        
        if ($timeSlots) {
            if (is_string($timeSlots)) {
                try {
                    $decodedSlots = json_decode($timeSlots, true);
                    if (is_array($decodedSlots)) {
                        return $decodedSlots;
                    } else {
                        // If not a valid JSON array, handle as legacy format (comma-separated)
                        return array_map('trim', explode(',', $timeSlots));
                    }
                } catch (\Exception $e) {
                    return [];
                }
            } elseif (is_array($timeSlots)) {
                return $timeSlots;
            }
        }
        
        // Default time slots in range format
        return [['time_start' => '09:00', 'time_end' => '17:00']];
    }

    /**
     * Set Time Slots
     * 
     * @param array|string $timeSlots
     * @return $this
     */
    public function setTimeSlots($timeSlots)
    {
        if (is_array($timeSlots)) {
            $timeSlots = json_encode($timeSlots);
        }
        return $this->setData(self::TIME_SLOTS, $timeSlots);
    }

    /**
     * Get Blocked Dates
     * 
     * @return array
     */
    public function getBlockDates()
    {
        $blockDates = $this->getData(self::BLOCK_DATES);
        if ($blockDates) {
            if (is_string($blockDates)) {
                try {
                    $decodedDates = json_decode($blockDates, true);
                    if (is_array($decodedDates)) {
                        return $decodedDates;
                    } else {
                        // If not a valid JSON array, handle as legacy format (newline-separated)
                        return array_map('trim', explode("\n", $blockDates));
                    }
                } catch (\Exception $e) {
                    return [];
                }
            } elseif (is_array($blockDates)) {
                return $blockDates;
            }
        }
        return [];
    }

    /**
     * Set Blocked Dates
     * 
     * @param array|string $blockDates
     * @return $this
     */
    public function setBlockDates($blockDates)
    {
        if (is_array($blockDates)) {
            $blockDates = json_encode($blockDates);
        }
        return $this->setData(self::BLOCK_DATES, $blockDates);
    }

    /**
     * Get Contact Person
     *
     * @return string|null
     */
    public function getContactPerson()
    {
        return $this->getData(self::CONTACT_PERSON);
    }

    /**
     * Set Contact Person
     *
     * @param string $contactPerson
     * @return $this
     */
    public function setContactPerson($contactPerson)
    {
        return $this->setData(self::CONTACT_PERSON, $contactPerson);
    }

    /**
     * Get Phone
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * Set Phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * Get Fax
     *
     * @return string|null
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * Set Fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set Email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get Address
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * Set Address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * Get Page Title
     *
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->getData(self::PAGE_TITLE);
    }

    /**
     * Set Page Title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        return $this->setData(self::PAGE_TITLE, $pageTitle);
    }

    /**
     * Get Keywords
     *
     * @return string|null
     */
    public function getKeywords()
    {
        return $this->getData(self::KEYWORDS);
    }

    /**
     * Set Keywords
     *
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords)
    {
        return $this->setData(self::KEYWORDS, $keywords);
    }

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * Set Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get Store IDs
     *
     * @return array|null
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * Set Store IDs
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreId(array $storeIds)
    {
        return $this->setData(self::STORE_ID, $storeIds);
    }
    
    /**
     * Get Product SKU
     *
     * @return string|null
     */
    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    /**
     * Set Product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku)
    {
        return $this->setData(self::PRODUCT_SKU, $productSku);
    }

    /**
     * Get Event Price
     *
     * @return string|null
     */
    public function getEventPrice()
    {
        return $this->getData(self::EVENT_PRICE);
    }

    /**
     * Set Event Price
     *
     * @param string $eventPrice
     * @return $this
     */
    public function setEventPrice($eventPrice)
    {
        return $this->setData(self::EVENT_PRICE, $eventPrice);
    }

    /**
     * Get Customer Group
     *
     * @return string|null
     */
    public function getCustomerGroup()
    {
        return $this->getData(self::CUSTOMER_GROUP);
    }

    /**
     * Set Customer Group
     *
     * @param string $customerGroup
     * @return $this
     */
    public function setCustomerGroup($customerGroup)
    {
        return $this->setData(self::CUSTOMER_GROUP, $customerGroup);
    }

    /**
     * Get Event Timezone
     *
     * @return string|null
     */
    public function getEventTimezone()
    {
        return $this->getData(self::EVENT_TIMEZONE);
    }

    /**
     * Set Event Timezone
     *
     * @param string $timezone
     * @return $this
     */
    public function setEventTimezone($timezone)
    {
        return $this->setData(self::EVENT_TIMEZONE, $timezone);
    }

    /**
     * Get Single Start Time
     *
     * @return string|null
     */
    public function getSingleStartTime()
    {
        return $this->getData(self::SINGLE_START_TIME);
    }

    /**
     * Set Single Start Time
     *
     * @param string $startTime
     * @return $this
     */
    public function setSingleStartTime($startTime)
    {
        return $this->setData(self::SINGLE_START_TIME, $startTime);
    }

    /**
     * Get Single End Time
     *
     * @return string|null
     */
    public function getSingleEndTime()
    {
        return $this->getData(self::SINGLE_END_TIME);
    }

    /**
     * Set Single End Time
     *
     * @param string $endTime
     * @return $this
     */
    public function setSingleEndTime($endTime)
    {
        return $this->setData(self::SINGLE_END_TIME, $endTime);
    }

    /**
     * Get Selected Time Slots
     * 
     * @return array
     */
    public function getSelectedTimeSlots()
    {
        $selectedTimeSlots = $this->getData(self::SELECTED_TIME_SLOTS);
        if ($selectedTimeSlots) {
            if (is_string($selectedTimeSlots)) {
                try {
                    return json_decode($selectedTimeSlots, true) ?: [];
                } catch (\Exception $e) {
                    return [];
                }
            } elseif (is_array($selectedTimeSlots)) {
                return $selectedTimeSlots;
            }
        }
        
        // Fallback to regular time slots if selected not available
        return $this->getTimeSlots();
    }

    /**
     * Set Selected Time Slots
     * 
     * @param array|string $selectedTimeSlots
     * @return $this
     */
    public function setSelectedTimeSlots($selectedTimeSlots)
    {
        if (is_array($selectedTimeSlots)) {
            $selectedTimeSlots = json_encode($selectedTimeSlots);
        }
        return $this->setData(self::SELECTED_TIME_SLOTS, $selectedTimeSlots);
    }

    /**
     * Get available statuses
     *
     * @return array
     */
    public static function getAvailableStatuses()
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }

    /**
     * Get recurring options
     *
     * @return array
     */
    public static function getRecurringOptions()
    {
        return [
            self::RECURRING_DISABLED => __('Single time'),
            self::RECURRING_ENABLED => __('Multiple times')
        ];
    }

    /**
     * Get repeat options
     *
     * @return array
     */
    public static function getRepeatOptions()
    {
        return [
            '0' => __('Once'),
            '1' => __('Daily'),
            '2' => __('Weekly'),
            '3' => __('Monthly')
        ];
    }

    /**
     * Get formatted time slots for display
     * 
     * @return array
     */
    public function getFormattedTimeSlots()
    {
        $slots = $this->getTimeSlots();
        $formatted = [];
        
        foreach ($slots as $slot) {
            if (is_array($slot) && isset($slot['time_start']) && isset($slot['time_end'])) {
                $formatted[] = $this->formatTimeRange($slot['time_start'], $slot['time_end']);
            } elseif (is_string($slot) && strpos($slot, '-') !== false) {
                $times = explode('-', $slot);
                $formatted[] = $this->formatTimeRange(trim($times[0]), trim($times[1]));
            } else {
                // For any other format, add as is
                $formatted[] = is_string($slot) ? $slot : '';
            }
        }
        
        return $formatted;
    }
    
    /**
     * Format a time range for display
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
     * Get all time slots in the original format
     * 
     * @return array
     */
    public function getRawTimeSlots()
    {
        $slots = $this->getTimeSlots();
        
        // Ensure all slots are in the correct format
        $formattedSlots = [];
        foreach ($slots as $slot) {
            if (is_array($slot) && isset($slot['time_start']) && isset($slot['time_end'])) {
                $formattedSlots[] = $slot;
            } elseif (is_string($slot) && strpos($slot, '-') !== false) {
                $times = explode('-', $slot);
                $formattedSlots[] = [
                    'time_start' => trim($times[0]),
                    'time_end' => trim($times[1])
                ];
            }
        }
        
        return $formattedSlots;
    }
}