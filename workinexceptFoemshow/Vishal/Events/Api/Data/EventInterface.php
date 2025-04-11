<?php
namespace Vishal\Events\Api\Data;

interface EventInterface
{
    /**
     * Constants for keys of data array - exactly matching form fields and database columns
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

    const REGISTRATION_TYPE = 'registration_type';
    const QTY = 'qty';

    /**
     * Get Event ID
     *
     * @return int|null
     */
    public function getEventId();

    /**
     * Set Event ID
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

    /**
     * Get Event Title
     *
     * @return string|null
     */
    public function getEventTitle();

    /**
     * Set Event Title
     *
     * @param string $eventTitle
     * @return $this
     */
    public function setEventTitle($eventTitle);

    /**
     * Get Event Venue
     *
     * @return string|null
     */
    public function getEventVenue();

    /**
     * Set Event Venue
     *
     * @param string $eventVenue
     * @return $this
     */
    public function setEventVenue($eventVenue);

    /**
     * Get URL Key
     *
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set URL Key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get Color
     *
     * @return string|null
     */
    public function getColor();

    /**
     * Set Color
     *
     * @param string $color
     * @return $this
     */
    public function setColor($color);

    /**
     * Get Start Date
     *
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set Start Date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Get End Date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set End Date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate);

    /**
     * Get Content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set Content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get YouTube Video URL
     *
     * @return string|null
     */
    public function getYoutubeVideoUrl();

    /**
     * Set YouTube Video URL
     *
     * @param string $youtubeVideoUrl
     * @return $this
     */
    public function setYoutubeVideoUrl($youtubeVideoUrl);

    /**
     * Get Status
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set Status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get Recurring
     *
     * @return int|null
     */
    public function getRecurring();

    /**
     * Set Recurring
     *
     * @param int $recurring
     * @return $this
     */
    public function setRecurring($recurring);

    /**
     * Get Repeat
     *
     * @return string|null
     */
    public function getRepeat();

    /**
     * Set Repeat
     *
     * @param string $repeat
     * @return $this
     */
    public function setRepeat($repeat);

    /**
     * Get Repeat Every
     *
     * @return int|null
     */
    public function getRepeatEvery();

    /**
     * Set Repeat Every
     *
     * @param int $repeatEvery
     * @return $this
     */
    public function setRepeatEvery($repeatEvery);

    /**
     * Get Available Days
     * 
     * @return array
     */
    public function getAvailableDays();

    /**
     * Set Available Days
     * 
     * @param array|string $availableDays
     * @return $this
     */
    public function setAvailableDays($availableDays);

    /**
     * Get Time Slots
     * 
     * @return array
     */
    public function getTimeSlots();

    /**
     * Set Time Slots
     * 
     * @param array|string $timeSlots
     * @return $this
     */
    public function setTimeSlots($timeSlots);

    /**
     * Get Blocked Dates
     * 
     * @return array
     */
    public function getBlockDates();

    /**
     * Set Blocked Dates
     * 
     * @param array|string $blockDates
     * @return $this
     */
    public function setBlockDates($blockDates);

    /**
     * Get Contact Person
     *
     * @return string|null
     */
    public function getContactPerson();

    /**
     * Set Contact Person
     *
     * @param string $contactPerson
     * @return $this
     */
    public function setContactPerson($contactPerson);

    /**
     * Get Phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set Phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);

    /**
     * Get Fax
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set Fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get Email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get Address
     *
     * @return string|null
     */
    public function getAddress();

    /**
     * Set Address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * Get Page Title
     *
     * @return string|null
     */
    public function getPageTitle();

    /**
     * Set Page Title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle);

    /**
     * Get Keywords
     *
     * @return string|null
     */
    public function getKeywords();

    /**
     * Set Keywords
     *
     * @param string $keywords
     * @return $this
     */
    public function setKeywords($keywords);

    /**
     * Get Description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Set Description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set Created At
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Updated At
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get Store IDs
     *
     * @return array|null
     */
    public function getStoreId();

    /**
     * Set Store IDs
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreId(array $storeIds);
    
    /**
     * Get Product SKU
     *
     * @return string|null
     */
    public function getProductSku();

    /**
     * Set Product SKU
     *
     * @param string $productSku
     * @return $this
     */
    public function setProductSku($productSku);

    /**
     * Get Event Price
     *
     * @return string|null
     */
    public function getEventPrice();

    /**
     * Set Event Price
     *
     * @param string $eventPrice
     * @return $this
     */
    public function setEventPrice($eventPrice);

    /**
     * Get Customer Group
     *
     * @return string|null
     */
    public function getCustomerGroup();

    /**
     * Set Customer Group
     *
     * @param string $customerGroup
     * @return $this
     */
    public function setCustomerGroup($customerGroup);

    /**
     * Get Event Timezone
     *
     * @return string|null
     */
    public function getEventTimezone();

    /**
     * Set Event Timezone
     *
     * @param string $timezone
     * @return $this
     */
    public function setEventTimezone($timezone);

    /**
     * Get Single Start Time
     *
     * @return string|null
     */
    public function getSingleStartTime();

    /**
     * Set Single Start Time
     *
     * @param string $startTime
     * @return $this
     */
    public function setSingleStartTime($startTime);

    /**
     * Get Single End Time
     *
     * @return string|null
     */
    public function getSingleEndTime();

    /**
     * Set Single End Time
     *
     * @param string $endTime
     * @return $this
     */
    public function setSingleEndTime($endTime);

    /**
     * Get Selected Time Slots
     * 
     * @return array
     */
    public function getSelectedTimeSlots();

    /**
     * Set Selected Time Slots
     * 
     * @param array|string $selectedTimeSlots
     * @return $this
     */
    public function setSelectedTimeSlots($selectedTimeSlots);

    /**
     * Get registration type
     *
     * @return string|null
     */
    public function getRegistrationType();

    /**
     * Set registration type
     *
     * @param string $registrationType
     * @return $this
     */
    public function setRegistrationType($registrationType);

    /**
     * Get quantity
     *
     * @return int|null
     */
    public function getQty();

    /**
     * Set quantity
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);
}