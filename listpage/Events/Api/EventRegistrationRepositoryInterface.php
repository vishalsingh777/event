<?php
namespace Insead\Events\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Insead\Events\Api\Data\EventRegistrationInterface;

interface EventRegistrationRepositoryInterface
{
    /**
     * Save registration
     *
     * @param \Insead\Events\Api\Data\EventRegistrationInterface $registration
     * @return \Insead\Events\Api\Data\EventRegistrationInterface
     * @throws LocalizedException
     */
    public function save(EventRegistrationInterface $registration);

    /**
     * Get registration by id
     *
     * @param int $registrationId
     * @return \Insead\Events\Api\Data\EventRegistrationInterface
     * @throws NoSuchEntityException
     */
    public function getById($registrationId);

    /**
     * Delete registration
     *
     * @param \Insead\Events\Api\Data\EventRegistrationInterface $registration
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(EventRegistrationInterface $registration);

    /**
     * Delete registration by ID
     *
     * @param int $registrationId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($registrationId);
    
    /**
     * Get registrations by event id
     *
     * @param int $eventId
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByEventId($eventId);
    
    /**
     * Get registrations by status
     *
     * @param string|array $status
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByStatus($status);
    
    /**
     * Get registrations by email
     *
     * @param string $email
     * @return \Insead\Events\Model\ResourceModel\EventRegistration\Collection
     */
    public function getRegistrationsByEmail($email);
}