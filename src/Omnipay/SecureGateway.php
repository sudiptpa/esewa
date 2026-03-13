<?php

declare(strict_types=1);

namespace Omnipay\Esewa;

use Omnipay\Common\AbstractGateway;

final class SecureGateway extends AbstractGateway
{
    public function getName(): string
    {
        return 'eSewa';
    }

    public function getDefaultParameters(): array
    {
        return [
            'merchantCode' => '',
            'secretKey' => '',
            'productCode' => '',
            'taxAmount' => '0',
            'serviceCharge' => '0',
            'deliveryCharge' => '0',
            'testMode' => false,
            'transactionUuid' => '',
            'failureUrl' => '',
            'timeoutSeconds' => 30,
            'transport' => null,
        ];
    }

    public function getMerchantCode(): string
    {
        return (string) $this->getParameter('merchantCode');
    }

    public function setMerchantCode(string $value): self
    {
        return $this->setParameter('merchantCode', $value);
    }

    public function getSecretKey(): string
    {
        return (string) $this->getParameter('secretKey');
    }

    public function setSecretKey(string $value): self
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTaxAmount(): string
    {
        return (string) $this->getParameter('taxAmount');
    }

    public function setTaxAmount(string $value): self
    {
        return $this->setParameter('taxAmount', $value);
    }

    public function getServiceCharge(): string
    {
        return (string) $this->getParameter('serviceCharge');
    }

    public function setServiceCharge(string $value): self
    {
        return $this->setParameter('serviceCharge', $value);
    }

    public function getDeliveryCharge(): string
    {
        return (string) $this->getParameter('deliveryCharge');
    }

    public function setDeliveryCharge(string $value): self
    {
        return $this->setParameter('deliveryCharge', $value);
    }

    public function getTransactionUuid(): string
    {
        return (string) $this->getParameter('transactionUuid');
    }

    public function setTransactionUuid(string $value): self
    {
        return $this->setParameter('transactionUuid', $value);
    }

    public function getProductCode(): string
    {
        return (string) $this->getParameter('productCode');
    }

    public function setProductCode(string $value): self
    {
        return $this->setParameter('productCode', $value);
    }

    public function getFailureUrl(): string
    {
        return (string) $this->getParameter('failureUrl');
    }

    public function getFailedUrl(): string
    {
        return $this->getFailureUrl();
    }

    public function setFailureUrl(string $value): self
    {
        return $this->setParameter('failureUrl', $value);
    }

    public function setFailedUrl(string $value): self
    {
        return $this->setFailureUrl($value);
    }

    public function getReferenceNumber(): string
    {
        return (string) $this->getParameter('referenceNumber');
    }

    public function setReferenceNumber(string $value): self
    {
        return $this->setParameter('referenceNumber', $value);
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function completePurchase(array $parameters = []): \Omnipay\Esewa\Message\CompletePurchaseRequest
    {
        $request = $this->createRequest(\Omnipay\Esewa\Message\CompletePurchaseRequest::class, $parameters);

        \assert($request instanceof \Omnipay\Esewa\Message\CompletePurchaseRequest);

        return $request;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function purchase(array $parameters = []): \Omnipay\Esewa\Message\PurchaseRequest
    {
        $request = $this->createRequest(\Omnipay\Esewa\Message\PurchaseRequest::class, $parameters);

        \assert($request instanceof \Omnipay\Esewa\Message\PurchaseRequest);

        return $request;
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function verifyPayment(array $parameters = []): \Omnipay\Esewa\Message\VerifyPaymentRequest
    {
        $request = $this->createRequest(\Omnipay\Esewa\Message\VerifyPaymentRequest::class, $parameters);

        \assert($request instanceof \Omnipay\Esewa\Message\VerifyPaymentRequest);

        return $request;
    }
}
