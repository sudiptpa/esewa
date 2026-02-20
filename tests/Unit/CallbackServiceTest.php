<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Domain\Verification\VerificationExpectation;
use EsewaPayment\Exception\FraudValidationException;
use EsewaPayment\Service\SignatureService;
use EsewaPayment\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class CallbackServiceTest extends TestCase
{
    public function testVerifyValidCallbackPayload(): void
    {
        $config = GatewayConfig::fromArray([
            'merchant_code' => 'EPAYTEST',
            'secret_key'    => 'secret',
            'environment'   => 'uat',
        ]);

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

        $result = $gateway->callback()->verify($payload, new VerificationExpectation(
            totalAmount: '100.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST',
            referenceId: 'R-1001'
        ));

        $this->assertTrue($result->valid);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyReturnsInvalidResultWhenSignatureIsWrong(): void
    {
        $config = GatewayConfig::fromArray([
            'merchant_code' => 'EPAYTEST',
            'secret_key'    => 'secret',
            'environment'   => 'uat',
        ]);

        $gateway = new EsewaClient($config, new FakeTransport([]));

        $data = [
            'status'           => 'COMPLETE',
            'transaction_uuid' => 'TXN-1001',
            'total_amount'     => '100.00',
            'product_code'     => 'EPAYTEST',
        ];

        $payload = new CallbackPayload(base64_encode((string) json_encode($data)), 'wrong-signature');
        $result = $gateway->callback()->verify($payload);

        $this->assertFalse($result->valid);
        $this->assertSame(PaymentStatus::COMPLETE, $result->status);
        $this->assertSame('Invalid callback signature.', $result->message);
        $this->assertFalse($result->isSuccessful());
    }

    public function testVerifyThrowsOnFraudMismatch(): void
    {
        $config = GatewayConfig::fromArray([
            'merchant_code' => 'EPAYTEST',
            'secret_key'    => 'secret',
            'environment'   => 'uat',
        ]);

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

        $gateway->callback()->verify($payload, new VerificationExpectation(
            totalAmount: '99.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST'
        ));
    }
}
