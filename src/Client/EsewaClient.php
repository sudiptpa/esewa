<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Service\CallbackVerifier;
use EsewaPayment\Service\SignatureService;

final class EsewaClient
{
    private readonly CheckoutService $checkout;
    private readonly CallbackService $callback;
    private readonly TransactionService $transactions;

    public function __construct(
        GatewayConfig $config,
        TransportInterface $transport,
        ?ClientOptions $options = null,
    ) {
        $options ??= new ClientOptions();

        $endpoints = new EndpointResolver();
        $signatures = new SignatureService($config->secretKey);

        $this->checkout = new CheckoutService($config, $endpoints, $signatures);
        $this->callback = new CallbackService(new CallbackVerifier($signatures, $options));
        $this->transactions = new TransactionService($config, $endpoints, $transport, $options);
    }

    public function checkout(): CheckoutService
    {
        return $this->checkout;
    }

    public function callbacks(): CallbackService
    {
        return $this->callback;
    }

    public function transactions(): TransactionService
    {
        return $this->transactions;
    }
}
