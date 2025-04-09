<?php
namespace Vishal\Events\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Vishal\Events\Api\Data\EventTicketInterface;
use Vishal\Events\Model\EventTicketFactory;
use Vishal\Events\Model\ResourceModel\EventTicket as EventTicketResource;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory as EventTicketCollectionFactory;

class EventTicketRepository
{
    /**
     * @var EventTicketResource
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
     * @param EventTicketResource $resource
     * @param EventTicketFactory $eventTicketFactory
     * @param EventTicketCollectionFactory $eventTicketCollectionFactory
     */
    public function __construct(
        EventTicketResource $resource,
        EventTicketFactory $eventTicketFactory,
        EventTicketCollectionFactory $eventTicketCollectionFactory
    ) {
        $this->resource = $resource;
        $this->eventTicketFactory = $eventTicketFactory;
        $this->eventTicketCollectionFactory = $eventTicketCollectionFactory;
    }

    /**
     * Save ticket
     *
     * @param EventTicketInterface $ticket
     * @return EventTicketInterface
     * @throws CouldNotSaveException
     */
    public function save(EventTicketInterface $ticket)
    {
        try {
            $this->resource->save($ticket);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $ticket;
    }

    /**
     * Get by id
     *
     * @param int $ticketId
     * @return EventTicketInterface
     * @throws NoSuchEntityException
     */
    public function getById($ticketId)
    {
        $ticket = $this->eventTicketFactory->create();
        $this->resource->load($ticket, $ticketId);
        if (!$ticket->getId()) {
            throw new NoSuchEntityException(__('The ticket with the "%1" ID doesn\'t exist.', $ticketId));
        }
        return $ticket;
    }

    /**
     * Delete ticket
     *
     * @param EventTicketInterface $ticket
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EventTicketInterface $ticket)
    {
        try {
            $this->resource->delete($ticket);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete by id
     *
     * @param int $ticketId
     * @return bool
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById($ticketId)
    {
        return $this->delete($this->getById($ticketId));
    }

    /**
     * Get tickets by event id
     *
     * @param int $eventId
     * @return \Vishal\Events\Model\ResourceModel\EventTicket\Collection
     */
    public function getTicketsByEventId($eventId)
    {
        $collection = $this->eventTicketCollectionFactory->create();
        $collection->addEventFilter($eventId);
        return $collection;
    }
}