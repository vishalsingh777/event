<?php
declare(strict_types=1);

namespace Vishal\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

class RepeatType implements OptionSourceInterface
{
    /**
     * Repeat type values
     */
    const TYPE_NONE = '';
    const TYPE_DAILY = 'daily';
    const TYPE_WEEKLY = 'weekly';
    const TYPE_MONTHLY = 'monthly';
    const TYPE_YEARLY = 'yearly';

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
            ['value' => self::TYPE_NONE, 'label' => __('-- Please Select --')],
            ['value' => self::TYPE_DAILY, 'label' => __('Daily')],
            ['value' => self::TYPE_WEEKLY, 'label' => __('Weekly')],
            ['value' => self::TYPE_MONTHLY, 'label' => __('Monthly')],
            ['value' => self::TYPE_YEARLY, 'label' => __('Yearly')]
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
            self::TYPE_NONE => __('-- Please Select --'),
            self::TYPE_DAILY => __('Daily'),
            self::TYPE_WEEKLY => __('Weekly'),
            self::TYPE_MONTHLY => __('Monthly'),
            self::TYPE_YEARLY => __('Yearly')
        ];
    }
}