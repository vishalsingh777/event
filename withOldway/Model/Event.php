<?php
namespace Vishal\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class Event extends AbstractModel
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
            self::RECURRING_ENABLED => __('Enable'),
            self::RECURRING_DISABLED => __('Disable')
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
            'daily' => __('Daily'),
            'weekly' => __('Weekly'),
            'monthly' => __('Monthly'),
            'yearly' => __('Yearly')
        ];
    }

    /**
     * Get product IDs
     *
     * @return string
     */
    public function getProductIds()
    {
        return $this->getData('product_ids');
    }

    /**
     * Set product IDs
     *
     * @param string $productIds
     * @return $this
     */
    public function setProductIds($productIds)
    {
        return $this->setData('product_ids', $productIds);
    }
}