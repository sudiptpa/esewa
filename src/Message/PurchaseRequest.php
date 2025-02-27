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
    protected $purchaseEndPoint = 'api/epay/main/v2/form';

    /**
     * Prepare Data for API.
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('merchantCode', 'amount', 'totalAmount', 'productCode', 'failedUrl', 'returnUrl');

        return [
            'amount'                    => $this->getAmount(),
            'tax_amount'                => $this->getTaxAmount() ?: 0,
            'total_amount'              => $this->getTotalAmount(),
            'product_delivery_charge'   => $this->getDeliveryCharge() ?: 0,
            'product_service_charge'    => $this->getServiceCharge() ?: 0,
            'transaction_uuid'          => $this->getProductCode(),
            'product_code'              => $this->getMerchantCode(),
            'success_url'               => $this->getReturnUrl(),
            'failure_url'               => $this->getFailedUrl(),
            'signed_field_names'        => $this->getSignedFieldsName(),
            'signature'                 => $this->getSignature(),
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

        return "{$endPoint}{$this->purchaseEndPoint}";
    }
}
