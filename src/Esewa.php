<?php

declare(strict_types=1);

namespace Sujip\Esewa;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Config\Environment;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Contracts\TransportInterface;
use Sujip\Esewa\Infrastructure\Transport\CurlTransport;

final class Esewa
{
    public static function client(
        GatewayConfig $config,
        ?TransportInterface $transport = null,
        ?ClientOptions $options = null
    ): EsewaClient {
        return new EsewaClient($config, $transport ?? new CurlTransport(), $options);
    }

    public static function make(
        string $merchantCode,
        string $secretKey,
        ?TransportInterface $transport = null,
        Environment|string $environment = Environment::UAT,
        ?ClientOptions $options = null
    ): EsewaClient {
        return self::client(
            GatewayConfig::make(
                merchantCode: $merchantCode,
                secretKey: $secretKey,
                environment: $environment,
            ),
            $transport,
            $options
        );
    }
}
