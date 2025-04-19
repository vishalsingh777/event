<?php

namespace Insead\Stripe\Model\Subscription\RenderSource;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Frequency
 *
 * @package Insead\Stripe\Model\Subscription\RenderSource
 */
class Frequency extends \Magento\Framework\DataObject implements OptionSourceInterface
{
    /**
     * Retrieve option array
     *
     * @return []
     */
    public static function getOptionArray()
    {
        return [
            'Yearly'  => __('Yearly'),
            'Monthly' => __('Monthly'),
            'Daily'   => __('Daily')
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
