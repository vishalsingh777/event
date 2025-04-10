<?php
namespace Vishal\Events\Ui\Component\Form\Event;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var PoolInterface
     */
    private $pool;

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
     * @param PoolInterface $pool
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        PoolInterface $pool,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->pool = $pool;
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
        
        foreach ($items as $event) {
            $this->loadedData[$event->getId()] = $event->getData();
            
            // Format time slots for UI
            $timeSlots = $event->getTimeSlots();
            if (is_array($timeSlots)) {
                $this->loadedData[$event->getId()]['time_slots_grid'] = $timeSlots;
            } elseif (is_string($timeSlots) && !empty($timeSlots)) {
                try {
                    $timeSlotsArray = json_decode($timeSlots, true);
                    if (is_array($timeSlotsArray)) {
                        $this->loadedData[$event->getId()]['time_slots_grid'] = $timeSlotsArray;
                    }
                } catch (\Exception $e) {
                    // If not JSON, try comma separation
                    $timeSlotsArray = explode(',', $timeSlots);
                    $this->loadedData[$event->getId()]['time_slots_grid'] = $timeSlotsArray;
                }
            }
            
            // Format blocked dates for UI
            $blockedDates = $event->getBlockDates();
            if (is_array($blockedDates)) {
                // Keep as is for multiselect date picker
                $this->loadedData[$event->getId()]['block_dates'] = json_encode($blockedDates);
            } elseif (is_string($blockedDates) && !empty($blockedDates)) {
                // If it's already a JSON string, keep it
                if (strpos($blockedDates, '[') === 0) {
                    $this->loadedData[$event->getId()]['block_dates'] = $blockedDates;
                } else {
                    // Convert to JSON if not already
                    $blockedDatesArray = array_map('trim', explode("\n", $blockedDates));
                    $blockedDatesArray = array_filter($blockedDatesArray);
                    $this->loadedData[$event->getId()]['block_dates'] = json_encode($blockedDatesArray);
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
        
        foreach ($this->pool->getModifiersInstances() as $modifier) {
            $meta = $modifier->modifyMeta($meta);
        }

        return $meta;
    }
}