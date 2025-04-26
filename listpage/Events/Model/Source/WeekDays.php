<?php
declare(strict_types=1);
namespace Insead\Events\Model\Source;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;

class WeekDays implements OptionSourceInterface
{
    /**
     * Week day values - using numeric values to match existing available_days
     */
    const MONDAY = '1';
    const TUESDAY = '2';
    const WEDNESDAY = '3';
    const THURSDAY = '4';
    const FRIDAY = '5';
    const SATURDAY = '6';
    const SUNDAY = '0';
    
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
            ['value' => self::MONDAY, 'label' => __('Monday')],
            ['value' => self::TUESDAY, 'label' => __('Tuesday')],
            ['value' => self::WEDNESDAY, 'label' => __('Wednesday')],
            ['value' => self::THURSDAY, 'label' => __('Thursday')],
            ['value' => self::FRIDAY, 'label' => __('Friday')],
            ['value' => self::SATURDAY, 'label' => __('Saturday')],
            ['value' => self::SUNDAY, 'label' => __('Sunday')]
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
            self::MONDAY => __('Monday'),
            self::TUESDAY => __('Tuesday'),
            self::WEDNESDAY => __('Wednesday'),
            self::THURSDAY => __('Thursday'),
            self::FRIDAY => __('Friday'),
            self::SATURDAY => __('Saturday'),
            self::SUNDAY => __('Sunday')
        ];
    }
}       