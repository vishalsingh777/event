<?php
namespace Vishal\Events\Model\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class EventProducts implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => '', 'label' => __('-- Please Select --')]];
        
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku'])
            ->addAttributeToFilter('is_event', 1) // Filter where is_event = yes
            ->setOrder('name', 'ASC');
        
        foreach ($collection as $product) {
            $options[] = [
                'value' => $product->getSku(),
                'label' => $product->getName()
            ];
        }
        
        return $options;
    }
}