<?php

declare(strict_types=1);

namespace EsewaPayment;

use EsewaPayment\Client\EsewaGateway;
use EsewaPayment\Config\Config;
use EsewaPayment\Contracts\TransportInterface;

final class EsewaPayment
{
    public static function gateway(Config $config, TransportInterface $transport): EsewaGateway
    {
        return new EsewaGateway($config, $transport);
    }
}
