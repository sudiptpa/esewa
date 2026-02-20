<?php

declare(strict_types=1);

namespace EsewaPayment\Infrastructure\Transport;

use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Exception\ApiErrorException;
use EsewaPayment\Exception\TransportException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;

final class Psr18Transport implements TransportInterface
{
    public function __construct(
        private readonly ClientInterface $http,
        private readonly RequestFactoryInterface $requests,
    ) {
    }

    /**
     * @param array<string,string> $query
     * @param array<string,string> $headers
     *
     * @return array<string,mixed>
     */
    public function get(string $url, array $query = [], array $headers = []): array
    {
        $fullUrl = $url.(str_contains($url, '?') ? '&' : '?').http_build_query($query);
        $request = $this->requests->createRequest('GET', $fullUrl);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        try {
            $response = $this->http->sendRequest($request);
        } catch (\Throwable $e) {
            throw new TransportException('HTTP request failed: '.$e->getMessage(), 0, $e);
        }

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new TransportException('Unexpected HTTP status: '.$response->getStatusCode());
        }

        $decoded = json_decode((string) $response->getBody(), true);
        if (!is_array($decoded)) {
            throw new ApiErrorException('Invalid JSON response from eSewa status API.');
        }

        return $decoded;
    }
}
