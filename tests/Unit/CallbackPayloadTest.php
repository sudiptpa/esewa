<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Exception\InvalidPayloadException;
use PHPUnit\Framework\TestCase;

final class CallbackPayloadTest extends TestCase
{
    public function testFromArrayThrowsWhenDataOrSignatureMissing(): void
    {
        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('data and signature are required in callback payload.');

        CallbackPayload::fromArray(['data' => '']);
    }

    public function testDecodedDataThrowsOnInvalidBase64(): void
    {
        $payload = new CallbackPayload('not-base64', 'signature');

        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Callback data is not valid base64.');

        $payload->decodedData();
    }

    public function testDecodedDataThrowsOnInvalidJson(): void
    {
        $payload = new CallbackPayload(base64_encode('not-json'), 'signature');

        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Callback data is not valid JSON.');

        $payload->decodedData();
    }

    public function testDecodedDataThrowsWhenRequiredFieldsMissing(): void
    {
        $payload = new CallbackPayload(
            base64_encode((string) json_encode(['status' => 'COMPLETE'])),
            'signature'
        );

        $this->expectException(InvalidPayloadException::class);
        $this->expectExceptionMessage('Callback data is missing required fields.');

        $payload->decodedData();
    }

    public function testDecodedDataMapsUnknownStatusToUnknown(): void
    {
        $payload = new CallbackPayload(
            base64_encode((string) json_encode([
                'status'           => 'SOMETHING_NEW',
                'transaction_uuid' => 'TXN-1001',
                'total_amount'     => '100.00',
                'product_code'     => 'EPAYTEST',
            ])),
            'signature'
        );

        $data = $payload->decodedData();

        $this->assertSame(PaymentStatus::UNKNOWN, $data->status);
    }
}
