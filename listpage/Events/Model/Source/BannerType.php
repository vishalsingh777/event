<?php
/**
 * INSEAD Events Banner Type Source Model
 *
 * @category  Insead
 * @package   Insead\Events
 */
declare(strict_types=1);

namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\Banner;

class BannerType implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        $availableOptions = Banner::getAvailableBannerTypes();
        
        foreach ($availableOptions as $value => $label) {
            $options[] = [
                'label' => $label,
                'value' => $value
            ];
        }
        
        return $options;
    }
}