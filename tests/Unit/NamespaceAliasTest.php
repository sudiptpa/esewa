<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use Sujip\Esewa\Domain\Checkout\CheckoutRequest;
use PHPUnit\Framework\TestCase;
use Sujip\Esewa\Esewa;

final class NamespaceAliasTest extends TestCase
{
    public function testFrameworkAgnosticNamespaceEntryPointBootstrapsCoreClient(): void
    {
        $client = Esewa::make(
            merchantCode: 'EPAYTEST',
            secretKey: 'secret',
            environment: 'uat',
        );

        $intent = $client->checkout()->createIntent(new CheckoutRequest(
            amount: '100',
            taxAmount: '0',
            serviceCharge: '0',
            deliveryCharge: '0',
            transactionUuid: 'TXN-ALIAS-1',
            productCode: 'EPAYTEST',
            successUrl: 'https://merchant.test/success',
            failureUrl: 'https://merchant.test/failure',
        ));

        $this->assertSame('TXN-ALIAS-1', $intent->fields()['transaction_uuid']);
    }
}
