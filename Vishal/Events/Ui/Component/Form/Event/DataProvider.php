<?php
/**
 * DataProvider.php
 * Path: app/code/Vishal/Events/Ui/Component/Form/Event/DataProvider.php
 */

declare(strict_types=1);

namespace Vishal\Events\Ui\Component\Form\Event;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Vishal\Events\Model\ResourceModel\Event\CollectionFactory;
use Vishal\Events\Model\EventTicketRepository;
use Vishal\Events\Model\ResourceModel\Event as EventResource;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PoolInterface
     */
    protected $pool;

    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EventResource
     */
    protected $eventResource;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Filesystem $filesystem
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param PoolInterface $pool
     * @param EventTicketRepository $eventTicketRepository
     * @param SerializerInterface $serializer
     * @param EventResource $eventResource
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        PoolInterface $pool,
        EventTicketRepository $eventTicketRepository,
        SerializerInterface $serializer,
        EventResource $eventResource,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->pool = $pool;
        $this->eventTicketRepository = $eventTicketRepository;
        $this->serializer = $serializer;
        $this->eventResource = $eventResource;
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
            $eventData = $event->getData();
            
            // Get store IDs directly from the resource model
            if ($event->getId()) {
                $storeIds = $this->eventResource->getStoreIds($event->getId());
                $eventData['store_id'] = $storeIds;
            }
            
            $this->loadedData[$event->getId()] = $eventData;
            
            // Load tickets data if event exists
            if ($event->getId()) {
                $tickets = $this->eventTicketRepository->getTicketsForEvent($event->getId());
                if ($tickets->getSize()) {
                    $ticketsData = [];
                    foreach ($tickets as $ticket) {
                        if (!$ticket->getProductId()) {
                            $ticketsData[] = $ticket->getData();
                        }
                    }
                    
                    // Change from array to comma-separated string for product_tickets
                    $productIds = [];
                    foreach ($tickets as $ticket) {
                        if ($ticket->getProductId()) {
                            $productIds[] = $ticket->getProductId();
                        }
                    }
                    
                    $this->loadedData[$event->getId()]['manual_tickets'] = $ticketsData;
                    
                    // Store as comma-separated string instead of array
                    if (!empty($productIds)) {
                        $this->loadedData[$event->getId()]['product_tickets'] = implode(',', $productIds);
                    } else {
                        $this->loadedData[$event->getId()]['product_tickets'] = '';
                    }
                }
            }
        }
        
        // Use data persistor for recently saved data
        $data = $this->dataPersistor->get('vishal_event');
        if (!empty($data)) {
            $event = $this->collection->getNewEmptyItem();
            $event->setData($data);
            $this->loadedData[$event->getId()] = $event->getData();
            $this->dataPersistor->clear('vishal_event');
        }
        
        // Apply modifiers if they exist
        if ($this->pool) {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $this->loadedData = $modifier->modifyData($this->loadedData);
            }
        }
        
        return $this->loadedData;
    }

    /**
     * Get meta
     *
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();
        
        // Apply modifiers if they exist
        if ($this->pool) {
            foreach ($this->pool->getModifiersInstances() as $modifier) {
                $meta = $modifier->modifyMeta($meta);
            }
        }
        
        return $meta;
    }
}