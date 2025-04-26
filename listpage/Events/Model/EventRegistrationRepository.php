<?php
namespace Insead\Events\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Insead\Events\Api\Data\EventRegistrationInterface;
use Insead\Events\Api\EventRegistrationRepositoryInterface;
use Insead\Events\Model\ResourceModel\EventRegistration as ResourceEventRegistration;
use Insead\Events\Model\ResourceModel\EventRegistration\CollectionFactory as EventRegistrationCollectionFactory;

class EventRegistrationRepository implements EventRegistrationRepositoryInterface
{
    /**
     * @var ResourceEventRegistration
     */
    protected $resource;

    /**
     * @var EventRegistrationFactory
     */
    protected $registrationFactory;

    /**
     * @var EventRegistrationCollectionFactory
     */
    protected $registrationCollectionFactory;

    /**
     * @param ResourceEventRegistration $resource
     * @param EventRegistrationFactory $registrationFactory
     * @param EventRegistrationCollectionFactory $registrationCollectionFactory
     */
    public function __construct(
        ResourceEventRegistration $resource,
        EventRegistrationFactory $registrationFactory,
        EventRegistrationCollectionFactory $registrationCollectionFactory
    ) {
        $this->resource = $resource;
        $this->registrationFactory = $registrationFactory;
        $this->registrationCollectionFactory = $registrationCollectionFactory;
    }

    /**
     * Save registration
     *
     * @param EventRegistrationInterface $registration
     * @return EventRegistrationInterface
     * @throws CouldNotSaveException
     */
    public function save(EventRegistrationInterface $registration)
    {
        try {
            $this->resource->save($registration);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $registration;
    }

    /**
     * Load registration by id
     *
     * @param int $registrationId
     * @return EventRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getById($registrationId)
    {
        $registration = $this->registrationFactory->create();
        $this->resource->load($registration, $registrationId);
        if (!$registration->getId()) {
            throw new NoSuchEntityException(__('The registration with the "%1" ID doesn\'t exist.', $registrationId));
        }
        return $registration;
    }

    /**
     * Delete registration
     *
     * @param EventRegistrationInterface $registration
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(EventRegistrationInterface $registration)
    {
        try {
            $this->resource->delete($registration);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete registration by id
     *
     * @param int $registrationId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($registrationId)
    {
        return $this->delete($this->getById($registrationId));
    }
    
    /**
     * Get registrations by event id
     *
     * @param int $eventId
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByEventId($eventId)
    {
        $collection = $this->registrationCollectionFactory->create();
        $collection->addEventFilter($eventId);
        return $collection;
    }
    
    /**
     * Get registrations by status
     *
     * @param string|array $status
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByStatus($status)
    {
        $collection = $this->registrationCollectionFactory->create();
        $collection->addStatusFilter($status);
        return $collection;
    }
    
    /**
     * Get registrations by email
     *
     * @param string $email
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByEmail($email)
    {
        $collection = $this->registrationCollectionFactory->create();
        $collection->addEmailFilter($email);
        return $collection;
    }
}