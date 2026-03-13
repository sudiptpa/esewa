<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Transaction;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\ValueObject\ReferenceId;

final readonly class TransactionStatus implements Arrayable
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?ReferenceId $referenceId,
        public readonly array $raw,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::COMPLETE;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'reference_id' => $this->referenceId?->value(),
            'raw' => $this->raw,
        ];
    }
}
