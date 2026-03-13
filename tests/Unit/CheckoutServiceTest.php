<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Client\EsewaClient;
use Sujip\Esewa\Esewa;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;
use Sujip\Esewa\Tests\Fakes\FakeTransport;
use PHPUnit\Framework\TestCase;

final class CheckoutServiceTest extends TestCase
{
    public function testCreateIntentBuildsFormFields(): void
    {
        $gateway = new EsewaClient(
            GatewayConfig::make(
                merchantCode: 'EPAYTEST',
                secretKey: 'secret',
                environment: 'uat',
            ),
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

    public function testFactoryCanBootstrapWithoutExplicitTransportForCheckout(): void
    {
        $gateway = Esewa::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $intent = $gateway->checkout()->createIntent(new CheckoutRequest(
            amount: '100',
            taxAmount: '0',
            serviceCharge: '0',
            deliveryCharge: '0',
            transactionUuid: 'TXN-1002',
            productCode: 'EPAYTEST',
            successUrl: 'https://merchant.test/success',
            failureUrl: 'https://merchant.test/failure',
        ));

        $this->assertSame('TXN-1002', $intent->fields()['transaction_uuid']);
    }
}
