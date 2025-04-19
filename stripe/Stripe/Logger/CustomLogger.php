<?php

namespace Insead\Stripe\Logger;

use Monolog\Logger;

class CustomLogger extends Logger
{
    public function __construct(Handler $handler)
    {
        parent::__construct('insead_stripe', [$handler]);
    }
}
