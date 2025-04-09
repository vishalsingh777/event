<?php
namespace Vishal\Events\Ui\Component\Form\Event\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductRepository;
use Vishal\Events\Model\ResourceModel\EventTicket\CollectionFactory as TicketCollectionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;

class EventModifier implements ModifierInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    
    /**
     * @var ProductRepository
     */
    private $productRepository;
    
    /**
     * @var TicketCollectionFactory
     */
    private $ticketCollectionFactory;
    
    /**
     * @var RequestInterface
     */
    private $request;
    
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ProductRepository $productRepository
     * @param TicketCollectionFactory $ticketCollectionFactory
     * @param RequestInterface $request
     * @param Registry $registry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductRepository $productRepository,
        TicketCollectionFactory $ticketCollectionFactory,
        RequestInterface $request,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->ticketCollectionFactory = $ticketCollectionFactory;
        $this->request = $request;
        $this->registry = $registry;
    }

    /**
     * Modify meta
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        // Here we could modify the form meta data if needed
        return $meta;
    }

    /**
     * Modify data
     *
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $eventId = $this->request->getParam('event_id');
        
        if ($eventId) {
            // Load tickets data
            $ticketCollection = $this->ticketCollectionFactory->create();
            $ticketCollection->addFieldToFilter('event_id', $eventId);
            
            if ($ticketCollection->getSize()) {
                $tickets = [];
                foreach ($ticketCollection as $ticket) {
                    $ticketData = $ticket->getData();
                    
                    // Add product information if available
                    if ($ticket->getProductId()) {
                        try {
                            $product = $this->productRepository->getById($ticket->getProductId());
                            $ticketData['product_name'] = $product->getName();
                        } catch (\Exception $e) {
                            $ticketData['product_name'] = '';
                        }
                    }
                    
                    $tickets[] = $ticketData;
                }
                
                $data[$eventId]['tickets'] = $tickets;
            }
        }
        
        return $data;
    }
}