<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Transaction;

final class StatusResult
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly PaymentStatus $status,
        public readonly ?string $referenceId,
        public readonly array $raw,
    ) {}

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::COMPLETE;
    }
}
