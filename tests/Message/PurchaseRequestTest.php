<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Tests\TestCase;

/**
 * Class PurchaseRequestTest.
 */
class PurchaseRequestTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize([
            'merchantCode'   => 'epay_payment',
            'amount'         => 100,
            'deliveryCharge' => 0,
            'serviceCharge'  => 0,
            'taxAmount'      => 0,
            'totalAmount'    => 100,
            'productCode'    => 'ABAC2098',
            'returnUrl'      => 'https://merchant.com/payment/1/complete',
            'failedUrl'      => 'https://merchant.com/payment/1/failed',
        ]);
    }

    public function testSend()
    {
        $response = $this->request->send();

        $this->assertInstanceOf('Omnipay\Esewa\Message\PurchaseResponse', $response);
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());

        $this->assertSame('https://epay.esewa.com.np/api/epay/main/v2/form', $response->getRedirectUrl());
        $this->assertSame('POST', $response->getRedirectMethod());

        $data = $response->getData();
        $this->assertArrayHasKey('amount', $data);
        $this->assertSame('100.00', $data['amount']);
    }
}
