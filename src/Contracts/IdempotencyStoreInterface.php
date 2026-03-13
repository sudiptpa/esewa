<?php

declare(strict_types=1);

namespace Sujip\Esewa\Contracts;

interface IdempotencyStoreInterface
{
    public function has(string $key): bool;

    public function put(string $key): void;
}
