<?php

declare(strict_types=1);

namespace Sujip\Esewa\Support;

use Sujip\Esewa\Contracts\ClockInterface;

final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}
