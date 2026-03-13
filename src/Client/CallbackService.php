<?php

declare(strict_types=1);

namespace Sujip\Esewa\Client;

use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\CallbackVerification;
use Sujip\Esewa\Domain\Verification\VerificationExpectation;
use Sujip\Esewa\Service\CallbackVerifier;

final class CallbackService
{
    public function __construct(private readonly CallbackVerifier $verifier)
    {
    }

    public function verifyCallback(CallbackPayload $payload, ?VerificationExpectation $context = null): CallbackVerification
    {
        return $this->verifier->verify($payload, $context);
    }
}
