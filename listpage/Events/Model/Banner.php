<?php
/**
 * INSEAD Events Banner Model
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Insead\Events\Model\ResourceModel\Banner as BannerResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Banner extends AbstractModel
{
    /**
     * Banner statuses
     */
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    
    /**
     * Banner types
     */
    public const TYPE_LISTING = 1;
    public const TYPE_EVENT = 2;
    
    /**
     * Media types
     */
    public const MEDIA_TYPE_IMAGE = 1;
    public const MEDIA_TYPE_VIDEO = 2;
    
    /**
     * @var string
     */
    protected $_cacheTag = 'insead_events_banner';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'insead_events_banner';

    /**
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(BannerResource::class);
    }
    
    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities(): array
    {
        return [$this->_cacheTag . '_' . $this->getId()];
    }
    
    /**
     * Get available statuses
     *
     * @return array
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }
    
    /**
     * Get available banner types
     *
     * @return array
     */
    public static function getAvailableBannerTypes(): array
    {
        return [
            self::TYPE_LISTING => __('Event Listing Page'),
            self::TYPE_EVENT => __('Event Detail Page')
        ];
    }
    
    /**
     * Get available media types
     *
     * @return array
     */
    public static function getAvailableMediaTypes(): array
    {
        return [
            self::MEDIA_TYPE_IMAGE => __('Image'),
            self::MEDIA_TYPE_VIDEO => __('Video')
        ];
    }
    
    /**
     * Set store IDs
     *
     * @param array|string $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds): self
    {
        if (is_array($storeIds)) {
            $this->setData('store_ids', implode(',', $storeIds));
        } else {
            $this->setData('store_ids', $storeIds);
        }
        return $this;
    }
    
    /**
     * Get store IDs
     *
     * @return array
     */
    public function getStoreIds(): array
    {
        $storeIds = $this->getData('store_ids');
        if (is_string($storeIds) && !empty($storeIds)) {
            return explode(',', $storeIds);
        }
        return is_array($storeIds) ? $storeIds : [];
    }
    
    /**
     * Set event IDs
     *
     * @param array|string $eventIds
     * @return $this
     */
    public function setEventIds($eventIds): self
    {
        if (is_array($eventIds)) {
            $this->setData('event_ids', implode(',', $eventIds));
        } else {
            $this->setData('event_ids', $eventIds);
        }
        return $this;
    }
    
    /**
     * Get event IDs
     *
     * @return array
     */
    public function getEventIds(): array
    {
        $eventIds = $this->getData('event_ids');
        if (is_string($eventIds) && !empty($eventIds)) {
            return explode(',', $eventIds);
        }
        return is_array($eventIds) ? $eventIds : [];
    }
    
}