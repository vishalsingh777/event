<?php
namespace Vishal\Events\Ui\Component\Form\Event;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
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
            $this->loadedData[$event->getId()] = $event->getData();
            
            // Format time slots if needed
            if (isset($this->loadedData[$event->getId()]['time_slots'])) {
                $timeSlots = $this->loadedData[$event->getId()]['time_slots'];
                if (is_string($timeSlots) && !empty($timeSlots)) {
                    try {
                        // Try to decode JSON
                        $decodedTimeSlots = json_decode($timeSlots, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->loadedData[$event->getId()]['time_slots'] = $decodedTimeSlots;
                        }
                    } catch (\Exception $e) {
                        // If not JSON, leave as is
                    }
                }
            }
            
            // Format available days if needed
            if (isset($this->loadedData[$event->getId()]['available_days'])) {
                $availableDays = $this->loadedData[$event->getId()]['available_days'];
                if (is_string($availableDays) && !empty($availableDays)) {
                    try {
                        // Try to decode JSON
                        $decodedDays = json_decode($availableDays, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->loadedData[$event->getId()]['available_days'] = $decodedDays;
                        }
                    } catch (\Exception $e) {
                        // If not JSON, leave as is
                    }
                }
            }
            
            // Format block dates if needed
            if (isset($this->loadedData[$event->getId()]['block_dates'])) {
                $blockDates = $this->loadedData[$event->getId()]['block_dates'];
                if (is_string($blockDates) && !empty($blockDates)) {
                    try {
                        // Try to decode JSON
                        $decodedDates = json_decode($blockDates, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->loadedData[$event->getId()]['block_dates'] = $decodedDates;
                        }
                    } catch (\Exception $e) {
                        // If not JSON, leave as is
                    }
                }
            }
            
            // Format customer groups if needed
            if (isset($this->loadedData[$event->getId()]['customer_group'])) {
                $customerGroups = $this->loadedData[$event->getId()]['customer_group'];
                if (is_string($customerGroups) && !empty($customerGroups)) {
                    try {
                        // Try to decode JSON
                        $decodedGroups = json_decode($customerGroups, true);
                        if (json_last_error() === JSON_ERROR_NONE) {
                            $this->loadedData[$event->getId()]['customer_group'] = $decodedGroups;
                        } else {
                            // If not JSON, try comma separated
                            $this->loadedData[$event->getId()]['customer_group'] = explode(',', $customerGroups);
                        }
                    } catch (\Exception $e) {
                        // If exception, try comma separated
                        $this->loadedData[$event->getId()]['customer_group'] = explode(',', $customerGroups);
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
}