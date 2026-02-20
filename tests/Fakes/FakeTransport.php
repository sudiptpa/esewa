<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Fakes;

use EsewaPayment\Contracts\TransportInterface;

final class FakeTransport implements TransportInterface
{
    /** @var array<string,mixed> */
    private array $next;

    /** @var array<string,string> */
    public array $lastQuery = [];

    public string $lastUrl = '';

    /** @param array<string,mixed> $next */
    public function __construct(array $next)
    {
        $this->next = $next;
    }

    /**
     * @param array<string,string> $query
     * @param array<string,string> $headers
     * @return array<string,mixed>
     */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        $this->lastUrl = $url;
        $this->lastQuery = $query;

        return $this->next;
    }
}
