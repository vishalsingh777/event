<?php
namespace Vishal\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Vishal\Events\Model\ResourceModel\EventRegistration as EventRegistrationResource;

class EventRegistration extends AbstractModel
{
    /**
     * Registration status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'vishal_event_registration';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(EventRegistrationResource::class);
    }
    
    /**
     * Check if registration is pending
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->getStatus() === self::STATUS_PENDING;
    }
    
    /**
     * Check if registration is approved
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->getStatus() === self::STATUS_APPROVED;
    }
    
    /**
     * Check if registration is rejected
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->getStatus() === self::STATUS_REJECTED;
    }
    
    /**
     * Get registration full name
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }
    
    /**
     * Get formatted address
     *
     * @return string
     */
    public function getFormattedAddress()
    {
        $parts = [];
        
        if ($this->getStreet()) {
            $parts[] = $this->getStreet();
        }
        
        if ($this->getCity()) {
            $parts[] = $this->getCity();
        }
        
        if ($this->getZipcode()) {
            $parts[] = $this->getZipcode();
        }
        
        if ($this->getCountry()) {
            $parts[] = $this->getCountry();
        }
        
        return implode(', ', $parts);
    }
}