<?php
/**
 * Products.php
 * Path: app/code/Vishal/Events/Model/Config/Source/Products.php
 */

declare(strict_types=1);

namespace Vishal\Events\Model\Config\Source;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class Products implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect(['name', 'sku'])
            ->addAttributeToFilter('status', 1) // Only enabled products
            ->setOrder('name', 'ASC');
        
        $options = [];
        foreach ($collection as $product) {
            $options[] = [
                'value' => $product->getId(),
                'label' => $product->getName() . ' (' . $product->getSku() . ')'
            ];
        }
        
        return $options;
    }
}