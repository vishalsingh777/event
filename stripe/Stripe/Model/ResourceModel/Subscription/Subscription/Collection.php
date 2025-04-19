<?php

namespace Insead\Stripe\Model\ResourceModel\Subscription\Subscription;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Insead\Stripe\Model\Subscription\Subscription as Model;
use Insead\Stripe\Model\ResourceModel\Subscription\Subscription as ResourceModel;

class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
