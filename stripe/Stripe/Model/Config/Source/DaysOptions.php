<?php

namespace Insead\Stripe\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class DaysOptions implements ArrayInterface
{
    /**
     * Provide available days options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $maxDays = 31;
        for ($i = 1; $i <= $maxDays; $i++) {
            $options[] = [
                'value' => $i,
                'label' => __($i . ' ' . ($i == 1 ? 'Day' : 'Days'))
            ];
        }
        return $options;
    }
}
