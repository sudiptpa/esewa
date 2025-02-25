<?php

namespace Omnipay\Esewa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

/**
 * Class CheckStatusResponse.
 */
class CheckStatusResponse extends AbstractResponse
{
    /**
     * @param RequestInterface $request
     * @param $data
     */
    public function __construct(RequestInterface $request, $data)
    {
        $this->request = $request;
        $this->data = json_decode($data);
    }

    /**
     * @return string
     */
    public function getResponseText()
    {
        return (string) trim($this->data->status);
    }

    /**
     * @return bool
     */
    public function isSuccessful()
    {
        $string = strtolower($this->getResponseText());

        return in_array($string, ['complete']);
    }
}
