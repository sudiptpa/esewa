<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Transaction;

final class TransactionStatusPayload
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?string $referenceId,
        public readonly array $raw,
    ) {
    }

    /** @param array<string,mixed> $raw */
    public static function fromArray(array $raw): self
    {
        return new self(
            status: PaymentStatus::fromValue(isset($raw['status']) ? (string) $raw['status'] : null),
            referenceId: isset($raw['ref_id']) ? (string) $raw['ref_id'] : null,
            raw: $raw,
        );
    }

    public function toResult(): TransactionStatus
    {
        return new TransactionStatus($this->status, $this->referenceId, $this->raw);
    }
}
