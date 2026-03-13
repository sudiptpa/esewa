<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Verification;

enum VerificationState: string
{
    case VERIFIED = 'verified';
    case INVALID_SIGNATURE = 'invalid_signature';
    case REPLAYED = 'replayed';
}
