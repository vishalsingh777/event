<?php
namespace Insead\Events\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Insead\Events\Api\Data\EventInterface;
use Insead\Events\Api\Data\EventInterfaceFactory;
use Insead\Events\Api\EventRepositoryInterface;
use Insead\Events\Model\ResourceModel\Event as EventResource;
use Insead\Events\Model\ResourceModel\Event\CollectionFactory as EventCollectionFactory;
use Insead\Events\Api\Data\EventSearchResultsInterfaceFactory;
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
            // Handle store IDs if they are in array format
            if (is_array($event->getStoreId())) {
                $event->setStoreId(implode(',', $event->getStoreId()));
            }
            
            // Log the data structure for debugging
            $this->logData('Before save', $event->getData());
            
            $this->resource->save($event);
            
            // Handle time slots saving to separate table
            $this->saveTimeSlots($event);
            
            // Dispatch event for URL rewrite generation
            $this->eventManager->dispatch('insead_events_event_save_after', ['event' => $event]);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the event: %1', $exception->getMessage()),
                $exception
            );
        }
        return $event;
    }

   /**
     * Save time slots to separate table
     *
     * @param EventInterface $event
     * @return void
     */
    private function saveTimeSlots($event)
    {
        $eventId = $event->getId();
        
        if (!$eventId) {
            return;
        }
        
        $connection = $this->resource->getConnection();
        $timeSlotTable = $this->resource->getTable('insead_event_times');
        
        // Delete existing time slots for this event
        $connection->delete($timeSlotTable, ['event_id = ?' => $eventId]);
        
        // If this is a non-recurring event with single time, save that
        if ((int)$event->getRecurring() === 0) {
            if ($event->getSingleStartTime() && $event->getSingleEndTime()) {
                $data = [
                    'event_id' => $eventId,
                    'time_start' => $event->getSingleStartTime(),
                    'time_end' => $event->getSingleEndTime(),
                    'sort_order' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $connection->insert($timeSlotTable, $data);
            }
            return;
        }
        
        // Handle multiple time slots for recurring events
        $timeSlots = [];

        // Get the data from the date_time[date_time] structure
        $date_time = $event->getData('date_time');
        if (isset($date_time['date_time']) && is_array($date_time['date_time'])) {
            $dateTimeData = $date_time['date_time'];
            
            $sortOrder = 0;
            foreach ($dateTimeData as $rowId => $rowData) { 
                // Skip if this is not a numeric key (not a row entry)
                if (!is_numeric($rowId)) {
                    continue;
                }
                
                // Check if row is marked for deletion
                if (isset($rowData['is_deleted']) && $rowData['is_deleted'] == 1) {
                    continue;
                }
               
                // Get the actual field values from the nested 'date_time' key
                if (isset($rowData['date_time']) && isset($rowData['date_time']['time_start']) && isset($rowData['date_time']['time_end'])) {
                    $timeSlots[] = [
                        'event_id' => $eventId,
                        'record_id' => $rowData['record_id'],
                        'time_start' => $rowData['date_time']['time_start'],
                        'time_end' => $rowData['date_time']['time_end'],
                        'sort_order' => $sortOrder++,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                }
            } 
        }
        // If no structured time slots, try to use time_slots JSON field
        else if ($event->getTimeSlots()) {        
            $slots = $event->getTimeSlots();
            if (is_string($slots)) {
                try {
                    $slots = json_decode($slots, true);
                } catch (\Exception $e) {
                    $slots = [];
                }
            }
            
            if (is_array($slots)) {
                $sortOrder = 0;
                foreach ($slots as $slot) {
                    if (is_array($slot) && isset($slot['time_start']) && isset($slot['time_end'])) {
                        $timeSlots[] = [
                            'event_id' => $eventId,
                            'time_start' => $slot['time_start'],
                            'time_end' => $slot['time_end'],
                            'sort_order' => $sortOrder++,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        }
        if (!empty($timeSlots)) {
            // Insert all time slots in a batch
            foreach ($timeSlots as $timeSlot) {
                $connection->insert($timeSlotTable, $timeSlot);
            }
        }
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
        
        // Convert store_id from string to array if needed
        if (is_string($event->getStoreId()) && !empty($event->getStoreId())) {
            $event->setStoreId(explode(',', $event->getStoreId()));
        }
        
        // Load time slots from separate table
        $this->loadTimeSlots($event);
        
        return $event;
    }

    /**
 * Load time slots from separate table
 *
 * @param EventInterface $event
 * @return void
 */
private function loadTimeSlots($event)
{
    $eventId = $event->getId();
    
    if (!$eventId) {
        return;
    }
    
    $connection = $this->resource->getConnection();
    $timeSlotTable = $this->resource->getTable('insead_event_times');
    
    $select = $connection->select()
        ->from($timeSlotTable)
        ->where('event_id = ?', $eventId)
        ->order('sort_order ASC');
    
    $timeSlots = $connection->fetchAll($select);
    
    if (!empty($timeSlots)) {
        $formattedTimeSlots = [];
        
        foreach ($timeSlots as $timeSlot) {
            $formattedTimeSlots[] = [
                'time_start' => $timeSlot['time_start'],
                'time_end' => $timeSlot['time_end']
            ];
        }
        
        // Set the first time slot as the single time for non-recurring events
        if ((int)$event->getRecurring() === 0 && !empty($formattedTimeSlots)) {
            $firstSlot = reset($formattedTimeSlots);
            $event->setSingleStartTime($firstSlot['time_start']);
            $event->setSingleEndTime($firstSlot['time_end']);
        }
        
        // Set time slots as JSON string
        $event->setTimeSlots(json_encode($formattedTimeSlots));
        
        // Format data for the form structure with date_time key and record_id
        $dateTimeData = [];
        $index = 0;
        foreach ($formattedTimeSlots as $slot) {
            $dateTimeData[$index] = [
                'record_id' => $index,
                'date_time' => [
                    'time_start' => $slot['time_start'],
                    'time_end' => $slot['time_end']
                ]
            ];
            $index++;
        }
        
        // Set the data in the format expected by UI component
        $event->setData('date_time', ['date_time' => $dateTimeData]);
    }
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
        
        // Convert store_id from string to array if needed
        if (is_string($event->getStoreId()) && !empty($event->getStoreId())) {
           // $event->setStoreId(explode(',', $event->getStoreId()));
        }
        
        // Check if event is valid for the requested store
        //if ($storeId !== null && !$this->isEventAvailableForStore($event, $storeId)) {
          //  throw new NoSuchEntityException(__('The event with the "%1" URL key doesn\'t exist for store %2.', $urlKey, $storeId));
       // }
        
        // Load time slots from separate table
        $this->loadTimeSlots($event);
        
        return $event;
    }
    
    /**
     * Check if event is available for the given store
     *
     * @param EventInterface $event
     * @param int $storeId
     * @return bool
     */
    private function isEventAvailableForStore($event, $storeId)
    {
        $storeIds = $event->getStoreId();
        
        // If event is assigned to all stores or the specific store
        return in_array(0, $storeIds) || in_array($storeId, $storeIds);
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
            // Delete time slots first
            $connection = $this->resource->getConnection();
            $timeSlotTable = $this->resource->getTable('insead_event_times');
            $connection->delete($timeSlotTable, ['event_id = ?' => $event->getId()]);
            
            // Delete event
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
        
        $items = $collection->getItems();
        
        // Load time slots for each event and convert store IDs
        foreach ($items as $event) {
            // Convert store_id from string to array if needed
            if (is_string($event->getStoreId()) && !empty($event->getStoreId())) {
                $event->setStoreId(explode(',', $event->getStoreId()));
            }
            
            $this->loadTimeSlots($event);
        }
        
        $searchResults->setItems($items);
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
    
    /**
     * Log data for debugging
     *
     * @param string $title
     * @param mixed $data
     * @return void
     */
    private function logData($title, $data)
    {
        $logFile = BP . '/var/log/event_timeslots_debug.log';
        $message = "=== " . $title . " (" . date('Y-m-d H:i:s') . ") ===\n";
        $message .= print_r($data, true) . "\n\n";
        file_put_contents($logFile, $message, FILE_APPEND);
    }
}