<?php
namespace Vishal\Events\Api\Data;

interface EventInterface
{
    /**
     * Constants for keys of data array
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
}