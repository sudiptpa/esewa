<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaGateway;
use EsewaPayment\Config\Config;
use EsewaPayment\Domain\Verification\ReturnPayload;
use EsewaPayment\Domain\Verification\VerificationContext;
use EsewaPayment\Exception\FraudValidationException;
use EsewaPayment\Service\SignatureService;
use EsewaPayment\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class CallbackServiceTest extends TestCase
{
    public function testVerifyValidCallbackPayload(): void
    {
        $config = Config::fromArray([
            'merchant_code' => 'EPAYTEST',
            'secret_key' => 'secret',
            'environment' => 'uat',
        ]);

        $gateway = new EsewaGateway($config, new FakeTransport([]));

        $data = [
            'status' => 'COMPLETE',
            'transaction_uuid' => 'TXN-1001',
            'total_amount' => '100.00',
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'transaction_code' => 'R-1001',
        ];

        $signature = (new SignatureService('secret'))->generate(
            '100.00',
            'TXN-1001',
            'EPAYTEST',
            'total_amount,transaction_uuid,product_code'
        );

        $payload = new ReturnPayload(base64_encode((string)json_encode($data)), $signature);

        $result = $gateway->callback()->verify($payload, new VerificationContext(
            totalAmount: '100.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST',
            referenceId: 'R-1001'
        ));

        $this->assertTrue($result->valid);
        $this->assertTrue($result->isSuccessful());
    }

    public function testVerifyThrowsOnFraudMismatch(): void
    {
        $config = Config::fromArray([
            'merchant_code' => 'EPAYTEST',
            'secret_key' => 'secret',
            'environment' => 'uat',
        ]);

        $gateway = new EsewaGateway($config, new FakeTransport([]));

        $data = [
            'status' => 'COMPLETE',
            'transaction_uuid' => 'TXN-1001',
            'total_amount' => '100.00',
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
        ];

        $signature = (new SignatureService('secret'))->generate('100.00', 'TXN-1001', 'EPAYTEST');
        $payload = new ReturnPayload(base64_encode((string)json_encode($data)), $signature);

        $this->expectException(FraudValidationException::class);

        $gateway->callback()->verify($payload, new VerificationContext(
            totalAmount: '99.00',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST'
        ));
    }
}
