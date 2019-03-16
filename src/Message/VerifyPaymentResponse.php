<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Class VerifyPaymentResponse.
 */
class VerifyPaymentResponse extends AbstractResponse
{
    /**
     * @param RequestInterface $request
     * @param $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getResponseText()
    {
        return (string) $data->response_code;
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        return in_array($this->getResponseText(), ['Success']);
    }
}
