<?php

namespace Insead\Stripe\Model\Subscription;

use Magento\Framework\Model\AbstractModel;
use Insead\Stripe\Model\ResourceModel\Subscription\Subscription as ResourceModel;

class Subscription extends AbstractModel
{
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }
}
