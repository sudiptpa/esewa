<?php

declare(strict_types=1);

namespace Sujip\Esewa\Support;

use Sujip\Esewa\Contracts\RetryPolicyInterface;
use Sujip\Esewa\Exception\TransportException;

final class FixedDelayRetryPolicy implements RetryPolicyInterface
{
    public function __construct(
        private readonly int $maxRetries = 2,
        private readonly int $delayUs = 150000,
    ) {
        if ($this->maxRetries < 0) {
            throw new \InvalidArgumentException('maxRetries cannot be negative.');
        }

        if ($this->delayUs < 0) {
            throw new \InvalidArgumentException('delayUs cannot be negative.');
        }
    }

    public function shouldRetry(int $attempt, TransportException $exception): bool
    {
        return $attempt < $this->maxRetries;
    }

    public function delayUs(int $attempt, TransportException $exception): int
    {
        return $this->delayUs;
    }
}
