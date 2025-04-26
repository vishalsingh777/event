<?php
declare(strict_types=1);

namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

class IsActive implements OptionSourceInterface
{
    /**
     * Status values
     */
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_ENABLED, 'label' => __('Enabled')],
            ['value' => self::STATUS_DISABLED, 'label' => __('Disabled')]
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
            self::STATUS_ENABLED => __('Enabled'),
            self::STATUS_DISABLED => __('Disabled')
        ];
    }
}

