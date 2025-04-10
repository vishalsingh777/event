<?php
namespace Vishal\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Color implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'green', 'label' => __('Green')],
            ['value' => 'blue', 'label' => __('Blue')],
            ['value' => 'red', 'label' => __('Red')],
            ['value' => 'orange', 'label' => __('Orange')],
            ['value' => 'purple', 'label' => __('Purple')]
        ];
    }
}