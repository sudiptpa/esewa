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
     * @var string
     */
    protected $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36';

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
     * @return string
     */
    public function getUserAgent()
    {
        $userAgent = $this->userAgent;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        return $userAgent;
    }

    /**
     * @param $data
     *
     * @return \Omnipay\Esewa\Message\VerifyPaymentResponse
     */
    public function sendData($data)
    {
        $endPoint = $this->getEndpoint();

        $headers = [
            'User-Agent'   => $this->getUserAgent(),
            'Accept'       => 'application/xml',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ];

        $httpResponse = $this->httpClient->request('POST', $endPoint, $headers, http_build_query($data));

        $content = new SimpleXMLElement($httpResponse->getBody()->getContents());

        return $this->response = new VerifyPaymentResponse($this, $content);
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
