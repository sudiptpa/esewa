<?php

declare(strict_types=1);

namespace Sujip\Esewa\Contracts;

interface TransportInterface
{
    /**
     * @param array<string, string> $query
     * @param array<string, string> $headers
     *
     * @return array<string, mixed>
     */
    public function get(string $url, array $query = [], array $headers = []): array;
}
