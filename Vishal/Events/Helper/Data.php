<?php

declare(strict_types=1);

namespace Vishal\Events\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Config path constants
     */
    const XML_PATH_EVENTS_ENABLED = 'vishal_events/general/enabled';
    const XML_PATH_EVENTS_LIST_TITLE = 'vishal_events/general/list_title';
    const XML_PATH_EVENTS_LIST_META_TITLE = 'vishal_events/general/list_meta_title';
    const XML_PATH_EVENTS_LIST_META_KEYWORDS = 'vishal_events/general/list_meta_keywords';
    const XML_PATH_EVENTS_LIST_META_DESCRIPTION = 'vishal_events/general/list_meta_description';
    const XML_PATH_EVENTS_LIST_ITEMS_PER_PAGE = 'vishal_events/general/items_per_page';
    const XML_PATH_EVENTS_URL_PREFIX = 'vishal_events/general/url_prefix';

    /**
     * Check if the module is enabled
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_EVENTS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getListTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_LIST_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list meta title
     *
     * @param int|null $storeId
     * @return string
     */
    public function getListMetaTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_LIST_META_TITLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list meta keywords
     *
     * @param int|null $storeId
     * @return string
     */
    public function getListMetaKeywords($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_LIST_META_KEYWORDS,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list meta description
     *
     * @param int|null $storeId
     * @return string
     */
    public function getListMetaDescription($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_LIST_META_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events list items per page
     *
     * @param int|null $storeId
     * @return int
     */
    public function getListItemsPerPage($storeId = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_LIST_ITEMS_PER_PAGE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get events URL prefix
     *
     * @param int|null $storeId
     * @return string
     */
    public function getUrlPrefix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_EVENTS_URL_PREFIX,
            ScopeInterface::SCOPE_STORE,
            $storeId
        ) ?: 'events';
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
     * Generate event URL
     *
     * @param \Vishal\Events\Model\Event $event
     * @return string
     */
    public function getEventUrl($event)
    {
        // Direct controller URL - most reliable
        return $this->_urlBuilder->getUrl('events/index/view', ['event_url' => $event->getUrlKey()]);
    }
}