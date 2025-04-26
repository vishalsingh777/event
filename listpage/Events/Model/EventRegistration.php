<?php
namespace Insead\Events\Model;

use Magento\Framework\Model\AbstractModel;
use Insead\Events\Model\ResourceModel\EventRegistration as EventRegistrationResource;
use Insead\Events\Api\Data\EventRegistrationInterface;

class EventRegistration extends AbstractModel implements EventRegistrationInterface
{
    /**
     * Registration status constants
     */
    const STATUS_PENDING = 0;
    const STATUS_REGISTERED = 1;
    const STATUS_APPROVED = 1;
    const STATUS_REJECTED = 2;
    
    /**
     * Payment status constants
     */
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_REFUNDED = 'refunded';
    
    /**
     * @var string
     */
    protected $_eventPrefix = 'insead_event_registration';

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
     * Get registration id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::REGISTRATION_ID);
    }

    /**
     * Set registration id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::REGISTRATION_ID, $id);
    }

    /**
     * Get event id
     *
     * @return int
     */
    public function getEventId()
    {
        return $this->getData(self::EVENT_ID);
    }

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId)
    {
        return $this->setData(self::EVENT_ID, $eventId);
    }

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstName()
    {
        return $this->getData(self::FIRST_NAME);
    }

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName)
    {
        return $this->setData(self::FIRST_NAME, $firstName);
    }

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastName()
    {
        return $this->getData(self::LAST_NAME);
    }

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName)
    {
        return $this->setData(self::LAST_NAME, $lastName);
    }

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get street
     *
     * @return string|null
     */
    public function getStreet()
    {
        return $this->getData(self::STREET);
    }

    /**
     * Set street
     *
     * @param string $street
     * @return $this
     */
    public function setStreet($street)
    {
        return $this->setData(self::STREET, $street);
    }

    /**
     * Get city
     *
     * @return string|null
     */
    public function getCity()
    {
        return $this->getData(self::CITY);
    }

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city)
    {
        return $this->setData(self::CITY, $city);
    }

    /**
     * Get country
     *
     * @return string|null
     */
    public function getCountry()
    {
        return $this->getData(self::COUNTRY);
    }

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        return $this->setData(self::COUNTRY, $country);
    }

    /**
     * Get zipcode
     *
     * @return string|null
     */
    public function getZipcode()
    {
        return $this->getData(self::ZIPCODE);
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return $this
     */
    public function setZipcode($zipcode)
    {
        return $this->setData(self::ZIPCODE, $zipcode);
    }
    
    /**
     * Get selected date
     *
     * @return string|null
     */
    public function getSelectedDate()
    {
        return $this->getData(self::SELECTED_DATE);
    }

    /**
     * Set selected date
     *
     * @param string $date
     * @return $this
     */
    public function setSelectedDate($date)
    {
        return $this->setData(self::SELECTED_DATE, $date);
    }
    
    /**
     * Get time slot
     *
     * @return string|null
     */
    public function getTimeSlot()
    {
        return $this->getData(self::TIME_SLOT);
    }

    /**
     * Set time slot
     *
     * @param string $timeSlot
     * @return $this
     */
    public function setTimeSlot($timeSlot)
    {
        return $this->setData(self::TIME_SLOT, $timeSlot);
    }
    
    /**
     * Get payment status
     *
     * @return string|null
     */
    public function getPaymentStatus()
    {
        return $this->getData(self::PAYMENT_STATUS);
    }

    /**
     * Set payment status
     *
     * @param string $paymentStatus
     * @return $this
     */
    public function setPaymentStatus($paymentStatus)
    {
        return $this->setData(self::PAYMENT_STATUS, $paymentStatus);
    }
    
    /**
     * Get payment currency
     *
     * @return string|null
     */
    public function getPaymentCurrency()
    {
        return $this->getData(self::PAYMENT_CURRENCY);
    }

    /**
     * Set payment currency
     *
     * @param string $paymentCurrency
     * @return $this
     */
    public function setPaymentCurrency($paymentCurrency)
    {
        return $this->setData(self::PAYMENT_CURRENCY, $paymentCurrency);
    }
    
    /**
     * Get payment method
     *
     * @return string|null
     */
    public function getPaymentMethod()
    {
        return $this->getData(self::PAYMENT_METHOD);
    }

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod)
    {
        return $this->setData(self::PAYMENT_METHOD, $paymentMethod);
    }
    
    /**
     * Get payment reference
     *
     * @return string|null
     */
    public function getPaymentReference()
    {
        return $this->getData(self::PAYMENT_REFERENCE);
    }

    /**
     * Set payment reference
     *
     * @param string $paymentReference
     * @return $this
     */
    public function setPaymentReference($paymentReference)
    {
        return $this->setData(self::PAYMENT_REFERENCE, $paymentReference);
    }
    
    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Set order id
     *
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    
    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
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
        return trim($this->getFirstName() . ' ' . $this->getLastName());
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