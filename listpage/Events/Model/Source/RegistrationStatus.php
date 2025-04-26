<?php
namespace Insead\Events\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Insead\Events\Model\EventRegistration;

class RegistrationStatus implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => EventRegistration::STATUS_PENDING, 'label' => __('Pending Approval')],
            ['value' => EventRegistration::STATUS_REGISTERED, 'label' => __('Registered/Approved')],
            ['value' => EventRegistration::STATUS_REJECTED, 'label' => __('Rejected')]
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
            EventRegistration::STATUS_PENDING => __('Pending Approval'),
            EventRegistration::STATUS_REGISTERED => __('Registered/Approved'),
            EventRegistration::STATUS_REJECTED => __('Rejected')
        ];
    }
}