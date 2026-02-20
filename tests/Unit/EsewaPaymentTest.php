<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\EsewaPayment;
use EsewaPayment\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class EsewaPaymentTest extends TestCase
{
    public function testMakeCreatesClientWithMinimalSetup(): void
    {
        $client = EsewaPayment::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            transport: new FakeTransport([]),
            environment: 'uat'
        );

        $this->assertInstanceOf(EsewaClient::class, $client);
        $this->assertNotNull($client->checkout());
        $this->assertNotNull($client->callbacks());
        $this->assertNotNull($client->transactions());
    }
}
