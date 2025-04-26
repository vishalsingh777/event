<?php
namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\Event;

class Recurring implements OptionSourceInterface
{
    /**
     * @var Event
     */
    protected $event;

    /**
     * Constructor
     *
     * @param Event $event
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->event::getRecurringOptions();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}