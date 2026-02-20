<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Domain\Transaction\TransactionStatus;
use EsewaPayment\Domain\Transaction\TransactionStatusPayload;
use EsewaPayment\Domain\Transaction\TransactionStatusRequest;

final class TransactionService
{
    public function __construct(
        private readonly GatewayConfig $config,
        private readonly EndpointResolver $endpoints,
        private readonly TransportInterface $transport,
    ) {
    }

    public function fetchStatus(TransactionStatusRequest $query): TransactionStatus
    {
        $payload = $this->transport->get(
            $this->endpoints->statusCheckUrl($this->config),
            [
                'product_code'     => $query->productCode,
                'total_amount'     => $query->totalAmount,
                'transaction_uuid' => $query->transactionUuid,
            ]
        );

        return TransactionStatusPayload::fromArray($payload)->toResult();
    }

    public function status(TransactionStatusRequest $query): TransactionStatus
    {
        return $this->fetchStatus($query);
    }
}
