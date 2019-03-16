<?php

namespace Omnipay\Esewa\Message;

use SimpleXMLElement;

/**
 * Class VerifyPaymentRequest.
 */
class VerifyPaymentRequest extends AbstractRequest
{
    /**
     * @var string
     */
    protected $verifyEndPoint = 'epay/transrec';

    /**
     * @return string
     */
    public function getData()
    {
        return [
            'amt' => $this->getAmount(),
            'rid' => $this->getReferenceNumber(),
            'pid' => $this->getProductCode(),
            'scd' => $this->getMerchantCode(),
        ];
    }

    /**
     * @param $data
     *
     * @return \Omnipay\Esewa\Message\OrderResponse
     */
    public function sendData($data)
    {
        $endPoint = $this->getEndpoint().'?'.http_build_query($data);

        $httpResponse = $this->httpClient->request('GET', $endPoint);

        $data = new SimpleXMLElement($httpResponse->getBody()->getContents());

        return $this->response = new VerifyPaymentResponse($this, $data);
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        $endPoint = $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;

        return $endPoint.$this->verifyEndPoint;
    }
}
