<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Transaction\TransactionStatusRequest;
use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Exception\TransportException;
use EsewaPayment\Infrastructure\Idempotency\InMemoryIdempotencyStore;
use EsewaPayment\Service\SignatureService;
use EsewaPayment\Tests\Fakes\ArrayLogger;
use EsewaPayment\Tests\Fakes\FakeTransport;
use EsewaPayment\Tests\Fakes\FlakyTransport;
use PHPUnit\Framework\TestCase;

final class ProductionHardeningTest extends TestCase
{
    public function testCallbackReplayProtectionRejectsDuplicatePayload(): void
    {
        $logger = new ArrayLogger();
        $store = new InMemoryIdempotencyStore();
        $options = new ClientOptions(
            preventCallbackReplay: true,
            idempotencyStore: $store,
            logger: $logger
        );

        $gateway = new EsewaClient(
            GatewayConfig::make('EPAYTEST', 'secret', 'uat'),
            new FakeTransport([]),
            $options
        );

        $data = [
            'status' => 'COMPLETE',
            'transaction_uuid' => 'TXN-2001',
            'total_amount' => '100.00',
            'product_code' => 'EPAYTEST',
            'transaction_code' => 'REF-2001',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        $signature = (new SignatureService('secret'))->generate('100.00', 'TXN-2001', 'EPAYTEST');
        $payload = new CallbackPayload(base64_encode((string) json_encode($data)), $signature);

        $first = $gateway->callbacks()->verifyCallback($payload);
        $second = $gateway->callbacks()->verifyCallback($payload);

        $this->assertTrue($first->valid);
        $this->assertFalse($second->valid);
        $this->assertSame('Duplicate callback detected.', $second->message);
        $this->assertTrue($this->hasEvent($logger, 'esewa.callback.replay_detected'));
    }

    public function testStatusCheckRetriesOnTransportErrorsAndEventuallySucceeds(): void
    {
        $logger = new ArrayLogger();
        $transport = new FlakyTransport([
            new TransportException('temporary timeout'),
            new TransportException('temporary 502'),
            ['status' => 'COMPLETE', 'ref_id' => 'REF-OK'],
        ]);

        $gateway = new EsewaClient(
            GatewayConfig::make('EPAYTEST', 'secret', 'uat'),
            $transport,
            new ClientOptions(maxStatusRetries: 2, statusRetryDelayMs: 0, logger: $logger)
        );

        $result = $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-3001',
            totalAmount: '500.00',
            productCode: 'EPAYTEST'
        ));

        $this->assertSame(3, $transport->attempts);
        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
        $this->assertTrue($this->hasEvent($logger, 'esewa.status.retry'));
    }

    public function testStatusCheckThrowsWhenRetryLimitExceeded(): void
    {
        $transport = new FlakyTransport([
            new TransportException('temporary timeout'),
            new TransportException('still down'),
        ]);

        $gateway = new EsewaClient(
            GatewayConfig::make('EPAYTEST', 'secret', 'uat'),
            $transport,
            new ClientOptions(maxStatusRetries: 1, statusRetryDelayMs: 0)
        );

        $this->expectException(TransportException::class);

        $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-3002',
            totalAmount: '300.00',
            productCode: 'EPAYTEST'
        ));
    }

    private function hasEvent(ArrayLogger $logger, string $event): bool
    {
        foreach ($logger->records as $record) {
            if (($record['context']['event'] ?? null) === $event) {
                return true;
            }
        }

        return false;
    }
}
