<?php
namespace Vishal\Events\Api\Data;

interface EventTicketInterface
{
    /**
     * Constants for keys of data array
     */
    const TICKET_ID = 'ticket_id';
    const EVENT_ID = 'event_id';
    const NAME = 'name';
    const SKU = 'sku';
    const PRICE = 'price';
    const POSITION = 'position';
    const PRODUCT_ID = 'product_id';

    /**
     * Get Ticket ID
     *
     * @return int|null
     */
    public function getTicketId();

    /**
     * Set Ticket ID
     *
     * @param int $ticketId
     * @return $this
     */
    public function setTicketId($ticketId);

    /**
     * Get Event ID
     *
     * @return int|null
     */
    public function getEventId();

    /**
     * Set Event ID
     *
     * @param int $eventId
     * @return $this
     */
    public function setEventId($eventId);

    /**
     * Get Name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set Name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get SKU
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Set Price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get Position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set Position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get Product ID
     *
     * @return int|null
     */
    public function getProductId();

    /**
     * Set Product ID
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);
}