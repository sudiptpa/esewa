<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Esewa;
use Sujip\Esewa\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class EsewaTest extends TestCase
{
    public function testMakeCreatesClientWithMinimalSetup(): void
    {
        $client = Esewa::make(
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
