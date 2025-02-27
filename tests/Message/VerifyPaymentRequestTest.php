<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Tests\TestCase;

/**
 * Class VerifyPaymentRequestTest.
 */
class VerifyPaymentRequestTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->request = new VerifyPaymentRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize([
            'merchantCode'    => 'epay_payment',
            'testMode'        => true,
            'amount'          => 100,
            'referenceNumber' => 'GDFG89',
            'productCode'     => 'ABAC2098',
        ]);
    }

    public function testVerificationSuccess()
    {
        $this->setMockHttpResponse('VerifyPaymentRequestSuccess.txt');

        $response = $this->request->send();
        $data = $response->getData();

        $this->assertInstanceOf('Omnipay\Esewa\Message\VerifyPaymentResponse', $response);

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('Success', $response->getResponseText());
    }

    public function testVerificationFailure()
    {
        $this->setMockHttpResponse('VerifyPaymentRequestFailure.txt');

        $response = $this->request->send();
        $data = $response->getData();

        $this->assertInstanceOf('Omnipay\Esewa\Message\VerifyPaymentResponse', $response);

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('failure', $response->getResponseText());
    }
}
