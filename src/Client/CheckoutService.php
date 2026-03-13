<?php

declare(strict_types=1);

namespace Sujip\Esewa\Client;

use Sujip\Esewa\Config\EndpointResolver;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Domain\Checkout\CheckoutIntent;
use Sujip\Esewa\Domain\Checkout\CheckoutPayload;
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;
use Sujip\Esewa\Service\SignatureService;

final class CheckoutService
{
    public function __construct(
        private readonly GatewayConfig $config,
        private readonly EndpointResolver $endpoints,
        private readonly SignatureService $signatures,
    ) {
    }

    public function createIntent(CheckoutRequest $request): CheckoutIntent
    {
        $totalAmount = $request->totalAmount();
        $signature = $this->signatures->generate(
            $totalAmount,
            $request->transactionUuid->value(),
            $request->productCode->value(),
            $request->signedFieldNames,
        );

        $payload = new CheckoutPayload(
            amount: $request->amount,
            taxAmount: $request->taxAmount,
            serviceCharge: $request->serviceCharge,
            deliveryCharge: $request->deliveryCharge,
            totalAmount: \Sujip\Esewa\ValueObject\Amount::fromString($totalAmount),
            transactionUuid: $request->transactionUuid,
            productCode: $request->productCode,
            successUrl: $request->successUrl,
            failureUrl: $request->failureUrl,
            signedFieldNames: $request->signedFieldNames,
            signature: $signature,
        );

        return new CheckoutIntent($this->endpoints->checkoutFormUrl($this->config), $payload);
    }
}
