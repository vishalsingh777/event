<?php
namespace Vishal\Events\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for event search results.
 * @api
 */
interface EventSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get events list.
     *
     * @return \Vishal\Events\Api\Data\EventInterface[]
     */
    public function getItems();

    /**
     * Set events list.
     *
     * @param \Vishal\Events\Api\Data\EventInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}