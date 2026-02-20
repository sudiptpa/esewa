<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Service\SignatureService;
use PHPUnit\Framework\TestCase;

final class SignatureServiceTest extends TestCase
{
    public function testGenerateAndVerifySignature(): void
    {
        $service = new SignatureService('8gBm/:&EnhH.1/q');

        $signature = $service->generate('100.00', 'TXN-1001', 'EPAYTEST');

        $this->assertNotSame('', $signature);
        $this->assertTrue($service->verify($signature, '100.00', 'TXN-1001', 'EPAYTEST'));
        $this->assertFalse($service->verify($signature, '99.00', 'TXN-1001', 'EPAYTEST'));
    }
}
