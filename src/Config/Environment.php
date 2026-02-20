<?php

declare(strict_types=1);

namespace EsewaPayment\Config;

enum Environment: string
{
    case UAT = 'uat';
    case PRODUCTION = 'production';

    public static function fromString(string $value): self
    {
        return match (strtolower($value)) {
            'uat', 'test', 'sandbox' => self::UAT,
            'production', 'prod', 'live' => self::PRODUCTION,
            default => throw new \InvalidArgumentException("Unsupported environment: {$value}"),
        };
    }
}
