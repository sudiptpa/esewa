<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Client\EsewaGateway;
use EsewaPayment\Config\Config;
use EsewaPayment\Domain\Checkout\CheckoutRequest;
use EsewaPayment\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class CheckoutServiceTest extends TestCase
{
    public function testCreateIntentBuildsFormFields(): void
    {
        $gateway = new EsewaGateway(
            Config::fromArray([
                'merchant_code' => 'EPAYTEST',
                'secret_key' => 'secret',
                'environment' => 'uat',
            ]),
            new FakeTransport([])
        );

        $intent = $gateway->checkout()->createIntent(new CheckoutRequest(
            amount: '100',
            taxAmount: '0',
            serviceCharge: '0',
            deliveryCharge: '0',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST',
            successUrl: 'https://merchant.test/success',
            failureUrl: 'https://merchant.test/failure',
        ));

        $form = $intent->form();

        $this->assertStringContainsString('/v2/form', $form['action_url']);
        $this->assertSame('100.00', $form['fields']['total_amount']);
        $this->assertSame('TXN-1001', $form['fields']['transaction_uuid']);
        $this->assertNotSame('', $form['fields']['signature']);
    }
}
