<?php
namespace Vishal\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Vishal\Events\Model\Event;

class Status implements OptionSourceInterface
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
        $availableOptions = $this->event::getAvailableStatuses();
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