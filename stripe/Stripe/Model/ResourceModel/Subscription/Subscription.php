<?php

namespace Insead\Stripe\Model\ResourceModel\Subscription;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Subscription extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('stripe_subscription_status', 'id');
    }
}
