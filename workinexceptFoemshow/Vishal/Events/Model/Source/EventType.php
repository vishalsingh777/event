<?php
namespace Vishal\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class EventType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Paid Event')],
            ['value' => 1, 'label' => __('Registration Event')],
            ['value' => 2, 'label' => __('Registration with Approval')]
        ];
    }
}