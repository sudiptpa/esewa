<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Verification;

final class VerificationExpectation
{
    public function __construct(
        public readonly string $totalAmount,
        public readonly string $transactionUuid,
        public readonly string $productCode,
        public readonly ?string $referenceId = null,
    ) {
    }
}
