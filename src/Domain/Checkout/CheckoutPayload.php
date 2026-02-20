<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Checkout;

final class CheckoutPayload
{
    public function __construct(
        public readonly string $amount,
        public readonly string $taxAmount,
        public readonly string $serviceCharge,
        public readonly string $deliveryCharge,
        public readonly string $totalAmount,
        public readonly string $transactionUuid,
        public readonly string $productCode,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly string $signedFieldNames,
        public readonly string $signature,
    ) {}

    /** @return array<string,string> */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount,
            'tax_amount' => $this->taxAmount,
            'product_service_charge' => $this->serviceCharge,
            'product_delivery_charge' => $this->deliveryCharge,
            'total_amount' => $this->totalAmount,
            'transaction_uuid' => $this->transactionUuid,
            'product_code' => $this->productCode,
            'success_url' => $this->successUrl,
            'failure_url' => $this->failureUrl,
            'signed_field_names' => $this->signedFieldNames,
            'signature' => $this->signature,
        ];
    }
}
