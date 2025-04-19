<?php

namespace Insead\Stripe\Logger;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base as MagentoBaseHandler;

class Handler extends MagentoBaseHandler
{
    protected $fileName = '/var/log/stripe_subcription.log';
    protected $loggerType = Logger::INFO;
}
