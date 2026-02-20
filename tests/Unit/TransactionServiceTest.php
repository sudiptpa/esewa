<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaGateway;
use EsewaPayment\Config\Config;
use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Transaction\StatusQuery;
use EsewaPayment\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class TransactionServiceTest extends TestCase
{
    public function testStatusMapsResponseAndQuery(): void
    {
        $fake = new FakeTransport([
            'status' => 'COMPLETE',
            'ref_id' => 'REF-123',
        ]);

        $gateway = new EsewaGateway(
            Config::fromArray([
                'merchant_code' => 'EPAYTEST',
                'secret_key' => 'secret',
                'environment' => 'uat',
            ]),
            $fake
        );

        $result = $gateway->transactions()->status(new StatusQuery(
            transactionUuid: 'TXN-1001',
            totalAmount: '100.00',
            productCode: 'EPAYTEST',
        ));

        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('TXN-1001', $fake->lastQuery['transaction_uuid']);
    }
}
