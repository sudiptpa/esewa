<?php

namespace Omnipay\Esewa;

trait GatewayTrait
{
    /**
     * @return string
     */
    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    /**
     * Generates the signature for the message.
     *
     * @param mixed $message
     *
     * @return string
     */
    public function generateSignature($message)
    {
        $signedMessage = hash_hmac('sha256', $message, $this->getSecretKey(), true);

        return base64_encode($signedMessage);
    }
}
