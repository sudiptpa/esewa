<?php

declare(strict_types=1);

namespace Sujip\Esewa\Contracts;

use Sujip\Esewa\Exception\TransportException;

interface RetryPolicyInterface
{
    public function shouldRetry(int $attempt, TransportException $exception): bool;

    public function delayUs(int $attempt, TransportException $exception): int;
}
