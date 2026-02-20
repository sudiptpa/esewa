<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Verification;

use EsewaPayment\Domain\Transaction\PaymentStatus;

final class CallbackVerification
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly bool $valid,
        public readonly PaymentStatus $status,
        public readonly ?string $referenceId,
        public readonly string $message,
        public readonly array $raw,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->valid && $this->status === PaymentStatus::COMPLETE;
    }
}
