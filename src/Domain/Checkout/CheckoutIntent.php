<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Checkout;

final class CheckoutIntent
{
    /**
     * @param array<string, string> $fields
     */
    public function __construct(
        public readonly string $actionUrl,
        public readonly array $fields,
    ) {
    }

    /**
     * @return array{action_url:string,fields:array<string,string>}
     */
    public function form(): array
    {
        return [
            'action_url' => $this->actionUrl,
            'fields'     => $this->fields,
        ];
    }
}
