<?php

declare(strict_types=1);

namespace EsewaPayment\Client;

use EsewaPayment\Domain\Verification\ReturnPayload;
use EsewaPayment\Domain\Verification\VerificationContext;
use EsewaPayment\Domain\Verification\VerificationResult;
use EsewaPayment\Service\CallbackVerifier;

final class CallbackService
{
    public function __construct(private readonly CallbackVerifier $verifier) {}

    public function verify(ReturnPayload $payload, ?VerificationContext $context = null): VerificationResult
    {
        return $this->verifier->verify($payload, $context);
    }
}
