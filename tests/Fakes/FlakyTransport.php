<?php

declare(strict_types=1);

namespace EsewaPayment\Tests\Fakes;

use EsewaPayment\Contracts\TransportInterface;

final class FlakyTransport implements TransportInterface
{
    /** @var array<int, array<string,mixed>|\Throwable> */
    private array $responses;

    public int $attempts = 0;

    /**
     * @param array<int, array<string,mixed>|\Throwable> $responses
     */
    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    /**
     * @param array<string,string> $query
     * @param array<string,string> $headers
     *
     * @return array<string,mixed>
     */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        ++$this->attempts;

        $response = array_shift($this->responses);
        if ($response instanceof \Throwable) {
            throw $response;
        }

        if (is_array($response)) {
            return $response;
        }

        return [];
    }
}
