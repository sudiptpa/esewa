<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Config\Environment;
use EsewaPayment\Config\GatewayConfig;
use PHPUnit\Framework\TestCase;

final class GatewayConfigTest extends TestCase
{
    public function testMakeMapsValuesAndAliases(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'live',
            checkoutFormUrl: 'https://checkout.test/form',
            statusCheckUrl: 'https://checkout.test/status',
        );

        $this->assertSame('EPAYTEST', $config->merchantCode);
        $this->assertSame('secret', $config->secretKey);
        $this->assertSame(Environment::PRODUCTION, $config->environment);
        $this->assertSame('https://checkout.test/form', $config->checkoutFormUrl);
        $this->assertSame('https://checkout.test/status', $config->statusCheckUrl);
    }

    public function testThrowsOnEmptyMerchantCode(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('merchantCode is required.');

        new GatewayConfig('', 'secret');
    }

    public function testThrowsOnEmptySecretKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('secretKey is required.');

        new GatewayConfig('EPAYTEST', '');
    }

    public function testThrowsOnUnsupportedEnvironment(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported environment: qa');

        GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'qa',
        );
    }
}
