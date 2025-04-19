<?php
namespace Insead\Stripe\Model\Subscription\RenderSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class LiveModeStatus
 *
 * @package Insead\Stripe\Model\Subscription\RenderSource
 */
class LiveModeStatus extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    const STATUS_TEST = 0;
    const STATUS_LIVE = 1;

    /**
     * Retrieve option array
     *
     * @return []
     */
    public static function getOptionArray()
    {
        return [
            self::STATUS_TEST    => __('Test'),
            self::STATUS_LIVE => __('Live')
        ];
    }

    /**
     * Retrieve all options
     *
     * @return []
     */
    public static function getAllOptions()
    {
        $res = [];
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = ['value' => $index, 'label' => $value];
        }

        return $res;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
