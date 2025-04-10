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