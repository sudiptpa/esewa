<?php

declare(strict_types=1);

namespace EsewaPayment\Infrastructure\Idempotency;

use EsewaPayment\Contracts\IdempotencyStoreInterface;

final class NullIdempotencyStore implements IdempotencyStoreInterface
{
    public function has(string $key): bool
    {
        return false;
    }

    public function put(string $key): void
    {
    }
}
