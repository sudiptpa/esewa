<?php

declare(strict_types=1);

namespace EsewaPayment\Config;

use EsewaPayment\Contracts\IdempotencyStoreInterface;
use EsewaPayment\Infrastructure\Idempotency\NullIdempotencyStore;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ClientOptions
{
    public function __construct(
        public readonly int $maxStatusRetries = 2,
        public readonly int $statusRetryDelayMs = 150,
        public readonly bool $preventCallbackReplay = true,
        public readonly IdempotencyStoreInterface $idempotencyStore = new NullIdempotencyStore(),
        public readonly LoggerInterface $logger = new NullLogger(),
    ) {
        if ($maxStatusRetries < 0) {
            throw new \InvalidArgumentException('maxStatusRetries cannot be negative.');
        }

        if ($statusRetryDelayMs < 0) {
            throw new \InvalidArgumentException('statusRetryDelayMs cannot be negative.');
        }
    }
}
