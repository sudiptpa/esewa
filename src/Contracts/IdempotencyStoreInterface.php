<?php

declare(strict_types=1);

namespace EsewaPayment\Contracts;

interface IdempotencyStoreInterface
{
    public function has(string $key): bool;

    public function put(string $key): void;
}
