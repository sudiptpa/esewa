<?php

declare(strict_types=1);

namespace EsewaPayment\Infrastructure\Idempotency;

use EsewaPayment\Contracts\IdempotencyStoreInterface;

final class InMemoryIdempotencyStore implements IdempotencyStoreInterface
{
    /** @var array<string, true> */
    private array $keys = [];

    public function has(string $key): bool
    {
        return isset($this->keys[$key]);
    }

    public function put(string $key): void
    {
        $this->keys[$key] = true;
    }
}
