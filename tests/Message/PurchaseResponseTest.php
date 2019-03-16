<?php

namespace Omnipay\NABTransact\Message;

use Omnipay\Esewa\Message\PurchaseResponse;
use Omnipay\Tests\TestCase;

/**
 * Class PurchaseResponseTest.
 */
class PurchaseResponseTest extends TestCase
{
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
}
