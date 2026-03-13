<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Sujip\Esewa\Client\TransactionService;
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Config\EndpointResolver;
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest as CoreTransactionStatusRequest;

final class VerifyPaymentRequest extends AbstractRequest
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if ($this->getProductCode() === '') {
            $this->setProductCode($this->getMerchantCode());
        }

        $this->validate('merchantCode', 'amount', 'productCode');

        $transactionUuid = $this->getTransactionUuid();
        if ($transactionUuid === '') {
            $transactionUuid = $this->getTransactionId();
        }

        if ($transactionUuid === '') {
            throw new \InvalidArgumentException('transactionUuid or transactionId is required.');
        }

        return [
            'transaction_uuid' => $transactionUuid,
            'total_amount' => $this->getTotalAmount() !== '' ? $this->getTotalAmount() : (string) $this->getAmount(),
            'product_code' => $this->getProductCode(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendData($data): VerifyPaymentResponse
    {
        $service = new TransactionService(
            $this->gatewayConfig(),
            new EndpointResolver(),
            $this->transport(),
            new ClientOptions(),
        );

        $result = $service->fetchStatus(new CoreTransactionStatusRequest(
            transactionUuid: (string) $data['transaction_uuid'],
            totalAmount: (string) $data['total_amount'],
            productCode: (string) $data['product_code'],
        ));

        return $this->response = new VerifyPaymentResponse($this, [
            'status' => $result->status->value,
            'ref_id' => $result->referenceId?->value(),
        ]);
    }
}
