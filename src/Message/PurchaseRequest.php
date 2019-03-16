<?php

namespace Omnipay\Esewa\Message;

/**
 * Class PurchaseRequest.
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $purchaseEndPoint = 'epay/main';

    /**
     * Prepare Data for API.
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('merchantCode', 'amount', 'totalAmount', 'productCode', 'failedUrl', 'returnUrl');

        return [
            'amt'   => $this->getAmount(),
            'pdc'   => $this->getDeliveryCharge() ?: 0,
            'psc'   => $this->getServiceCharge() ?: 0,
            'txAmt' => $this->getTaxAmount() ?: 0,
            'tAmt'  => $this->getTotalAmount(),
            'pid'   => $this->getProductCode(),
            'scd'   => $this->getMerchantCode(),
            'su'    => $this->getReturnUrl(),
            'fu'    => $this->getFailedUrl(),
        ];
    }

    /**
     * @param $data
     *
     * @return \Omnipay\Esewa\Message\PurchaseResponse
     */
    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data, $this->getEndpoint());
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        $endPoint = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;

        return $endPoint.$this->purchaseEndPoint;
    }
}
