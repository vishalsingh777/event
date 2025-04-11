<?php
namespace Vishal\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Timezone implements OptionSourceInterface
{
    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $localeLists;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(
        \Magento\Framework\Locale\ListsInterface $localeLists
    ) {
        $this->localeLists = $localeLists;
    }

    /**
     * Get options for timezone dropdown
     *
     * @return array
     */
    public function toOptionArray()
    {
        // Get the default timezone options from Magento
       // $timezones = $this->localeLists->getOptionTimezones();
        
        // Alternatively, you can specify a limited set of common timezones
        // Uncomment and modify this if you prefer a shorter list
        
        $commonTimezones = [
            ['value' => 'UTC', 'label' => __('UTC')],
            ['value' => 'Asia/Singapore', 'label' => __('Asia/Singapore (GMT+8)')],
            ['value' => 'America/New_York', 'label' => __('America/New_York (GMT-5)')],
            ['value' => 'America/Chicago', 'label' => __('America/Chicago (GMT-6)')],
            ['value' => 'America/Denver', 'label' => __('America/Denver (GMT-7)')],
            ['value' => 'America/Los_Angeles', 'label' => __('America/Los_Angeles (GMT-8)')],
            ['value' => 'Europe/London', 'label' => __('Europe/London (GMT+0)')],
            ['value' => 'Europe/Paris', 'label' => __('Europe/Paris (GMT+1)')],
            ['value' => 'Asia/Singapore', 'label' => __('Asia/Singapore (GMT+8)')],
            ['value' => 'Australia/Sydney', 'label' => __('Australia/Sydney (GMT+11)')]
        ];
        return $commonTimezones;
        
        
        //return $timezones;
    }
}