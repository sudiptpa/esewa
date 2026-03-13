<?php

declare(strict_types=1);

namespace Sujip\Esewa\Contracts;

interface Arrayable
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
