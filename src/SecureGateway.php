<?php

namespace Omnipay\Esewa;

use Omnipay\Common\AbstractGateway;

/**
 * Class SecureGateway.
 */
class SecureGateway extends AbstractGateway
{
    use GatewayTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return 'eSewa';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return [
            'merchantCode' => '',
            'testMode'     => false,
            'secretKey'    => '',
        ];
    }

    /**
     * @return string
     */
    public function getMerchantCode()
    {
        return $this->getParameter('merchantCode');
    }

    /**
     * @param $value
     */
    public function setMerchantCode($value)
    {
        return $this->setParameter('merchantCode', $value);
    }

    /**
     * @param $value
     */
    public function setTaxAmount($value)
    {
        return $this->setParameter('taxAmount', $value);
    }

    /**
     * @return string
     */
    public function getTaxAmount()
    {
        return $this->getParameter('taxAmount');
    }

    /**
     * @param $value
     */
    public function setServiceCharge($value)
    {
        return $this->setParameter('serviceCharge', $value);
    }

    /**
     * @return string
     */
    public function getServiceCharge()
    {
        return $this->getParameter('serviceCharge');
    }

    /**
     * @param $value
     */
    public function setDeliveryCharge($value)
    {
        return $this->setParameter('deliveryCharge', $value);
    }

    /**
     * @return string
     */
    public function getDeliveryCharge()
    {
        return $this->getParameter('deliveryCharge');
    }

    /**
     * @param $value
     */
    public function setTotalAmount($value)
    {
        return $this->setParameter('totalAmount', $value);
    }

    /**
     * @return string
     */
    public function getTotalAmount()
    {
        return $this->getParameter('totalAmount');
    }

    /**
     * @param $value
     */
    public function setProductCode($value)
    {
        return $this->setParameter('productCode', $value);
    }

    /**
     * @return string
     */
    public function getProductCode()
    {
        return $this->getParameter('productCode');
    }

    /**
     * @return string
     */
    public function getFailedUrl()
    {
        return $this->getParameter('failedUrl');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFailedUrl($value)
    {
        return $this->setParameter('failedUrl', $value);
    }

    /**
     * @return string
     */
    public function getReferenceNumber()
    {
        return $this->getParameter('referenceNumber');
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setReferenceNumber($value)
    {
        return $this->setParameter('referenceNumber', $value);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Esewa\Message\PurchaseRequest
     */
    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Esewa\Message\PurchaseRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Esewa\Message\VerifyPaymentRequest
     */
    public function verifyPayment(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Esewa\Message\VerifyPaymentRequest', $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return \Omnipay\Esewa\Message\CheckStatusRequest
     */
    public function checkStatus(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Esewa\Message\CheckStatusRequest', $parameters);
    }
}
