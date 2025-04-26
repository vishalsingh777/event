<?php
namespace Insead\Events\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ViewMode implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'grid', 'label' => __('Grid')],
            ['value' => 'list', 'label' => __('List')],
            ['value' => 'calendar', 'label' => __('Calendar')]
        ];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'grid' => __('Grid'),
            'list' => __('List'),
            'calendar' => __('Calendar')
        ];
    }
} 