<?php

declare(strict_types=1);

namespace Sujip\Esewa\Infrastructure\Idempotency;

use PDO;
use Sujip\Esewa\Contracts\ClockInterface;
use Sujip\Esewa\Contracts\IdempotencyStoreInterface;
use Sujip\Esewa\Support\SystemClock;

final class PdoIdempotencyStore implements IdempotencyStoreInterface
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $table = 'esewa_idempotency_keys',
        private readonly int $ttlSeconds = 3600,
        private readonly ClockInterface $clock = new SystemClock(),
    ) {
        if ($this->ttlSeconds < 1) {
            throw new \InvalidArgumentException('ttlSeconds must be at least 1.');
        }

        $this->initialize();
    }

    public function has(string $key): bool
    {
        $this->purgeExpired();

        $statement = $this->pdo->prepare(sprintf('SELECT 1 FROM %s WHERE idempotency_key = :key LIMIT 1', $this->table));
        $statement->execute(['key' => $key]);

        return (bool) $statement->fetchColumn();
    }

    public function put(string $key): void
    {
        $statement = $this->pdo->prepare(sprintf(
            'INSERT OR REPLACE INTO %s (idempotency_key, expires_at) VALUES (:key, :expires_at)',
            $this->table
        ));

        $statement->execute([
            'key' => $key,
            'expires_at' => $this->clock->now()->getTimestamp() + $this->ttlSeconds,
        ]);
    }

    private function initialize(): void
    {
        $this->pdo->exec(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (idempotency_key VARCHAR(255) PRIMARY KEY, expires_at INTEGER NOT NULL)',
            $this->table
        ));
    }

    private function purgeExpired(): void
    {
        $statement = $this->pdo->prepare(sprintf('DELETE FROM %s WHERE expires_at <= :expires_at', $this->table));
        $statement->execute([
            'expires_at' => $this->clock->now()->getTimestamp(),
        ]);
    }
}
