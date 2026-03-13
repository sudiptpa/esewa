<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Domain\Transaction\PaymentStatus;
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest;
use Sujip\Esewa\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TransactionServiceTest extends TestCase
{
    public function testStatusMapsResponseAndQuery(): void
    {
        $fake = new FakeTransport([
            'status' => 'COMPLETE',
            'ref_id' => 'REF-123',
        ]);

        $gateway = new EsewaClient(
            GatewayConfig::make(
                merchantCode: 'EPAYTEST',
                secretKey: 'secret',
                environment: 'uat',
            ),
            $fake
        );

        $result = $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-1001',
            totalAmount: '100.00',
            productCode: 'EPAYTEST',
        ));

        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
        $this->assertTrue($result->isSuccessful());
        $this->assertSame('TXN-1001', $fake->lastQuery['transaction_uuid']);
    }

    #[DataProvider('statusProvider')]
    public function testStatusMapsKnownStatuses(string $apiStatus, PaymentStatus $expectedStatus): void
    {
        $gateway = new EsewaClient(
            GatewayConfig::make(
                merchantCode: 'EPAYTEST',
                secretKey: 'secret',
                environment: 'uat',
            ),
            new FakeTransport([
                'status' => $apiStatus,
                'ref_id' => 'REF-XYZ',
            ])
        );

        $result = $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-1001',
            totalAmount: '100.00',
            productCode: 'EPAYTEST',
        ));

        $this->assertSame($expectedStatus, $result->status);
    }

    /**
     * @return array<int,array{0:string,1:PaymentStatus}>
     */
    public static function statusProvider(): array
    {
        return [
            ['PENDING', PaymentStatus::PENDING],
            ['COMPLETE', PaymentStatus::COMPLETE],
            ['FULL_REFUND', PaymentStatus::FULL_REFUND],
            ['PARTIAL_REFUND', PaymentStatus::PARTIAL_REFUND],
            ['AMBIGUOUS', PaymentStatus::AMBIGUOUS],
            ['NOT_FOUND', PaymentStatus::NOT_FOUND],
            ['CANCELED', PaymentStatus::CANCELED],
            ['NEW_FUTURE_STATUS', PaymentStatus::UNKNOWN],
        ];
    }
}
