<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Tests\TestCase;

/**
 * Class PurchaseResponseTest.
 */
class PurchaseResponseTest extends TestCase
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

    public function testRedirect()
    {
        $data = ['test' => '123'];

        $response = new PurchaseResponse($this->getMockRequest(), $data, 'https://example.com/');

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());

        $this->assertSame('https://example.com/', $response->getRedirectUrl());
        $this->assertSame('POST', $response->getRedirectMethod());
        $this->assertSame($data, $response->getRedirectData());
    }

    public function testSuccessResponse()
    {
        $this->setMockHttpResponse('PurchaseResponseSuccess.txt');

        $response = $this->request->send();
        $data = $response->getData();

        $this->assertInstanceOf('Omnipay\Esewa\Message\PurchaseResponse', $response);

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
    }
}
