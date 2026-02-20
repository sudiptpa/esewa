<?php

declare(strict_types=1);

namespace EsewaPayment;

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\TransportInterface;

final class EsewaPayment
{
    public static function client(
        GatewayConfig $config,
        TransportInterface $transport,
        ?ClientOptions $options = null
    ): EsewaClient
    {
        return new EsewaClient($config, $transport, $options);
    }
}
