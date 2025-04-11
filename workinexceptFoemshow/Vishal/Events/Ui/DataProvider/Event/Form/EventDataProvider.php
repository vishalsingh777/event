<?php
namespace Vishal\Events\Ui\DataProvider\Event\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class EventDataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var EventResource
     */
    private $eventResource;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $eventCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
     * @param EventResource $eventResource
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $eventCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        EventResource $eventResource,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $eventCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->eventResource = $eventResource;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        
        $items = $this->collection->getItems();
        $this->loadedData = [];
        
        foreach ($items as $event) {
            $eventData = $event->getData();
            $this->loadedData[$event->getId()] = $eventData;
            
            // Convert store_id from string to array if needed
            if (isset($eventData['store_id']) && is_string($eventData['store_id']) && !empty($eventData['store_id'])) {
                $this->loadedData[$event->getId()]['store_id'] = explode(',', $eventData['store_id']);
            }
            
            // Format customer groups
            if (isset($eventData['customer_group']) && is_string($eventData['customer_group']) && !empty($eventData['customer_group'])) {
                try {
                    $customerGroup = json_decode($eventData['customer_group'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($customerGroup)) {
                        $this->loadedData[$event->getId()]['customer_group'] = $customerGroup;
                    } else {
                        $this->loadedData[$event->getId()]['customer_group'] = explode(',', $eventData['customer_group']);
                    }
                } catch (\Exception $e) {
                    $this->loadedData[$event->getId()]['customer_group'] = explode(',', $eventData['customer_group']);
                }
            }
            
            // Handle available days
            if (isset($eventData['available_days']) && is_string($eventData['available_days']) && !empty($eventData['available_days'])) {
                try {
                    $availableDays = json_decode($eventData['available_days'], true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($availableDays)) {
                        $this->loadedData[$event->getId()]['available_days'] = $availableDays;
                    }
                } catch (\Exception $e) {
                    // If JSON parsing fails, leave as is
                }
            }
            
            // Process time slots for dynamic rows
            $this->processTimeSlots($event);
        }

        $data = $this->dataPersistor->get('event_form_data');
        if (!empty($data)) {
            $event = $this->collection->getNewEmptyItem();
            $event->setData($data);
            $this->loadedData[$event->getId()] = $event->getData();
            $this->dataPersistor->clear('event_form_data');
        }

        return $this->loadedData;
    }
    
    /**
     * Process time slots for UI component
     *
     * @param \Vishal\Events\Model\Event $event
     * @return void
     */
    private function processTimeSlots($event)
    {
        $eventId = $event->getId();
        
        if (!$eventId) {
            return;
        }
        
        // Get time slots from the database
        $connection = $this->eventResource->getConnection();
        $timeSlotTable = $this->eventResource->getTable('vishal_event_times');
        
        $select = $connection->select()
            ->from($timeSlotTable)
            ->where('event_id = ?', $eventId)
            ->order('sort_order ASC');
        
        $timeSlots = $connection->fetchAll($select);
        
        if (!empty($timeSlots)) {
            // For non-recurring events, set single time values
            if ((int)$event->getRecurring() === 0 && !empty($timeSlots)) {
                $firstSlot = reset($timeSlots);
                $this->loadedData[$eventId]['single_start_time'] = $firstSlot['time_start'];
                $this->loadedData[$eventId]['single_end_time'] = $firstSlot['time_end'];
            } else {
                // For recurring events, format for dynamic rows with the expected structure
                $dateTimeData = [];
                foreach ($timeSlots as $index => $timeSlot) {
                    $dateTimeData[$index] = [
                        'record_id' => $index,
                        'false' => [
                            'time_start' => $timeSlot['time_start'],
                            'time_end' => $timeSlot['time_end']
                        ]
                    ];
                }
                
                // Set the formatted time slots
                if (!empty($dateTimeData)) {
                    $this->loadedData[$eventId]['false'] = ['date_time' => $dateTimeData];
                    $this->debugTimeSlots($eventId, $timeSlots);
                }
            }
        }
    }

    /**
     * Get meta information
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        
        // Add store options
        $meta['website_fieldset']['children']['store_id']['arguments']['data']['config']['options'] = $this->getStoreOptions();
        
        return $meta;
    }

    /**
     * Get store options
     *
     * @return array
     */
    private function getStoreOptions()
    {
        $storeManager = $this->storeManager;
        $stores = [];
        $stores[] = ['label' => __('All Store Views'), 'value' => 0];
        
        foreach ($storeManager->getStores() as $store) {
            $stores[] = [
                'label' => $store->getName(),
                'value' => $store->getId(),
            ];
        }
        
        return $stores;
    }

    private function debugTimeSlots($eventId, $timeSlots)
{
    $logFile = BP . '/var/log/event_timeslots_loading.log';
    $message = "Loading time slots for Event ID: " . $eventId . "\n";
    $message .= "Time slots from DB: " . print_r($timeSlots, true) . "\n";
    $message .= "Formatted data: " . print_r($this->loadedData[$eventId]['false'], true) . "\n";
    $message .= "------------------------------------------------\n";
    file_put_contents($logFile, $message, FILE_APPEND);
}
}