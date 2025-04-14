<?php
namespace Vishal\Events\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Api\Data\EventSearchResultsInterface;


interface EventRepositoryInterface
{
    /**
     * Save event
     *
     * @param EventInterface $event
     * @return EventInterface
     * @throws LocalizedException
     */
    public function save(EventInterface $event);

    /**
     * Get event by ID
     *
     * @param int $eventId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    public function getById($eventId);

    /**
     * Get empty event entity
     * 
     * @return EventInterface
     */
    public function getEmptyEntity();

    /**
     * Get event by URL key
     *
     * @param string $urlKey
     * @param int|null $storeId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $storeId = null);

    /**
     * Delete event
     *
     * @param EventInterface $event
     * @return bool
     * @throws LocalizedException
     */
    public function delete(EventInterface $event);

    /**
     * Delete event by ID
     *
     * @param int $eventId
     * @return bool
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($eventId);

    /**
     * Get event list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}