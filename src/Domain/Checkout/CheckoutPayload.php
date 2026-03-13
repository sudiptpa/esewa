<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Checkout;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\TransactionUuid;

final readonly class CheckoutPayload implements Arrayable
{
    public function __construct(
        public readonly Amount $amount,
        public readonly Amount $taxAmount,
        public readonly Amount $serviceCharge,
        public readonly Amount $deliveryCharge,
        public readonly Amount $totalAmount,
        public readonly TransactionUuid $transactionUuid,
        public readonly ProductCode $productCode,
        public readonly string $successUrl,
        public readonly string $failureUrl,
        public readonly string $signedFieldNames,
        public readonly string $signature,
    ) {
    }

    /** @return array<string,string> */
    public function toArray(): array
    {
        return [
            'amount'                  => $this->amount->value(),
            'tax_amount'              => $this->taxAmount->value(),
            'product_service_charge'  => $this->serviceCharge->value(),
            'product_delivery_charge' => $this->deliveryCharge->value(),
            'total_amount'            => $this->totalAmount->value(),
            'transaction_uuid'        => $this->transactionUuid->value(),
            'product_code'            => $this->productCode->value(),
            'success_url'             => $this->successUrl,
            'failure_url'             => $this->failureUrl,
            'signed_field_names'      => $this->signedFieldNames,
            'signature'               => $this->signature,
        ];
    }
}
