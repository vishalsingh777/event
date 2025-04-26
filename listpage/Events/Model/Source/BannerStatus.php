<?php
/**
 * INSEAD Events BannerStatus Source Model
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\Banner;

class BannerStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $availableOptions = Banner::getAvailableStatuses();
        
        foreach ($availableOptions as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        
        return $options;
    }
}