<?php

declare(strict_types=1);

namespace Sujip\Esewa\Infrastructure\Idempotency;

use Sujip\Esewa\Contracts\ClockInterface;
use Sujip\Esewa\Contracts\IdempotencyStoreInterface;
use Sujip\Esewa\Support\SystemClock;

final class FilesystemIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(
        private readonly string $directory,
        private readonly int $ttlSeconds = 3600,
        private readonly ClockInterface $clock = new SystemClock(),
    ) {
        if ($this->ttlSeconds < 1) {
            throw new \InvalidArgumentException('ttlSeconds must be at least 1.');
        }

        if (!is_dir($this->directory) && !@mkdir($this->directory, 0777, true) && !is_dir($this->directory)) {
            throw new \RuntimeException(sprintf('Unable to create idempotency directory: %s', $this->directory));
        }
    }

    public function has(string $key): bool
    {
        $path = $this->path($key);

        if (!is_file($path)) {
            return false;
        }

        $expiresAt = (int) trim((string) file_get_contents($path));
        if ($expiresAt <= $this->clock->now()->getTimestamp()) {
            @unlink($path);

            return false;
        }

        return true;
    }

    public function put(string $key): void
    {
        $expiresAt = $this->clock->now()->getTimestamp() + $this->ttlSeconds;
        file_put_contents($this->path($key), (string) $expiresAt, LOCK_EX);
    }

    private function path(string $key): string
    {
        return rtrim($this->directory, '/').'/'.hash('sha256', $key).'.lock';
    }
}
