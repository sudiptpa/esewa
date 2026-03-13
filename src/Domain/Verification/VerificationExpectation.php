<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Verification;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Contracts\Hydratable;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\ReferenceId;
use Sujip\Esewa\ValueObject\TransactionUuid;

final readonly class VerificationExpectation implements Arrayable, Hydratable
{
    public readonly Amount $totalAmount;
    public readonly TransactionUuid $transactionUuid;
    public readonly ProductCode $productCode;
    public readonly ?ReferenceId $referenceId;

    public function __construct(
        string|Amount $totalAmount,
        string|TransactionUuid $transactionUuid,
        string|ProductCode $productCode,
        string|ReferenceId|null $referenceId = null,
    ) {
        $this->totalAmount = $totalAmount instanceof Amount ? $totalAmount : Amount::fromString($totalAmount);
        $this->transactionUuid = $transactionUuid instanceof TransactionUuid ? $transactionUuid : TransactionUuid::fromString($transactionUuid);
        $this->productCode = $productCode instanceof ProductCode ? $productCode : ProductCode::fromString($productCode);
        $this->referenceId = $referenceId instanceof ReferenceId || $referenceId === null ? $referenceId : ReferenceId::fromString($referenceId);
    }

    public static function make(
        string|Amount $totalAmount,
        string|TransactionUuid $transactionUuid,
        string|ProductCode $productCode,
        string|ReferenceId|null $referenceId = null,
    ): self {
        return new self(
            totalAmount: $totalAmount,
            transactionUuid: $transactionUuid,
            productCode: $productCode,
            referenceId: $referenceId,
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return self::make(
            totalAmount: (string) ($data['total_amount'] ?? $data['totalAmount'] ?? ''),
            transactionUuid: (string) ($data['transaction_uuid'] ?? $data['transactionUuid'] ?? ''),
            productCode: (string) ($data['product_code'] ?? $data['productCode'] ?? ''),
            referenceId: isset($data['reference_id']) || isset($data['referenceId'])
                ? (string) ($data['reference_id'] ?? $data['referenceId'])
                : null,
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'total_amount' => $this->totalAmount->value(),
            'transaction_uuid' => $this->transactionUuid->value(),
            'product_code' => $this->productCode->value(),
        ];

        if ($this->referenceId !== null) {
            $payload['reference_id'] = $this->referenceId->value();
        }

        return $payload;
    }
}
