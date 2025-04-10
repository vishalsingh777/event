<?php
namespace Vishal\Events\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vishal\Events\Api\Data\EventInterface;
use Vishal\Events\Api\Data\EventInterfaceFactory;
use Vishal\Events\Api\EventRepositoryInterface;
use Vishal\Events\Model\ResourceModel\Event as EventResource;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Vishal\Events\Api\Data\EventSearchResultsInterfaceFactory;
use Magento\Framework\Event\ManagerInterface;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;
    
    /**
     * @var EventSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;
    
    /**
     * @var EventResource
     */
    private $resource;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var EventCollectionFactory
     */
    private $eventCollectionFactory;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @param EventResource $resource
     * @param EventInterfaceFactory $eventFactory
     * @param EventCollectionFactory $eventCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param EventSearchResultsInterfaceFactory $searchResultsFactory
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        EventResource $resource,
        EventInterfaceFactory $eventFactory,
        EventCollectionFactory $eventCollectionFactory,
        CollectionProcessorInterface $collectionProcessor,
        EventSearchResultsInterfaceFactory $searchResultsFactory,
        ManagerInterface $eventManager
    ) {
        $this->resource = $resource;
        $this->eventFactory = $eventFactory;
        $this->eventCollectionFactory = $eventCollectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
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
     * Load event by ID
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
            throw new NoSuchEntityException(__('The event with the "%1" ID doesn\'t exist.', $eventId));
        }
        return $event;
    }

    /**
     * Get event by URL key
     *
     * @param string $urlKey
     * @param int|null $storeId
     * @return EventInterface
     * @throws NoSuchEntityException
     */
    public function getByUrlKey($urlKey, $storeId = null)
    {
        $event = $this->eventFactory->create();
        $eventId = $this->resource->getIdByUrlKey($urlKey);
        if (!$eventId) {
            throw new NoSuchEntityException(__('The event with the "%1" URL key doesn\'t exist.', $urlKey));
        }
        $this->resource->load($event, $eventId);
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
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete event by ID
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
        
        $this->collectionProcessor->process($searchCriteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }
    
    /**
     * Get empty event entity
     * 
     * @return EventInterface
     */
    public function getEmptyEntity()
    {
        return $this->eventFactory->create();
    }
}
