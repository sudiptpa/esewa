<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\Config;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Domain\Checkout\CheckoutIntent;
use EsewaPayment\Domain\Checkout\CheckoutRequest;
use EsewaPayment\Service\SignatureService;

final class CheckoutService
{
    public function __construct(
        private readonly Config $config,
        private readonly EndpointResolver $endpoints,
        private readonly SignatureService $signatures,
    ) {}

    public function createIntent(CheckoutRequest $request): CheckoutIntent
    {
        $totalAmount = $request->totalAmount();
        $signature = $this->signatures->generate(
            $totalAmount,
            $request->transactionUuid,
            $request->productCode,
            $request->signedFieldNames,
        );

        $fields = [
            'amount' => number_format((float)$request->amount, 2, '.', ''),
            'tax_amount' => number_format((float)$request->taxAmount, 2, '.', ''),
            'product_service_charge' => number_format((float)$request->serviceCharge, 2, '.', ''),
            'product_delivery_charge' => number_format((float)$request->deliveryCharge, 2, '.', ''),
            'total_amount' => $totalAmount,
            'transaction_uuid' => $request->transactionUuid,
            'product_code' => $request->productCode,
            'success_url' => $request->successUrl,
            'failure_url' => $request->failureUrl,
            'signed_field_names' => $request->signedFieldNames,
            'signature' => $signature,
        ];

        return new CheckoutIntent($this->endpoints->checkoutFormUrl($this->config), $fields);
    }
}
