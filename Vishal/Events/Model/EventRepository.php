<?php
/**
 * EventRepository.php
 * Path: app/code/Vishal/Events/Model/EventRepository.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Model\ResourceModel\Event as ResourceEvent;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;

class EventRepository
{
    /**
     * @var ResourceEvent
     */
    private $resource;

    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var EventCollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param ResourceEvent $resource
     * @param EventFactory $eventFactory
     * @param EventCollectionFactory $eventCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface $storeManager
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        ResourceEvent $resource,
        EventFactory $eventFactory,
        EventCollectionFactory $eventCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        StoreManagerInterface $storeManager,
        ManagerInterface $eventManager
    ) {
        $this->resource = $resource;
        $this->eventFactory = $eventFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Save event
     *
     * @param EventInterface $event
     * @return EventInterface
     * @throws CouldNotSaveException
     */
    public function save(EventInterface $event)
    {
        try {
            $this->resource->save($event);
            
            // Dispatch event for URL rewrite generation
            $this->eventManager->dispatch('vishal_events_event_save_after', ['event' => $event]);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event: %1', $exception->getMessage()),
                $exception
            );
        }
        return $event;
    }

    /**
     * Get event by id
     *
     * @param int $eventId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    public function getById($eventId)
    {
        $event = $this->eventFactory->create();
        $this->resource->load($event, $eventId);
        if (!$event->getId()) {
            throw new NoSuchEntityException(__('Event with id "%1" does not exist.', $eventId));
        }
        return $event;
    }

    /**
     * Get event by URL key
     *
     * @param string $urlKey
     * @param int $storeId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $storeId)
    {
        $collection = $this->eventCollectionFactory->create();
        $collection->addFieldToFilter('url_key', $urlKey);
        $collection->addStoreFilter($storeId);
        $collection->addActiveFilter();
        
        $event = $collection->getFirstItem();
        
        if (!$event || !$event->getId()) {
            throw new NoSuchEntityException(__('Event with URL key "%1" does not exist.', $urlKey));
        }
        
        return $event;
    }

    /**
     * Delete event
     *
     * @param EventInterface $event
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EventInterface $event)
    {
        try {
            $this->resource->delete($event);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the event: %1', $exception->getMessage()),
                $exception
            );
        }
        return true;
    }

    /**
     * Delete event by id
     *
     * @param int $eventId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($eventId)
    {
        return $this->delete($this->getById($eventId));
    }

    /**
     * Get event list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->eventCollectionFactory->create();
        
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        
        // Add sortOrder
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            $collection->addOrder(
                $sortOrder->getField(),
                $sortOrder->getDirection() == 'ASC' ? 'ASC' : 'DESC'
            );
        }
        
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        
        return $searchResults;
    }
    
    /**
     * Get active events
     *
     * @param int $storeId
     * @return \Vishal\Events\Model\ResourceModel\Event\Collection
     */
    public function getActiveEvents($storeId = null)
    {
        $collection = $this->eventCollectionFactory->create();
        $collection->addActiveFilter();
        
        if ($storeId !== null) {
            $collection->addStoreFilter($storeId);
        }
        
        $collection->addStartDateOrder();
        
        return $collection;
    }
}