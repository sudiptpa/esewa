<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Sujip\Esewa\Config\Environment;
use Sujip\Esewa\Config\GatewayConfig;
use Sujip\Esewa\Contracts\TransportInterface;
use Sujip\Esewa\Infrastructure\Transport\CurlTransport;

abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
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

    public function getTotalAmount(): string
    {
        return (string) $this->getParameter('totalAmount');
    }

    public function setTotalAmount(string $value): self
    {
        return $this->setParameter('totalAmount', $value);
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

    public function getTransactionUuid(): string
    {
        return (string) $this->getParameter('transactionUuid');
    }

    public function setTransactionUuid(string $value): self
    {
        return $this->setParameter('transactionUuid', $value);
    }

    public function setTransport(TransportInterface $transport): self
    {
        return $this->setParameter('transport', $transport);
    }

    public function getTransport(): ?TransportInterface
    {
        $transport = $this->getParameter('transport');

        return $transport instanceof TransportInterface ? $transport : null;
    }

    public function getTimeoutSeconds(): int
    {
        $timeout = $this->getParameter('timeoutSeconds');

        if ($timeout === null) {
            return 30;
        }

        return max(1, (int) $timeout);
    }

    public function setTimeoutSeconds(int $value): self
    {
        return $this->setParameter('timeoutSeconds', max(1, $value));
    }

    protected function gatewayConfig(): GatewayConfig
    {
        $productCode = $this->getProductCode();
        if ($productCode === '') {
            $productCode = $this->getMerchantCode();
            $this->setProductCode($productCode);
        }

        return GatewayConfig::make(
            merchantCode: $this->getMerchantCode(),
            secretKey: $this->getSecretKey(),
            environment: $this->getTestMode() ? Environment::UAT : Environment::PRODUCTION,
        );
    }

    protected function transport(): TransportInterface
    {
        return $this->getTransport() ?? new CurlTransport($this->getTimeoutSeconds());
    }
}
