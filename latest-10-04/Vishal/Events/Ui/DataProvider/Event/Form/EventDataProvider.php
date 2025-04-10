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
        
        foreach ($items as $event) {
            $this->loadedData[$event->getId()] = $event->getData();
            
            // Load stores
            if ($event->getStoreId()) {
                $this->loadedData[$event->getId()]['store_id'] = $event->getStoreId();
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