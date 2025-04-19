<?php
namespace Insead\Stripe\Ui\DataProvider;

use Insead\Stripe\Model\ResourceModel\Subscription\Subscription\Collection;
use Insead\Stripe\Model\ResourceModel\Subscription\Subscription\CollectionFactory;

/**
 * Class SubscriptionDataProvider
 */
class Subscription extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Queue collection
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Queue constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get data
     *
     * @return []
     */
    public function getData()
    {
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }

        return $this->getCollection()->toArray();
    }
}
