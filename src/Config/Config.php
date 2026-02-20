<?php

declare(strict_types=1);

namespace EsewaPayment\Config;

final class Config
{
    public function __construct(
        public readonly string $merchantCode,
        #[\SensitiveParameter]
        public readonly string $secretKey,
        public readonly Environment $environment = Environment::UAT,
        public readonly ?string $checkoutFormUrl = null,
        public readonly ?string $statusCheckUrl = null,
    ) {
        if ($merchantCode === '') {
            throw new \InvalidArgumentException('merchantCode is required.');
        }

        if ($secretKey === '') {
            throw new \InvalidArgumentException('secretKey is required.');
        }
    }

    /** @param array<string, string> $config */
    public static function fromArray(array $config): self
    {
        return new self(
            merchantCode: (string) ($config['merchant_code'] ?? ''),
            secretKey: (string) ($config['secret_key'] ?? ''),
            environment: Environment::fromString((string) ($config['environment'] ?? 'uat')),
            checkoutFormUrl: $config['checkout_form_url'] ?? null,
            statusCheckUrl: $config['status_check_url'] ?? null,
        );
    }
}
