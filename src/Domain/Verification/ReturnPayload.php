<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Verification;

use EsewaPayment\Exception\InvalidPayloadException;

final class ReturnPayload
{
    public function __construct(
        public readonly string $data,
        public readonly string $signature,
    ) {
        if ($data === '' || $signature === '') {
            throw new InvalidPayloadException('data and signature are required in callback payload.');
        }
    }

    /** @param array<string,mixed> $payload */
    public static function fromArray(array $payload): self
    {
        return new self(
            data: (string)($payload['data'] ?? ''),
            signature: (string)($payload['signature'] ?? ''),
        );
    }

    /** @return array<string,mixed> */
    public function decodedData(): array
    {
        $decoded = base64_decode($this->data, true);
        if ($decoded === false) {
            throw new InvalidPayloadException('Callback data is not valid base64.');
        }

        $json = json_decode($decoded, true);
        if (!is_array($json)) {
            throw new InvalidPayloadException('Callback data is not valid JSON.');
        }

        return $json;
    }
}
