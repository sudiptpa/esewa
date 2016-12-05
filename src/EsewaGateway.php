<?php

namespace Omnipay\NABTransact;

use Omnipay\Common\AbstractGateway;

/**
 * EsewaGateway Gateway.
 */
class EsewaGateway extends AbstractGateway
{
    public function getName()
    {
        return 'Esewa Payment';
    }
}
