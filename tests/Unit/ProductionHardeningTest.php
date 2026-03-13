<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Domain\Transaction\PaymentStatus;
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest;
use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\VerificationState;
use Sujip\Esewa\Exception\TransportException;
use Sujip\Esewa\Infrastructure\Idempotency\InMemoryIdempotencyStore;
use Sujip\Esewa\Contracts\RetryPolicyInterface;
use Sujip\Esewa\Service\SignatureService;
use Sujip\Esewa\Tests\Fakes\FakeTransport;
use Sujip\Esewa\Tests\Fakes\FlakyTransport;
use PHPUnit\Framework\TestCase;

final class ProductionHardeningTest extends TestCase
{
    public function testCallbackReplayProtectionRejectsDuplicatePayload(): void
    {
        $store = new InMemoryIdempotencyStore();
        $options = new ClientOptions(
            preventCallbackReplay: true,
            idempotencyStore: $store,
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
        $this->assertSame(VerificationState::VERIFIED, $first->state);
        $this->assertFalse($second->valid);
        $this->assertSame(VerificationState::REPLAYED, $second->state);
        $this->assertTrue($second->isReplayed());
        $this->assertSame('Duplicate callback detected.', $second->message);
    }

    public function testStatusCheckRetriesOnTransportErrorsAndEventuallySucceeds(): void
    {
        $transport = new FlakyTransport([
            new TransportException('temporary timeout'),
            new TransportException('temporary 502'),
            ['status' => 'COMPLETE', 'ref_id' => 'REF-OK'],
        ]);

        $gateway = new EsewaClient(
            GatewayConfig::make('EPAYTEST', 'secret', 'uat'),
            $transport,
            new ClientOptions(maxStatusRetries: 2, statusRetryDelayMs: 0)
        );

        $result = $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-3001',
            totalAmount: '500.00',
            productCode: 'EPAYTEST'
        ));

        $this->assertSame(3, $transport->attempts);
        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
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

    public function testStatusCheckUsesCustomRetryPolicy(): void
    {
        $transport = new FlakyTransport([
            new TransportException('temporary timeout'),
            ['status' => 'COMPLETE', 'ref_id' => 'REF-OK'],
        ]);

        $policy = new class implements RetryPolicyInterface {
            public int $shouldRetryCalls = 0;
            public int $delayCalls = 0;

            public function shouldRetry(int $attempt, TransportException $exception): bool
            {
                ++$this->shouldRetryCalls;

                return $attempt < 1;
            }

            public function delayUs(int $attempt, TransportException $exception): int
            {
                ++$this->delayCalls;

                return 0;
            }
        };

        $gateway = new EsewaClient(
            GatewayConfig::make('EPAYTEST', 'secret', 'uat'),
            $transport,
            new ClientOptions(
                maxStatusRetries: 0,
                statusRetryDelayMs: 0,
                retryPolicy: $policy,
            )
        );

        $result = $gateway->transactions()->fetchStatus(new TransactionStatusRequest(
            transactionUuid: 'TXN-3003',
            totalAmount: '500.00',
            productCode: 'EPAYTEST'
        ));

        $this->assertSame(1, $policy->shouldRetryCalls);
        $this->assertSame(1, $policy->delayCalls);
        $this->assertSame(2, $transport->attempts);
        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
    }
}
