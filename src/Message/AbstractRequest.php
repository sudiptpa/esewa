<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Esewa\GatewayTrait;

/**
 * Class AbstractRequest.
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    use GatewayTrait;

    /**
     * @var string
     */
    protected $liveEndpoint = 'https://epay.esewa.com.np/';

    /**
     * @var string
     */
    protected $testEndpoint = 'https://rc.esewa.com.np/';

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
     * @param $value
     */
    public function setSignedFieldsName($value)
    {
        return $this->setParameter('signedFields', $value);
    }

    /**
     * @return string
     */
    public function getSignedFieldsName()
    {
        return $this->getParameter('signedFields') ?: 'total_amount,transaction_uuid,product_code';
    }

    /**
     * @param $value
     */
    public function setSignature($value)
    {
        return $this->setParameter('signature', $this->generateSignature($value));
    }

    /**
     * @return string
     */
    public function getSignature()
    {
        if ($signature = $this->getParameter('signature')) {
            return $signature;
        }

        $value = http_build_query([
            'total_amount'     => $this->getTotalAmount(),
            'transaction_uuid' => $this->getProductCode(),
            'product_code'     => $this->getMerchantCode(),
        ], '', ',');

        return $this->generateSignature($value);
    }

    /**
     * @param $value
     */
    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }
}
