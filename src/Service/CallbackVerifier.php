<?php

declare(strict_types=1);

namespace EsewaPayment\Service;

use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Domain\Verification\VerificationExpectation;
use EsewaPayment\Domain\Verification\CallbackVerification;
use EsewaPayment\Exception\FraudValidationException;

final class CallbackVerifier
{
    public function __construct(private readonly SignatureService $signatures)
    {
    }

    public function verify(CallbackPayload $payload, ?VerificationExpectation $context = null): CallbackVerification
    {
        $data = $payload->decodedData();

        $validSignature = $this->signatures->verify(
            $payload->signature,
            $data->totalAmount,
            $data->transactionUuid,
            $data->productCode,
            $data->signedFieldNames
        );

        if (!$validSignature) {
            return new CallbackVerification(
                false,
                $data->status,
                $data->transactionCode,
                'Invalid callback signature.',
                $data->raw
            );
        }

        if ($context !== null) {
            $this->assertConsistent($context, $data->totalAmount, $data->transactionUuid, $data->productCode, $data->transactionCode);
        }

        return new CallbackVerification(true, $data->status, $data->transactionCode, 'Callback verified.', $data->raw);
    }

    private function assertConsistent(
        VerificationExpectation $context,
        string $totalAmount,
        string $transactionUuid,
        string $productCode,
        ?string $referenceId
    ): void {
        if ($context->totalAmount !== $totalAmount) {
            throw new FraudValidationException('total_amount mismatch during callback verification.');
        }

        if ($context->transactionUuid !== $transactionUuid) {
            throw new FraudValidationException('transaction_uuid mismatch during callback verification.');
        }

        if ($context->productCode !== $productCode) {
            throw new FraudValidationException('product_code mismatch during callback verification.');
        }

        if ($context->referenceId !== null && $context->referenceId !== $referenceId) {
            throw new FraudValidationException('reference_id mismatch during callback verification.');
        }
    }
}
