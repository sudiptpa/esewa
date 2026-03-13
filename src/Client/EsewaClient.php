<?php

declare(strict_types=1);

namespace Sujip\Esewa\Client;

use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Config\EndpointResolver;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Contracts\TransportInterface;
use Sujip\Esewa\Service\CallbackVerifier;
use Sujip\Esewa\Service\SignatureService;

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
