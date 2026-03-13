<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Domain\Transaction\PaymentStatus;
use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\VerificationExpectation;
use Sujip\Esewa\Domain\Verification\VerificationState;
use Sujip\Esewa\Exception\FraudValidationException;
use Sujip\Esewa\Service\SignatureService;
use Sujip\Esewa\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class CallbackServiceTest extends TestCase
{
    public function testVerifyValidCallbackPayload(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $gateway = new EsewaClient($config, new FakeTransport([]));

        $data = [
            'status'             => 'COMPLETE',
            'transaction_uuid'   => 'TXN-1001',
            'total_amount'       => '100.00',
            'product_code'       => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'transaction_code'   => 'R-1001',
        ];

        $signature = (new SignatureService('secret'))->generate(
            '100.00',
            'TXN-1001',
            'EPAYTEST',
            'total_amount,transaction_uuid,product_code'
        );

        $payload = new CallbackPayload(base64_encode((string) json_encode($data)), $signature);

        $result = $gateway->callbacks()->verifyCallback($payload, new VerificationExpectation(
            totalAmount: '100.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST',
            referenceId: 'R-1001'
        ));

        $this->assertTrue($result->valid);
        $this->assertSame(VerificationState::VERIFIED, $result->state);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyReturnsInvalidResultWhenSignatureIsWrong(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $gateway = new EsewaClient($config, new FakeTransport([]));

        $data = [
            'status'           => 'COMPLETE',
            'transaction_uuid' => 'TXN-1001',
            'total_amount'     => '100.00',
            'product_code'     => 'EPAYTEST',
        ];

        $payload = new CallbackPayload(base64_encode((string) json_encode($data)), 'wrong-signature');
        $result = $gateway->callbacks()->verifyCallback($payload);

        $this->assertFalse($result->valid);
        $this->assertSame(VerificationState::INVALID_SIGNATURE, $result->state);
        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
        $this->assertSame('Invalid callback signature.', $result->message);
        $this->assertFalse($result->isSuccessful());
    }

    public function testVerifyThrowsOnFraudMismatch(): void
    {
        $config = GatewayConfig::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $gateway = new EsewaClient($config, new FakeTransport([]));

        $data = [
            'status'             => 'COMPLETE',
            'transaction_uuid'   => 'TXN-1001',
            'total_amount'       => '100.00',
            'product_code'       => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        $signature = (new SignatureService('secret'))->generate('100.00', 'TXN-1001', 'EPAYTEST');
        $payload = new CallbackPayload(base64_encode((string) json_encode($data)), $signature);

        $this->expectException(FraudValidationException::class);

        $gateway->callbacks()->verifyCallback($payload, new VerificationExpectation(
            totalAmount: '99.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST'
        ));
    }
}
