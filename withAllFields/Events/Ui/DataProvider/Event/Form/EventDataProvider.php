<?php
namespace Vishal\Events\Ui\DataProvider\Event\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

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
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $eventCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManager
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
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $eventCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
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
            
            // Process time slots for dynamic rows
            if (isset($eventData['time_slots']) && !empty($eventData['time_slots'])) {
                $timeSlots = $eventData['time_slots'];
                
                if (is_string($timeSlots)) {
                    try {
                        $decodedTimeSlots = json_decode($timeSlots, true);
                        
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedTimeSlots)) {
                            $timeSlotRows = [];
                            
                            foreach ($decodedTimeSlots as $slot) {
                                if (is_array($slot) && isset($slot['time_start']) && isset($slot['time_end'])) {
                                    $timeSlotRows[] = [
                                        'time_start' => $slot['time_start'],
                                        'time_end' => $slot['time_end']
                                    ];
                                } elseif (is_string($slot) && strpos($slot, '-') !== false) {
                                    // Handle legacy format with time ranges like "09:00-17:00"
                                    $times = explode('-', $slot);
                                    $timeSlotRows[] = [
                                        'time_start' => trim($times[0]),
                                        'time_end' => trim($times[1])
                                    ];
                                }
                            }
                            
                            if (!empty($timeSlotRows)) {
                                $this->loadedData[$event->getId()]['date_time'] = [
                                    'data' => $timeSlotRows
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        // If JSON parsing fails, leave as is
                    }
                }
            }
            
            // Set single start and end times for non-recurring events
            if (isset($eventData['recurring']) && (int)$eventData['recurring'] === 0) {
                // Try to get from existing fields
                if (isset($eventData['single_start_time']) && isset($eventData['single_end_time'])) {
                    $this->loadedData[$event->getId()]['single_start_time'] = $eventData['single_start_time'];
                    $this->loadedData[$event->getId()]['single_end_time'] = $eventData['single_end_time'];
                } 
                // If not available, try to get from first time slot
                elseif (isset($eventData['time_slots']) && !empty($eventData['time_slots'])) {
                    $timeSlots = $event->getTimeSlots();
                    if (!empty($timeSlots)) {
                        $firstSlot = reset($timeSlots);
                        if (is_array($firstSlot) && isset($firstSlot['time_start']) && isset($firstSlot['time_end'])) {
                            $this->loadedData[$event->getId()]['single_start_time'] = $firstSlot['time_start'];
                            $this->loadedData[$event->getId()]['single_end_time'] = $firstSlot['time_end'];
                        }
                    }
                }
            }
            
            // Load stores
            if ($event->getStoreId()) {
                $this->loadedData[$event->getId()]['store_id'] = $event->getStoreId();
            }
            
            // Format available days
            if (isset($eventData['available_days'])) {
                $availableDays = $eventData['available_days'];
                if (is_string($availableDays) && !empty($availableDays)) {
                    try {
                        $decodedDays = json_decode($availableDays, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDays)) {
                            $this->loadedData[$event->getId()]['available_days'] = $decodedDays;
                        }
                    } catch (\Exception $e) {
                        // If JSON parsing fails, leave as is
                    }
                }
            }
            
            // Format block dates
            if (isset($eventData['block_dates'])) {
                $blockDates = $eventData['block_dates'];
                if (is_string($blockDates) && !empty($blockDates)) {
                    try {
                        $decodedDates = json_decode($blockDates, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedDates)) {
                            $this->loadedData[$event->getId()]['block_dates'] = $decodedDates;
                        }
                    } catch (\Exception $e) {
                        // If JSON parsing fails, leave as is
                    }
                }
            }
            
            // Format customer groups
            if (isset($eventData['customer_group'])) {
                $customerGroup = $eventData['customer_group'];
                if (is_string($customerGroup) && !empty($customerGroup)) {
                    try {
                        $decodedGroups = json_decode($customerGroup, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedGroups)) {
                            $this->loadedData[$event->getId()]['customer_group'] = $decodedGroups;
                        } else {
                            // If not a valid JSON array, try comma separated
                            $this->loadedData[$event->getId()]['customer_group'] = explode(',', $customerGroup);
                        }
                    } catch (\Exception $e) {
                        // If exception, try comma separated
                        $this->loadedData[$event->getId()]['customer_group'] = explode(',', $customerGroup);
                    }
                }
            }
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
}