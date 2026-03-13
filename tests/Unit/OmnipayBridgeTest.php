<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Omnipay\Esewa\Message\CompletePurchaseResponse;
use Omnipay\Esewa\Message\CompletePurchaseRequest;
use Omnipay\Esewa\Message\PurchaseResponse;
use Omnipay\Esewa\Message\PurchaseRequest;
use Omnipay\Esewa\Message\VerifyPaymentResponse;
use Omnipay\Esewa\Message\VerifyPaymentRequest;
use Omnipay\Esewa\SecureGateway;
use PHPUnit\Framework\TestCase;
use Sujip\Esewa\Service\SignatureService;
use Sujip\Esewa\Tests\Fakes\FakeTransport;

final class OmnipayBridgeTest extends TestCase
{
    public function testPurchaseRequestBuildsRedirectResponse(): void
    {
        $gateway = new SecureGateway();
        $gateway->setMerchantCode('EPAYTEST');
        $gateway->setSecretKey('secret');
        $gateway->setProductCode('EPAYTEST');
        $gateway->setTestMode(true);
        $gateway->setReturnUrl('https://merchant.test/success');
        $gateway->setFailureUrl('https://merchant.test/failure');

        $response = $gateway->purchase([
            'amount' => '100',
            'transactionId' => 'TXN-2001',
        ])->send();

        $this->assertInstanceOf(PurchaseResponse::class, $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertSame('POST', $response->getRedirectMethod());
        $this->assertStringContainsString('/v2/form', $response->getRedirectUrl());
        $this->assertSame('TXN-2001', $response->getRedirectData()['transaction_uuid']);
        $this->assertSame('100.00', $response->getRedirectData()['total_amount']);
    }

    public function testVerifyPaymentRequestMapsStatusResponse(): void
    {
        $transport = new FakeTransport([
            'status' => 'COMPLETE',
            'ref_id' => 'REF-2001',
        ]);

        $gateway = new SecureGateway();
        $gateway->setMerchantCode('EPAYTEST');
        $gateway->setSecretKey('secret');
        $gateway->setProductCode('EPAYTEST');
        $gateway->setTestMode(true);

        $response = $gateway->verifyPayment([
            'amount' => '100.00',
            'transactionId' => 'TXN-2001',
            'transport' => $transport,
        ])->send();

        $this->assertInstanceOf(VerifyPaymentResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('COMPLETE', $response->getResponseText());
        $this->assertSame('REF-2001', $response->getReferenceId());
        $this->assertSame('TXN-2001', $transport->lastQuery['transaction_uuid']);
    }

    public function testCompletePurchaseRequestVerifiesCallbackPayload(): void
    {
        $request = new CompletePurchaseRequest();
        $request->initialize([
            'merchantCode' => 'EPAYTEST',
            'secretKey' => 'secret',
            'productCode' => 'EPAYTEST',
            'transactionUuid' => 'TXN-2001',
            'totalAmount' => '100.00',
            'referenceNumber' => 'REF-2001',
        ]);

        $data = [
            'status' => 'COMPLETE',
            'transaction_uuid' => 'TXN-2001',
            'total_amount' => '100.00',
            'product_code' => 'EPAYTEST',
            'signed_field_names' => 'total_amount,transaction_uuid,product_code',
            'transaction_code' => 'REF-2001',
        ];

        $signature = (new SignatureService('secret'))->generate(
            '100.00',
            'TXN-2001',
            'EPAYTEST',
            'total_amount,transaction_uuid,product_code'
        );

        $this->setHttpRequestPayload($request, [
            'data' => base64_encode((string) json_encode($data)),
            'signature' => $signature,
        ]);

        $response = $request->send();

        $this->assertInstanceOf(CompletePurchaseResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertSame('REF-2001', $response->getTransactionReference());
        $this->assertSame('COMPLETE', $response->getData()['status']);
    }

    public function testSecureGatewayCreatesExpectedRequestTypes(): void
    {
        $gateway = new SecureGateway();

        $this->assertInstanceOf(PurchaseRequest::class, $gateway->purchase());
        $this->assertInstanceOf(CompletePurchaseRequest::class, $gateway->completePurchase());
        $this->assertInstanceOf(VerifyPaymentRequest::class, $gateway->verifyPayment());
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function setHttpRequestPayload(CompletePurchaseRequest $request, array $payload): void
    {
        $httpRequest = new class ($payload) {
            public object $query;
            public object $request;

            /**
             * @param array<string, mixed> $payload
             */
            public function __construct(array $payload)
            {
                $this->query = new class {
                    /**
                     * @return array<string, mixed>
                     */
                    public function all(): array
                    {
                        return [];
                    }
                };
                $this->request = new class ($payload) {
                    /**
                     * @param array<string, mixed> $payload
                     */
                    public function __construct(private readonly array $payload)
                    {
                    }

                    /**
                     * @return array<string, mixed>
                     */
                    public function all(): array
                    {
                        return $this->payload;
                    }
                };
            }
        };

        $reflection = new \ReflectionProperty($request, 'httpRequest');
        $reflection->setValue($request, $httpRequest);
    }
}
