<?php

declare(strict_types=1);

namespace Sujip\Esewa\ValueObject;

final class Amount
{
    private function __construct(private readonly string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '' || !preg_match('/^\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new \InvalidArgumentException('Amount must be a non-negative decimal with up to 2 decimal places.');
        }

        return new self(number_format((float) $normalized, 2, '.', ''));
    }

    public static function fromFloat(float $value): self
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative.');
        }

        return new self(number_format($value, 2, '.', ''));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function add(self $other): self
    {
        return self::fromFloat((float) $this->value + (float) $other->value);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
