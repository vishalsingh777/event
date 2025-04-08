<?php
/**
 * EventTicketInterface.php
 * Path: app/code/Vishal/Events/Api/Data/EventTicketInterface.php
 */

declare(strict_types=1);

namespace Vishal\Events\Api\Data;

interface EventTicketInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const TICKET_ID = 'ticket_id';
    const EVENT_ID = 'event_id';
    const NAME = 'name';
    const SKU = 'sku';
    const PRICE = 'price';
    const POSITION = 'position';
    const PRODUCT_ID = 'product_id';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ticket id
     *
     * @return int|null
     */
    public function getTicketId();

    /**
     * Set ticket id
     *
     * @param int $ticketId
     * @return $this
     */
    public function setTicketId($ticketId);

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
     * Get ticket name
     *
     * @return string
     */
    public function getName();

    /**
     * Set ticket name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get ticket SKU
     *
     * @return string
     */
    public function getSku();

    /**
     * Set ticket SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get ticket price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set ticket price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set product id
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * Get creation time
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set creation time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get update time
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * Set update time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}