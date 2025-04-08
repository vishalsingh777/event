<?php
/**
 * Event.php
 * Path: app/code/Vishal/Events/Model/Event.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Model\ResourceModel\Event as EventResourceModel;

class Event extends AbstractModel implements EventInterface
{
    /**
     * Event cache tag
     */
    const CACHE_TAG = 'vishal_events';

    /**
     * @var string
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @var string
     */
    protected $_eventPrefix = 'vishal_events';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EventResourceModel::class);
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
     * Get event id
     *
     * @return int|null
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * Get event title
     *
     * @return string
     */
    public function getEventTitle()
    {
        return $this->getData(self::EVENT_TITLE);
    }

    /**
     * Set event title
     *
     * @param string $eventTitle
     * @return $this
     */
    public function setEventTitle($eventTitle)
    {
        return $this->setData(self::EVENT_TITLE, $eventTitle);
    }

    /**
     * Get event venue
     *
     * @return string|null
     */
    public function getEventVenue()
    {
        return $this->getData(self::EVENT_VENUE);
    }

    /**
     * Set event venue
     *
     * @param string $eventVenue
     * @return $this
     */
    public function setEventVenue($eventVenue)
    {
        return $this->setData(self::EVENT_VENUE, $eventVenue);
    }

    /**
     * Get URL key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(self::URL_KEY);
    }

    /**
     * Set URL key
     *
     * @param string $urlKey
     * @return $this
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Get color
     *
     * @return string|null
     */
    public function getColor()
    {
        return $this->getData(self::COLOR);
    }

    /**
     * Set color
     *
     * @param string $color
     * @return $this
     */
    public function setColor($color)
    {
        return $this->setData(self::COLOR, $color);
    }

    /**
     * Get start date
     *
     * @return string
     */
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    /**
     * Set start date
     *
     * @param string $startDate
     * @return $this
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get end date
     *
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    /**
     * Set end date
     *
     * @param string $endDate
     * @return $this
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get content
     *
     * @return string|null
     */
    public function getContent()
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * Set content
     *
     * @param string $content
     * @return $this
     */
    public function setContent($content)
    {
        return $this->setData(self::CONTENT, $content);
    }

    /**
     * Get YouTube video URL
     *
     * @return string|null
     */
    public function getYoutubeVideoUrl()
    {
        return $this->getData(self::YOUTUBE_VIDEO_URL);
    }

    /**
     * Set YouTube video URL
     *
     * @param string $youtubeVideoUrl
     * @return $this
     */
    public function setYoutubeVideoUrl($youtubeVideoUrl)
    {
        return $this->setData(self::YOUTUBE_VIDEO_URL, $youtubeVideoUrl);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get recurring flag
     *
     * @return int
     */
    public function getRecurring()
    {
        return $this->getData(self::RECURRING);
    }

    /**
     * Set recurring flag
     *
     * @param int $recurring
     * @return $this
     */
    public function setRecurring($recurring)
    {
        return $this->setData(self::RECURRING, $recurring);
    }

    /**
     * Get repeat type
     *
     * @return string|null
     */
    public function getRepeat()
    {
        return $this->getData(self::REPEAT);
    }

    /**
     * Set repeat type
     *
     * @param string $repeat
     * @return $this
     */
    public function setRepeat($repeat)
    {
        return $this->setData(self::REPEAT, $repeat);
    }

    /**
     * Get repeat every
     *
     * @return int|null
     */
    public function getRepeatEvery()
    {
        return $this->getData(self::REPEAT_EVERY);
    }

    /**
     * Set repeat every
     *
     * @param int $repeatEvery
     * @return $this
     */
    public function setRepeatEvery($repeatEvery)
    {
        return $this->setData(self::REPEAT_EVERY, $repeatEvery);
    }

    /**
     * Get contact person
     *
     * @return string|null
     */
    public function getContactPerson()
    {
        return $this->getData(self::CONTACT_PERSON);
    }

    /**
     * Set contact person
     *
     * @param string $contactPerson
     * @return $this
     */
    public function setContactPerson($contactPerson)
    {
        return $this->setData(self::CONTACT_PERSON, $contactPerson);
    }

    /**
     * Get phone
     *
     * @return string|null
     */
    public function getPhone()
    {
        return $this->getData(self::PHONE);
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        return $this->setData(self::PHONE, $phone);
    }

    /**
     * Get fax
     *
     * @return string|null
     */
    public function getFax()
    {
        return $this->getData(self::FAX);
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return $this
     */
    public function setFax($fax)
    {
        return $this->setData(self::FAX, $fax);
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get address
     *
     * @return string|null
     */
    public function getAddress()
    {
        return $this->getData(self::ADDRESS);
    }

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setData(self::ADDRESS, $address);
    }

    /**
     * Get page title
     *
     * @return string|null
     */
    public function getPageTitle()
    {
        return $this->getData(self::PAGE_TITLE);
    }

    /**
     * Set page title
     *
     * @param string $pageTitle
     * @return $this
     */
    public function setPageTitle($pageTitle)
    {
        return $this->setData(self::PAGE_TITLE, $pageTitle);
    }

    /**
     * Get meta keywords
     *
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * Set meta keywords
     *
     * @param string $metaKeywords
     * @return $this
     */
    public function setMetaKeywords($metaKeywords)
    {
        return $this->setData(self::META_KEYWORDS, $metaKeywords);
    }

    /**
     * Get meta description
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * Set meta description
     *
     * @param string $metaDescription
     * @return $this
     */
    public function setMetaDescription($metaDescription)
    {
        return $this->setData(self::META_DESCRIPTION, $metaDescription);
    }

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get store ids
     *
     * @return int[]
     */
    public function getStoreId()
    {
        return $this->hasData(self::STORE_ID) ? $this->getData(self::STORE_ID) : [];
    }

    /**
     * Set store ids
     *
     * @param int[] $storeId
     * @return $this
     */
    public function setStoreId(array $storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }
}