<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Checkout;

final class CheckoutRequest
{
    public function __construct(
        public readonly string $amount,
        public readonly string $taxAmount,
        public readonly string $serviceCharge,
        public readonly string $deliveryCharge,
        public readonly string $transactionUuid,
        public readonly string $productCode,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly string $signedFieldNames = 'total_amount,transaction_uuid,product_code',
    ) {
        foreach ([
            'transactionUuid' => $transactionUuid,
            'productCode' => $productCode,
            'successUrl' => $successUrl,
            'failureUrl' => $failureUrl,
        ] as $field => $value) {
            if ($value === '') {
                throw new \InvalidArgumentException("{$field} is required.");
            }
        }
    }

    public function totalAmount(): string
    {
        $total = (float)$this->amount
            + (float)$this->taxAmount
            + (float)$this->serviceCharge
            + (float)$this->deliveryCharge;

        return number_format($total, 2, '.', '');
    }
}
