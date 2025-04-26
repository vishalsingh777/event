<?php
namespace Insead\Events\Api\Data;

interface EventRegistrationInterface
{
    /**
     * Constants for keys of data array
     */
    const REGISTRATION_ID = 'registration_id';
    const EVENT_ID = 'event_id';
    const FIRST_NAME = 'first_name';
    const LAST_NAME = 'last_name';
    const EMAIL = 'email';
    const STREET = 'street';
    const CITY = 'city';
    const COUNTRY = 'country';
    const ZIPCODE = 'zipcode';
    const SELECTED_DATE = 'selected_date';
    const TIME_SLOT = 'time_slot';
    const PAYMENT_STATUS = 'payment_status';
    const PAYMENT_CURRENCY = 'payment_currency';
    const PAYMENT_METHOD = 'payment_method';
    const PAYMENT_REFERENCE = 'payment_reference';
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get registration id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set registration id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get event id
     *
     * @return int
     */
    public function getEventId();

    /**
     * Set event id
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

    /**
     * Get first name
     *
     * @return string|null
     */
    public function getFirstName();

    /**
     * Set first name
     *
     * @param string $firstName
     * @return $this
     */
    public function setFirstName($firstName);

    /**
     * Get last name
     *
     * @return string|null
     */
    public function getLastName();

    /**
     * Set last name
     *
     * @param string $lastName
     * @return $this
     */
    public function setLastName($lastName);

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);

    /**
     * Get street
     *
     * @return string|null
     */
    public function getStreet();

    /**
     * Set street
     *
     * @param string $street
     * @return $this
     */
    public function setStreet($street);

    /**
     * Get city
     *
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     *
     * @param string $city
     * @return $this
     */
    public function setCity($city);

    /**
     * Get country
     *
     * @return string|null
     */
    public function getCountry();

    /**
     * Set country
     *
     * @param string $country
     * @return $this
     */
    public function setCountry($country);

    /**
     * Get zipcode
     *
     * @return string|null
     */
    public function getZipcode();

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return $this
     */
    public function setZipcode($zipcode);
    
    /**
     * Get selected date
     *
     * @return string|null
     */
    public function getSelectedDate();

    /**
     * Set selected date
     *
     * @param string $date
     * @return $this
     */
    public function setSelectedDate($date);
    
    /**
     * Get time slot
     *
     * @return string|null
     */
    public function getTimeSlot();

    /**
     * Set time slot
     *
     * @param string $timeSlot
     * @return $this
     */
    public function setTimeSlot($timeSlot);
    
    /**
     * Get payment status
     *
     * @return string|null
     */
    public function getPaymentStatus();

    /**
     * Set payment status
     *
     * @param string $paymentStatus
     * @return $this
     */
    public function setPaymentStatus($paymentStatus);
    
    /**
     * Get payment currency
     *
     * @return string|null
     */
    public function getPaymentCurrency();

    /**
     * Set payment currency
     *
     * @param string $paymentCurrency
     * @return $this
     */
    public function setPaymentCurrency($paymentCurrency);
    
    /**
     * Get payment method
     *
     * @return string|null
     */
    public function getPaymentMethod();

    /**
     * Set payment method
     *
     * @param string $paymentMethod
     * @return $this
     */
    public function setPaymentMethod($paymentMethod);
    
    /**
     * Get payment reference
     *
     * @return string|null
     */
    public function getPaymentReference();

    /**
     * Set payment reference
     *
     * @param string $paymentReference
     * @return $this
     */
    public function setPaymentReference($paymentReference);

    /**
     * Get order id
     *
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order id
     *
     * @param string $orderId
     * @return $this
     */
    public function setOrderId($orderId);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set status
     *
     * @param int $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}