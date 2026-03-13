<?php

declare(strict_types=1);

namespace Sujip\Esewa\Client;

use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Config\EndpointResolver;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Contracts\TransportInterface;
use Sujip\Esewa\Domain\Transaction\TransactionStatus;
use Sujip\Esewa\Domain\Transaction\TransactionStatusPayload;
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest;
use Sujip\Esewa\Exception\TransportException;

final class TransactionService
{
    public function __construct(
        private readonly GatewayConfig $config,
        private readonly EndpointResolver $endpoints,
        private readonly TransportInterface $transport,
        private readonly ClientOptions $options,
    ) {
    }

    public function fetchStatus(TransactionStatusRequest $query): TransactionStatus
    {
        $attempt = 0;

        while (true) {
            try {
                $payload = $this->transport->get(
                    $this->endpoints->statusCheckUrl($this->config),
                    [
                        'product_code'     => $query->productCode->value(),
                        'total_amount'     => $query->totalAmount->value(),
                        'transaction_uuid' => $query->transactionUuid->value(),
                    ]
                );

                $result = TransactionStatusPayload::fromArray($payload)->toResult();

                return $result;
            } catch (TransportException $exception) {
                if (!$this->options->retryPolicy->shouldRetry($attempt, $exception)) {
                    throw $exception;
                }

                $delayUs = $this->options->retryPolicy->delayUs($attempt, $exception);
                ++$attempt;

                if ($delayUs > 0) {
                    usleep($delayUs);
                }
            }
        }
    }
}
