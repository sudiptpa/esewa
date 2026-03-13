<?php

declare(strict_types=1);

namespace Sujip\Esewa\Contracts;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;
}
