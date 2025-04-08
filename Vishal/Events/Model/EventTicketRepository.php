<?php
/**
 * EventTicketRepository.php
 * Path: app/code/Vishal/Events/Model/EventTicketRepository.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vishal\Events\Api\Data\EventTicketInterface;
use Vishal\Events\Model\ResourceModel\EventTicket as ResourceEventTicket;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory as EventTicketCollectionFactory;

class EventTicketRepository
{
    /**
     * @var ResourceEventTicket
     */
    private $resource;

    /**
     * @var EventTicketFactory
     */
    private $eventTicketFactory;

    /**
     * @var EventTicketCollectionFactory
     */
    private $eventTicketCollectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @param ResourceEventTicket $resource
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketCollectionFactory $eventTicketCollectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ResourceEventTicket $resource,
        EventTicketFactory $eventTicketFactory,
        EventTicketCollectionFactory $eventTicketCollectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketCollectionFactory = $eventTicketCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Save event ticket
     *
     * @param EventTicketInterface $eventTicket
     * @return EventTicketInterface
     * @throws CouldNotSaveException
     */
    public function save(EventTicketInterface $eventTicket)
    {
        try {
            $this->resource->save($eventTicket);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event ticket: %1', $exception->getMessage()),
                $exception
            );
        }
        return $eventTicket;
    }

    /**
     * Get event ticket by id
     *
     * @param int $ticketId
     * @return EventTicketInterface
     * @throws NoSuchEntityException
     */
    public function getById($ticketId)
    {
        $eventTicket = $this->eventTicketFactory->create();
        $this->resource->load($eventTicket, $ticketId);
        if (!$eventTicket->getId()) {
            throw new NoSuchEntityException(__('Event ticket with id "%1" does not exist.', $ticketId));
        }
        return $eventTicket;
    }

    /**
     * Delete event ticket
     *
     * @param EventTicketInterface $eventTicket
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EventTicketInterface $eventTicket)
    {
        try {
            $this->resource->delete($eventTicket);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the event ticket: %1', $exception->getMessage()),
                $exception
            );
        }
        return true;
    }

    /**
     * Delete event ticket by id
     *
     * @param int $ticketId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($ticketId)
    {
        return $this->delete($this->getById($ticketId));
    }

    /**
     * Get tickets for an event
     *
     * @param int $eventId
     * @return \Vishal\Events\Model\ResourceModel\EventTicket\Collection
     */
    public function getTicketsForEvent($eventId)
    {
        $collection = $this->eventTicketCollectionFactory->create();
        $collection->addEventFilter($eventId);
        $collection->addPositionOrder();
        return $collection;
    }

    /**
     * Get event ticket list
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->eventTicketCollectionFactory->create();
        
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
                ($sortOrder->getDirection() == 'ASC') ? 'ASC' : 'DESC'
            );
        }
        
        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        
        return $searchResults;
    }
    
    /**
     * Delete tickets for event
     *
     * @param int $eventId
     * @return void
     */
    public function deleteTicketsByEventId($eventId)
    {
        $collection = $this->getTicketsForEvent($eventId);
        foreach ($collection as $ticket) {
            $this->delete($ticket);
        }
    }
}