<?php

declare(strict_types=1);

namespace Sujip\Esewa\Infrastructure\Idempotency;

use Sujip\Esewa\Contracts\IdempotencyStoreInterface;

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
