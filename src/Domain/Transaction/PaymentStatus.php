<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Transaction;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case COMPLETE = 'COMPLETE';
    case FULL_REFUND = 'FULL_REFUND';
    case PARTIAL_REFUND = 'PARTIAL_REFUND';
    case AMBIGUOUS = 'AMBIGUOUS';
    case NOT_FOUND = 'NOT_FOUND';
    case CANCELED = 'CANCELED';
    case UNKNOWN = 'UNKNOWN';

    public static function fromValue(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::UNKNOWN;
    }
}
