<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Config\EndpointResolver;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\TransportInterface;
use EsewaPayment\Domain\Transaction\TransactionStatus;
use EsewaPayment\Domain\Transaction\TransactionStatusPayload;
use EsewaPayment\Domain\Transaction\TransactionStatusRequest;
use EsewaPayment\Exception\TransportException;

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
        $this->options->logger->info('eSewa status check started.', [
            'event' => 'esewa.status.started',
            'transaction_uuid' => $query->transactionUuid,
        ]);

        $attempt = 0;

        while (true) {
            try {
                $payload = $this->transport->get(
                    $this->endpoints->statusCheckUrl($this->config),
                    [
                        'product_code'     => $query->productCode,
                        'total_amount'     => $query->totalAmount,
                        'transaction_uuid' => $query->transactionUuid,
                    ]
                );

                $result = TransactionStatusPayload::fromArray($payload)->toResult();

                $this->options->logger->info('eSewa status check completed.', [
                    'event' => 'esewa.status.completed',
                    'transaction_uuid' => $query->transactionUuid,
                    'status' => $result->status->value,
                ]);

                return $result;
            } catch (TransportException $exception) {
                if ($attempt >= $this->options->maxStatusRetries) {
                    $this->options->logger->error('eSewa status check failed after retries.', [
                        'event' => 'esewa.status.failed',
                        'transaction_uuid' => $query->transactionUuid,
                        'attempt' => $attempt,
                        'error' => $exception->getMessage(),
                    ]);

                    throw $exception;
                }

                ++$attempt;

                $this->options->logger->warning('eSewa status check retry scheduled.', [
                    'event' => 'esewa.status.retry',
                    'transaction_uuid' => $query->transactionUuid,
                    'attempt' => $attempt,
                    'max_retries' => $this->options->maxStatusRetries,
                    'error' => $exception->getMessage(),
                ]);

                if ($this->options->statusRetryDelayMs > 0) {
                    usleep($this->options->statusRetryDelayMs * 1000);
                }
            }
        }
    }
}
