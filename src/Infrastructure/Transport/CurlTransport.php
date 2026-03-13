<?php

declare(strict_types=1);

namespace Sujip\Esewa\Infrastructure\Transport;

use Sujip\Esewa\Contracts\TransportInterface;
use Sujip\Esewa\Exception\ApiErrorException;
use Sujip\Esewa\Exception\TransportException;

final class CurlTransport implements TransportInterface
{
    public function __construct(
        private readonly int $timeoutSeconds = 30,
    ) {
        if ($this->timeoutSeconds < 1) {
            throw new \InvalidArgumentException('timeoutSeconds must be at least 1.');
        }
    }

    public function get(string $url, array $query = [], array $headers = []): array
    {
        if (!function_exists('curl_init')) {
            throw new TransportException('ext-curl is required for CurlTransport.');
        }

        $fullUrl = $url;
        if ($query !== []) {
            $fullUrl .= (str_contains($url, '?') ? '&' : '?').http_build_query($query);
        }

        $curl = curl_init($fullUrl);
        if ($curl === false) {
            throw new TransportException('Failed to initialize curl transport.');
        }

        $normalizedHeaders = ['Accept: application/json'];
        foreach ($headers as $name => $value) {
            $normalizedHeaders[] = $name.': '.$value;
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => $normalizedHeaders,
        ]);

        $body = curl_exec($curl);
        if ($body === false) {
            $message = curl_error($curl);
            curl_close($curl);

            throw new TransportException('HTTP request failed: '.$message);
        }

        $statusCode = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new TransportException('Unexpected HTTP status: '.$statusCode);
        }

        if (!is_string($body)) {
            throw new ApiErrorException('Unexpected non-string response from eSewa status API.');
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new ApiErrorException('Invalid JSON response from eSewa status API.');
        }

        return $decoded;
    }
}
