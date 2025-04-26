<?php
namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class TimeStart implements OptionSourceInterface
{
    /**
     * Get options for time start dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $formattedHour = str_pad($hour, 2, '0', STR_PAD_LEFT);

            // Add :00
            $timeValue = $formattedHour . ':00';
            $options[] = [
                'value' => $timeValue,
                'label' => $timeValue
            ];

            // Add :30, but only if it's not 23:30 (which is allowed)
            if (!($hour === 23)) {
                $timeValue = $formattedHour . ':30';
                $options[] = [
                    'value' => $timeValue,
                    'label' => $timeValue
                ];
            }
        }

        return $options;
    }
}