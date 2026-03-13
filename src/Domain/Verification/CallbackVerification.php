<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Verification;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Domain\Transaction\PaymentStatus;
use Sujip\Esewa\ValueObject\ReferenceId;

final class CallbackVerification implements Arrayable
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly VerificationState $state,
        public readonly bool $valid,
        public readonly PaymentStatus $status,
        public readonly ?ReferenceId $referenceId,
        public readonly string $message,
        public readonly array $raw,
    ) {
    }

    public function isSuccessful(): bool
    {
        return $this->valid && $this->status === PaymentStatus::COMPLETE;
    }

    public function isReplayed(): bool
    {
        return $this->state === VerificationState::REPLAYED;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'state' => $this->state->value,
            'valid' => $this->valid,
            'status' => $this->status->value,
            'reference_id' => $this->referenceId?->value(),
            'message' => $this->message,
            'raw' => $this->raw,
        ];
    }
}
