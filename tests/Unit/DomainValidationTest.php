<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Unit;

use EsewaPayment\Domain\Checkout\CheckoutRequest;
use EsewaPayment\Domain\Transaction\TransactionStatusRequest;
use PHPUnit\Framework\TestCase;

final class DomainValidationTest extends TestCase
{
    public function testCheckoutRequestComputesTotalAmount(): void
    {
        $request = new CheckoutRequest(
            amount: '100',
            taxAmount: '13.5',
            serviceCharge: '2',
            deliveryCharge: '4.5',
            transactionUuid: 'TXN-1001',
            productCode: 'EPAYTEST',
            successUrl: 'https://merchant.test/success',
            failureUrl: 'https://merchant.test/failure',
        );

        $this->assertSame('120.00', $request->totalAmount());
    }

    public function testCheckoutRequestThrowsWhenRequiredFieldMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('transactionUuid is required.');

        new CheckoutRequest(
            amount: '100',
            taxAmount: '0',
            serviceCharge: '0',
            deliveryCharge: '0',
            transactionUuid: '',
            productCode: 'EPAYTEST',
            successUrl: 'https://merchant.test/success',
            failureUrl: 'https://merchant.test/failure',
        );
    }

    public function testTransactionStatusRequestThrowsWhenRequiredFieldMissing(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('transactionUuid, totalAmount and productCode are required.');

        new TransactionStatusRequest(
            transactionUuid: '',
            totalAmount: '100.00',
            productCode: 'EPAYTEST',
        );
    }
}
