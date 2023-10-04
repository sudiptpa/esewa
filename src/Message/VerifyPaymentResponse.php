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
     * @param                  $data
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
        return (string) trim($this->data->response_code);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        $string = strtolower($this->getResponseText());

        return in_array($string, ['success']);
    }
}
