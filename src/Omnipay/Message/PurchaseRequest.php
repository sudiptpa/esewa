<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Sujip\Esewa\Client\CheckoutService;
use Sujip\Esewa\Config\EndpointResolver;
use Sujip\Esewa\Domain\Checkout\CheckoutRequest as CoreCheckoutRequest;
use Sujip\Esewa\Service\SignatureService;

final class PurchaseRequest extends AbstractRequest
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        if ($this->getProductCode() === '') {
            $this->setProductCode($this->getMerchantCode());
        }

        $this->validate('merchantCode', 'secretKey', 'amount', 'productCode', 'returnUrl', 'failureUrl');

        $transactionUuid = $this->getTransactionUuid();
        if ($transactionUuid === '') {
            $transactionUuid = $this->getTransactionId();
        }

        if ($transactionUuid === '') {
            throw new \InvalidArgumentException('transactionUuid or transactionId is required.');
        }

        return [
            'transaction_uuid' => $transactionUuid,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendData($data): PurchaseResponse
    {
        $service = new CheckoutService(
            $this->gatewayConfig(),
            new EndpointResolver(),
            new SignatureService($this->getSecretKey()),
        );

        $intent = $service->createIntent(new CoreCheckoutRequest(
            amount: (string) $this->getAmount(),
            taxAmount: $this->getTaxAmount() !== '' ? $this->getTaxAmount() : '0',
            serviceCharge: $this->getServiceCharge() !== '' ? $this->getServiceCharge() : '0',
            deliveryCharge: $this->getDeliveryCharge() !== '' ? $this->getDeliveryCharge() : '0',
            transactionUuid: (string) $data['transaction_uuid'],
            productCode: $this->getProductCode(),
            successUrl: (string) $this->getReturnUrl(),
            failureUrl: $this->getFailureUrl(),
        ));

        return $this->response = new PurchaseResponse($this, $intent->fields(), $intent->actionUrl);
    }
}
