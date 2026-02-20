<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\Config;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Transaction\StatusQuery;
use EsewaPayment\Domain\Transaction\StatusResult;

final class TransactionService
{
    public function __construct(
        private readonly Config $config,
        private readonly EndpointResolver $endpoints,
        private readonly TransportInterface $transport,
    ) {}

    public function status(StatusQuery $query): StatusResult
    {
        $payload = $this->transport->get(
            $this->endpoints->statusCheckUrl($this->config),
            [
                'product_code' => $query->productCode,
                'total_amount' => $query->totalAmount,
                'transaction_uuid' => $query->transactionUuid,
            ]
        );

        $status = PaymentStatus::fromValue(isset($payload['status']) ? (string)$payload['status'] : null);
        $referenceId = isset($payload['ref_id']) ? (string)$payload['ref_id'] : null;

        return new StatusResult($status, $referenceId, $payload);
    }
}
