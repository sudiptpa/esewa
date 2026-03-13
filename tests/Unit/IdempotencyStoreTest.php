<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Unit;

use PDO;
use PHPUnit\Framework\TestCase;
use Sujip\Esewa\Infrastructure\Idempotency\FilesystemIdempotencyStore;
use Sujip\Esewa\Infrastructure\Idempotency\PdoIdempotencyStore;
use Sujip\Esewa\Tests\Fakes\FrozenClock;

final class IdempotencyStoreTest extends TestCase
{
    public function testFilesystemStoreExpiresKeys(): void
    {
        $directory = sys_get_temp_dir().'/esewa-sdk-idempotency-'.bin2hex(random_bytes(4));
        $clock = new FrozenClock(new \DateTimeImmutable('2026-03-13 00:00:00 UTC'));
        $store = new FilesystemIdempotencyStore($directory, 10, $clock);

        $store->put('callback-1');

        $this->assertTrue($store->has('callback-1'));

        $clock->advanceSeconds(11);

        $this->assertFalse($store->has('callback-1'));
    }

    public function testPdoStoreExpiresKeys(): void
    {
        if (!extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('pdo_sqlite is not available.');
        }

        $clock = new FrozenClock(new \DateTimeImmutable('2026-03-13 00:00:00 UTC'));
        $store = new PdoIdempotencyStore(new PDO('sqlite::memory:'), ttlSeconds: 10, clock: $clock);

        $store->put('callback-1');

        $this->assertTrue($store->has('callback-1'));

        $clock->advanceSeconds(11);

        $this->assertFalse($store->has('callback-1'));
    }
}
