<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Transaction;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Contracts\Hydratable;
use Sujip\Esewa\ValueObject\ReferenceId;

final class TransactionStatusPayload implements Arrayable, Hydratable
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

    /** @param array<string,mixed> $raw */
    public static function fromArray(array $raw): self
    {
        return new self(
            status: PaymentStatus::fromValue(isset($raw['status']) ? (string) $raw['status'] : null),
            referenceId: isset($raw['ref_id']) && (string) $raw['ref_id'] !== ''
                ? ReferenceId::fromString((string) $raw['ref_id'])
                : null,
            raw: $raw,
        );
    }

    public function toResult(): TransactionStatus
    {
        return new TransactionStatus($this->status, $this->referenceId, $this->raw);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status->value,
            'ref_id' => $this->referenceId?->value(),
            'raw' => $this->raw,
        ];
    }
}
