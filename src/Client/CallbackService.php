<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Domain\Verification\CallbackVerification;
use EsewaPayment\Domain\Verification\VerificationExpectation;
use EsewaPayment\Service\CallbackVerifier;

final class CallbackService
{
    public function __construct(private readonly CallbackVerifier $verifier)
    {
    }

    public function verifyCallback(CallbackPayload $payload, ?VerificationExpectation $context = null): CallbackVerification
    {
        return $this->verify($payload, $context);
    }

    public function verify(CallbackPayload $payload, ?VerificationExpectation $context = null): CallbackVerification
    {
        return $this->verifier->verify($payload, $context);
    }
}
