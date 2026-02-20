<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Config\GatewayConfig;
use PHPUnit\Framework\TestCase;

final class EndpointResolverTest extends TestCase
{
    public function testUsesDefaultUatEndpoints(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $resolver = new EndpointResolver();

        $this->assertSame(
            'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
            $resolver->checkoutFormUrl($config)
        );
        $this->assertSame(
            'https://rc-epay.esewa.com.np/api/epay/transaction/status/',
            $resolver->statusCheckUrl($config)
        );
    }

    public function testUsesDefaultProductionEndpoints(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'production',
        );

        $resolver = new EndpointResolver();

        $this->assertSame(
            'https://epay.esewa.com.np/api/epay/main/v2/form',
            $resolver->checkoutFormUrl($config)
        );
        $this->assertSame(
            'https://epay.esewa.com.np/api/epay/transaction/status/',
            $resolver->statusCheckUrl($config)
        );
    }

    public function testUsesOverridesWhenProvided(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
            checkoutFormUrl: 'https://custom.test/form',
            statusCheckUrl: 'https://custom.test/status',
        );

        $resolver = new EndpointResolver();

        $this->assertSame('https://custom.test/form', $resolver->checkoutFormUrl($config));
        $this->assertSame('https://custom.test/status', $resolver->statusCheckUrl($config));
    }
}
