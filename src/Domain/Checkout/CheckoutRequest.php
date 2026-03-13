<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Checkout;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Contracts\Hydratable;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\TransactionUuid;

final readonly class CheckoutRequest implements Arrayable, Hydratable
{
    public readonly Amount $amount;
    public readonly Amount $taxAmount;
    public readonly Amount $serviceCharge;
    public readonly Amount $deliveryCharge;
    public readonly TransactionUuid $transactionUuid;
    public readonly ProductCode $productCode;
    public readonly string $successUrl;
    public readonly string $failureUrl;
    public readonly string $signedFieldNames;

    public function __construct(
        string|Amount $amount,
        string|Amount $taxAmount,
        string|Amount $serviceCharge,
        string|Amount $deliveryCharge,
        string|TransactionUuid $transactionUuid,
        string|ProductCode $productCode,
        string $successUrl,
        string $failureUrl,
        string $signedFieldNames = 'total_amount,transaction_uuid,product_code',
    ) {
        $this->amount = self::normalizeAmount($amount);
        $this->taxAmount = self::normalizeAmount($taxAmount);
        $this->serviceCharge = self::normalizeAmount($serviceCharge);
        $this->deliveryCharge = self::normalizeAmount($deliveryCharge);
        $this->transactionUuid = self::normalizeTransactionUuid($transactionUuid);
        $this->productCode = self::normalizeProductCode($productCode);
        $this->successUrl = $successUrl;
        $this->failureUrl = $failureUrl;
        $this->signedFieldNames = $signedFieldNames;

        foreach ([
            'successUrl'      => $successUrl,
            'failureUrl'      => $failureUrl,
        ] as $field => $value) {
            if ($value === '') {
                throw new \InvalidArgumentException("{$field} is required.");
            }
        }
    }

    public static function make(
        string|Amount $amount,
        string|Amount $taxAmount,
        string|Amount $serviceCharge,
        string|Amount $deliveryCharge,
        string|TransactionUuid $transactionUuid,
        string|ProductCode $productCode,
        string $successUrl,
        string $failureUrl,
        string $signedFieldNames = 'total_amount,transaction_uuid,product_code',
    ): self {
        return new self(
            amount: self::normalizeAmount($amount),
            taxAmount: self::normalizeAmount($taxAmount),
            serviceCharge: self::normalizeAmount($serviceCharge),
            deliveryCharge: self::normalizeAmount($deliveryCharge),
            transactionUuid: self::normalizeTransactionUuid($transactionUuid),
            productCode: self::normalizeProductCode($productCode),
            successUrl: $successUrl,
            failureUrl: $failureUrl,
            signedFieldNames: $signedFieldNames,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return self::make(
            amount: (string) ($data['amount'] ?? ''),
            taxAmount: (string) ($data['tax_amount'] ?? $data['taxAmount'] ?? '0'),
            serviceCharge: (string) ($data['service_charge'] ?? $data['serviceCharge'] ?? '0'),
            deliveryCharge: (string) ($data['delivery_charge'] ?? $data['deliveryCharge'] ?? '0'),
            transactionUuid: (string) ($data['transaction_uuid'] ?? $data['transactionUuid'] ?? ''),
            productCode: (string) ($data['product_code'] ?? $data['productCode'] ?? ''),
            successUrl: (string) ($data['success_url'] ?? $data['successUrl'] ?? ''),
            failureUrl: (string) ($data['failure_url'] ?? $data['failureUrl'] ?? ''),
            signedFieldNames: (string) ($data['signed_field_names'] ?? $data['signedFieldNames'] ?? 'total_amount,transaction_uuid,product_code'),
        );
    }

    public function totalAmount(): string
    {
        return $this->amount
            ->add($this->taxAmount)
            ->add($this->serviceCharge)
            ->add($this->deliveryCharge)
            ->value();
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'amount' => $this->amount->value(),
            'tax_amount' => $this->taxAmount->value(),
            'service_charge' => $this->serviceCharge->value(),
            'delivery_charge' => $this->deliveryCharge->value(),
            'transaction_uuid' => $this->transactionUuid->value(),
            'product_code' => $this->productCode->value(),
            'success_url' => $this->successUrl,
            'failure_url' => $this->failureUrl,
            'signed_field_names' => $this->signedFieldNames,
        ];
    }

    private static function normalizeAmount(string|Amount $value): Amount
    {
        return $value instanceof Amount ? $value : Amount::fromString($value);
    }

    private static function normalizeTransactionUuid(string|TransactionUuid $value): TransactionUuid
    {
        return $value instanceof TransactionUuid ? $value : TransactionUuid::fromString($value);
    }

    private static function normalizeProductCode(string|ProductCode $value): ProductCode
    {
        return $value instanceof ProductCode ? $value : ProductCode::fromString($value);
    }
}
