<?php
namespace Insead\Events\Ui\DataProvider\Registration;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Insead\Events\Model\ResourceModel\EventRegistration\CollectionFactory;
use Magento\Framework\App\RequestInterface;

class ListingDataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collectionFactory = $collectionFactory;
        $this->collection = $this->collectionFactory->create();
        $this->request = $request;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        
        $items = $this->getCollection()->toArray();
        
        // Process order_id to create link to order view
        foreach ($items['items'] as &$item) {
            if (!empty($item['order_id'])) {
                $item['order_id'] = sprintf(
                    '<a href="%s" target="_blank">%s</a>',
                    $this->getOrderViewUrl($item['order_id']),
                    $item['order_id']
                );
            } else {
                $item['order_id'] = __('No Order');
            }
        }
        
        return $items;
    }
    
    /**
     * Get URL to view order
     *
     * @param int $orderId
     * @return string
     */
    protected function getOrderViewUrl($orderId)
    {
        return '#'; // In a real scenario, this would be a URL to the order view page
    }
}