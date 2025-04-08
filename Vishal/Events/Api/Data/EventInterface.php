<?php
/**
 * EventInterface.php
 * Path: app/code/Vishal/Events/Api/Data/EventInterface.php
 */

declare(strict_types=1);

namespace Vishal\Events\Api\Data;

interface EventInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
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
    const META_KEYWORDS = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_ID = 'store_id';

    /**
     * Get event id
     *
     * @return int|null
     */
    public function getEventId();

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

    /**
     * Get event title
     *
     * @return string
     */
    public function getEventTitle();

    /**
     * Set event title
     *
     * @param string $eventTitle
     * @return $this
     */
    public function setEventTitle($eventTitle);

    /**
     * Get event venue
     *
     * @return string|null
     */
    public function getEventVenue();

    /**
     * Set event venue
     *
     * @param string $eventVenue
     * @return $this
     */
    public function setEventVenue($eventVenue);

    /**
     * Get URL key
     *
     * @return string
     */
    public function getUrlKey();

    /**
     * Set URL key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey);

    /**
     * Get color
     *
     * @return string|null
     */
    public function getColor();

    /**
     * Set color
     *
     * @param string $color
     * @return $this
     */
    public function setColor($color);

    /**
     * Get start date
     *
     * @return string
     */
    public function getStartDate();

    /**
     * Set start date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate);

    /**
     * Get end date
     *
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate);

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent();

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content);

    /**
     * Get YouTube video URL
     *
     * @return string|null
     */
    public function getYoutubeVideoUrl();

    /**
     * Set YouTube video URL
     *
     * @param string $youtubeVideoUrl
     * @return $this
     */
    public function setYoutubeVideoUrl($youtubeVideoUrl);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get recurring flag
     *
     * @return int
     */
    public function getRecurring();

    /**
     * Set recurring flag
     *
     * @param int $recurring
     * @return $this
     */
    public function setRecurring($recurring);

    /**
     * Get repeat type
     *
     * @return string|null
     */
    public function getRepeat();

    /**
     * Set repeat type
     *
     * @param string $repeat
     * @return $this
     */
    public function setRepeat($repeat);

    /**
     * Get repeat every
     *
     * @return int|null
     */
    public function getRepeatEvery();

    /**
     * Set repeat every
     *
     * @param int $repeatEvery
     * @return $this
     */
    public function setRepeatEvery($repeatEvery);

    /**
     * Get contact person
     *
     * @return string|null
     */
    public function getContactPerson();

    /**
     * Set contact person
     *
     * @param string $contactPerson
     * @return $this
     */
    public function setContactPerson($contactPerson);

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone();

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone);

    /**
     * Get fax
     *
     * @return string|null
     */
    public function getFax();

    /**
     * Set fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax);

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get address
     *
     * @return string|null
     */
    public function getAddress();

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address);

    /**
     * Get page title
     *
     * @return string|null
     */
    public function getPageTitle();

    /**
     * Set page title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle);

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords();

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords($metaKeywords);

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription();

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription);

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get store ids
     *
     * @return int[]
     */
    public function getStoreId();

    /**
     * Set store ids
     *
     * @param int[] $storeId
     * @return $this
     */
    public function setStoreId(array $storeId);
}