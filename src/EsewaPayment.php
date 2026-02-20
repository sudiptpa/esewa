<?php

declare(strict_types=1);

namespace EsewaPayment;

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\TransportInterface;

final class EsewaPayment
{
    public static function client(GatewayConfig $config, TransportInterface $transport): EsewaClient
    {
        return new EsewaClient($config, $transport);
    }
}
