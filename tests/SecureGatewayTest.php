<?php

namespace Omnipay\Esewa;

use Omnipay\Tests\GatewayTestCase;

/**
 * Class SecureGatewayTest.
 */
class SecureGatewayTest extends GatewayTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->gateway = new SecureGateway($this->getHttpClient(), $this->getHttpRequest());
        $this->gateway->setMerchantCode('epay_payment');
    }

    public function testPurchase()
    {
        $request = $this->gateway->purchase([
            'amount'         => 100,
            'deliveryCharge' => 0,
            'serviceCharge'  => 0,
            'taxAmount'      => 0,
            'totalAmount'    => 100,
            'productCode'    => 'ABAC2098',
            'returnUrl'      => 'https://merchant.com/payment/1/complete',
            'failedUrl'      => 'https://merchant.com/payment/1/failed',
        ]);

        $this->assertInstanceOf('\Omnipay\Esewa\Message\PurchaseRequest', $request);
        $this->assertSame('100.00', $request->getAmount());
        $this->assertSame(0, $request->getDeliveryCharge());
        $this->assertSame(0, $request->getServiceCharge());
        $this->assertSame(0, $request->getTaxAmount());
        $this->assertSame(100, $request->getTotalAmount());
        $this->assertSame('ABAC2098', $request->getProductCode());
        $this->assertSame('https://merchant.com/payment/1/complete', $request->getReturnUrl());
        $this->assertSame('https://merchant.com/payment/1/failed', $request->getFailedUrl());
    }

    public function testVerifyPayment()
    {
        $request = $this->gateway->verifyPayment([
            'amount'          => 100,
            'referenceNumber' => 'ESEWA1001',
            'productCode'     => 'ABAC2098',
        ]);

        $this->assertInstanceOf('\Omnipay\Esewa\Message\VerifyPaymentRequest', $request);
        $this->assertSame('100.00', $request->getAmount());
        $this->assertSame('ESEWA1001', $request->getReferenceNumber());
        $this->assertSame('ABAC2098', $request->getProductCode());
    }
}
