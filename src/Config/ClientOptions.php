<?php

declare(strict_types=1);

namespace Sujip\Esewa\Config;

use Sujip\Esewa\Contracts\ClockInterface;
use Sujip\Esewa\Contracts\IdempotencyStoreInterface;
use Sujip\Esewa\Contracts\RetryPolicyInterface;
use Sujip\Esewa\Infrastructure\Idempotency\NullIdempotencyStore;
use Sujip\Esewa\Support\FixedDelayRetryPolicy;
use Sujip\Esewa\Support\SystemClock;

final readonly class ClientOptions
{
    public readonly RetryPolicyInterface $retryPolicy;
    public readonly ClockInterface $clock;

    public function __construct(
        public readonly int $maxStatusRetries = 2,
        public readonly int $statusRetryDelayMs = 150,
        public readonly bool $preventCallbackReplay = true,
        public readonly IdempotencyStoreInterface $idempotencyStore = new NullIdempotencyStore(),
        ?RetryPolicyInterface $retryPolicy = null,
        ?ClockInterface $clock = null,
    ) {
        if ($maxStatusRetries < 0) {
            throw new \InvalidArgumentException('maxStatusRetries cannot be negative.');
        }

        if ($statusRetryDelayMs < 0) {
            throw new \InvalidArgumentException('statusRetryDelayMs cannot be negative.');
        }

        $this->retryPolicy = $retryPolicy ?? new FixedDelayRetryPolicy(
            maxRetries: $maxStatusRetries,
            delayUs: $statusRetryDelayMs * 1000,
        );
        $this->clock = $clock ?? new SystemClock();
    }
}
