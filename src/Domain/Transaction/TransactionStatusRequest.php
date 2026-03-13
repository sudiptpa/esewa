<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Transaction;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Contracts\Hydratable;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\TransactionUuid;

final readonly class TransactionStatusRequest implements Arrayable, Hydratable
{
    public readonly TransactionUuid $transactionUuid;
    public readonly Amount $totalAmount;
    public readonly ProductCode $productCode;

    public function __construct(
        string|TransactionUuid $transactionUuid,
        string|Amount $totalAmount,
        string|ProductCode $productCode,
    ) {
        $this->transactionUuid = $transactionUuid instanceof TransactionUuid ? $transactionUuid : TransactionUuid::fromString($transactionUuid);
        $this->totalAmount = $totalAmount instanceof Amount ? $totalAmount : Amount::fromString($totalAmount);
        $this->productCode = $productCode instanceof ProductCode ? $productCode : ProductCode::fromString($productCode);
    }

    public static function make(
        string|TransactionUuid $transactionUuid,
        string|Amount $totalAmount,
        string|ProductCode $productCode,
    ): self {
        return new self(
            transactionUuid: $transactionUuid,
            totalAmount: $totalAmount,
            productCode: $productCode,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return self::make(
            transactionUuid: (string) ($data['transaction_uuid'] ?? $data['transactionUuid'] ?? ''),
            totalAmount: (string) ($data['total_amount'] ?? $data['totalAmount'] ?? ''),
            productCode: (string) ($data['product_code'] ?? $data['productCode'] ?? ''),
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'transaction_uuid' => $this->transactionUuid->value(),
            'total_amount' => $this->totalAmount->value(),
            'product_code' => $this->productCode->value(),
        ];
    }
}
