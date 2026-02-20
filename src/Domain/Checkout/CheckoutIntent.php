<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Checkout;

final class CheckoutIntent
{
    public function __construct(
        public readonly string $actionUrl,
        public readonly CheckoutPayload $payload,
    ) {
    }

    /** @return array<string,string> */
    public function fields(): array
    {
        return $this->payload->toArray();
    }

    /**
     * @return array{action_url:string,fields:array<string,string>}
     */
    public function form(): array
    {
        return [
            'action_url' => $this->actionUrl,
            'fields'     => $this->fields(),
        ];
    }
}
