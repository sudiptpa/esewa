<?php

declare(strict_types=1);

namespace Sujip\Esewa\Tests\Fakes;

use Sujip\Esewa\Contracts\ClockInterface;

final class FrozenClock implements ClockInterface
{
    public function __construct(private \DateTimeImmutable $now)
    {
    }

    public function now(): \DateTimeImmutable
    {
        return $this->now;
    }

    public function advanceSeconds(int $seconds): void
    {
        $this->now = $this->now->modify(sprintf('+%d seconds', $seconds));
    }
}
