<?php
/**
 * INSEAD Events Banner Collection
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model\ResourceModel\Banner;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Insead\Events\Model\Banner;
use Insead\Events\Model\ResourceModel\Banner as BannerResource;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'banner_id';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'insead_events_banner_collection';
    
    /**
     * @var string
     */
    protected $_eventObject = 'banner_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            Banner::class,
            BannerResource::class
        );
    }
    
    /**
     * Add store filter to collection
     *
     * @param int|array $store
     * @return $this
     */
    public function addStoreFilter($store)
    {
        if (!is_array($store)) {
            $store = [$store];
        }
        
        $this->addFilter('store_ids', ['finset' => $store], 'public');
        
        return $this;
    }
    
    /**
     * Add event filter to collection
     *
     * @param int|array $event
     * @return $this
     */
    public function addEventFilter($event)
    {
        if (!is_array($event)) {
            $event = [$event];
        }
        
        $this->addFilter('event_ids', ['finset' => $event], 'public');
        
        return $this;
    }
    
    /**
     * Add type filter to collection
     *
     * @param int $type
     * @return $this
     */
    public function addTypeFilter($type)
    {
        $this->addFilter('banner_type', $type);
        
        return $this;
    }
    
    /**
     * Add enabled filter to collection
     *
     * @return $this
     */
    public function addEnabledFilter()
    {
        $this->addFilter('status', Banner::STATUS_ENABLED);
        
        return $this;
    }
}