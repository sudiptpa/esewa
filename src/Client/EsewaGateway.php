<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\Config;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Service\CallbackVerifier;
use EsewaPayment\Service\SignatureService;

final class EsewaGateway
{
    private readonly CheckoutService $checkout;
    private readonly CallbackService $callback;
    private readonly TransactionService $transactions;

    public function __construct(
        Config $config,
        TransportInterface $transport,
    ) {
        $endpoints = new EndpointResolver();
        $signatures = new SignatureService($config->secretKey);

        $this->checkout = new CheckoutService($config, $endpoints, $signatures);
        $this->callback = new CallbackService(new CallbackVerifier($signatures));
        $this->transactions = new TransactionService($config, $endpoints, $transport);
    }

    public function checkout(): CheckoutService
    {
        return $this->checkout;
    }

    public function callback(): CallbackService
    {
        return $this->callback;
    }

    public function transactions(): TransactionService
    {
        return $this->transactions;
    }
}
