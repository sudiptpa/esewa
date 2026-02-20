<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Domain\Checkout\CheckoutIntent;
use EsewaPayment\Domain\Checkout\CheckoutPayload;
use EsewaPayment\Domain\Checkout\CheckoutRequest;
use EsewaPayment\Service\SignatureService;

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
            $request->transactionUuid,
            $request->productCode,
            $request->signedFieldNames,
        );

        $payload = new CheckoutPayload(
            amount: number_format((float)$request->amount, 2, '.', ''),
            taxAmount: number_format((float)$request->taxAmount, 2, '.', ''),
            serviceCharge: number_format((float)$request->serviceCharge, 2, '.', ''),
            deliveryCharge: number_format((float)$request->deliveryCharge, 2, '.', ''),
            totalAmount: $totalAmount,
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
