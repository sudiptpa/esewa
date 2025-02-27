<?php

namespace Omnipay\Esewa\Message;

/**
 * Class CheckStatusRequest.
 */
class CheckStatusRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $statusEndPoint = 'api/epay/transaction/status/';

    /**
     * Prepare Data for API.
     *
     * @return array
     */
    public function getData()
    {
        $this->validate('totalAmount', 'productCode');

        return [
            'product_code'       => $this->getMerchantCode(),
            'total_amount'       => $this->getTotalAmount(),
            'transaction_uuid'   => $this->getProductCode(),
        ];
    }

    /**
     * @param $data
     *
     * @return \Omnipay\Esewa\Message\CheckStatusResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request(
            'GET',
            "{$this->getEndpoint()}?".http_build_query($data),
            []
        );

        return $this->response = new CheckStatusResponse(
            $this,
            $httpResponse->getBody()->getContents()
        );
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        $endPoint = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;

        return "{$endPoint}{$this->statusEndPoint}";
    }
}
