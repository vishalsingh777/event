<?php
/**
 * ProductDataProvider.php
 * Path: app/code/Vishal/Events/Ui/DataProvider/Event/ProductDataProvider.php
 */
namespace Vishal\Events\Ui\DataProvider\Event;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Vishal\Events\Model\EventTicketRepository;
use Magento\Framework\Registry;

class ProductDataProvider extends AbstractDataProvider
{
    /**
     * @var RequestInterface
     */
    protected $request;
    
    /**
     * @var EventTicketRepository
     */
    protected $eventTicketRepository;
    
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param EventTicketRepository $eventTicketRepository
     * @param Registry $registry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        EventTicketRepository $eventTicketRepository,
        Registry $registry,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->request = $request;
        $this->eventTicketRepository = $eventTicketRepository;
        $this->coreRegistry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            // Add base attributes to select
            $this->getCollection()->addAttributeToSelect(['name', 'sku', 'price', 'status']);
            
            // Get event ID from request or registry
            $eventId = $this->request->getParam('event_id') ?: 
                ($this->request->getParam('id') ?: 
                    ($this->coreRegistry->registry('current_event') ? 
                        $this->coreRegistry->registry('current_event')->getId() : null));
            
            if ($eventId) {
                // For edit mode - show only assigned products
                $tickets = $this->eventTicketRepository->getTicketsForEvent($eventId);
                $productIds = [];
                $positions = [];
                
                foreach ($tickets as $ticket) {
                    if ($ticket->getProductId()) {
                        $productIds[] = $ticket->getProductId();
                        $positions[$ticket->getProductId()] = $ticket->getPosition();
                    }
                }
                
                if (!empty($productIds)) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
                    
                    // Add position data to each product
                    foreach ($this->getCollection() as $product) {
                        $product->setPosition($positions[$product->getId()] ?? 0);
                    }
                }
            } else {
                // For new event - show all products
                // Only show enabled products
                $this->getCollection()->addAttributeToFilter('status', 1);
                // Set default position to 0
                foreach ($this->getCollection() as $product) {
                    $product->setPosition(0);
                }
            }
            
            $this->getCollection()->load();
        }
        
        $items = [];
        foreach ($this->getCollection() as $product) {
            $productData = $product->getData();
            // Ensure position is set
            if (!isset($productData['position'])) {
                $productData['position'] = 0;
            }
            $items[] = $productData;
        }
        
        return [
            'totalRecords' => $this->getCollection()->getSize(),
            'items' => $items,
        ];
    }
}