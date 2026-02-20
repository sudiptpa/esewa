<?php

declare(strict_types=1);

namespace EsewaPayment\Service;

use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Domain\Verification\ReturnPayload;
use EsewaPayment\Domain\Verification\VerificationContext;
use EsewaPayment\Domain\Verification\VerificationResult;
use EsewaPayment\Exception\FraudValidationException;

final class CallbackVerifier
{
    public function __construct(private readonly SignatureService $signatures)
    {
    }

    public function verify(ReturnPayload $payload, ?VerificationContext $context = null): VerificationResult
    {
        $data = $payload->decodedData();

        $totalAmount = (string) ($data['total_amount'] ?? '');
        $transactionUuid = (string) ($data['transaction_uuid'] ?? '');
        $productCode = (string) ($data['product_code'] ?? '');
        $signedFieldNames = (string) ($data['signed_field_names'] ?? 'total_amount,transaction_uuid,product_code');
        $status = PaymentStatus::fromValue((string) ($data['status'] ?? null));
        $referenceId = isset($data['transaction_code']) ? (string) $data['transaction_code'] : null;

        $validSignature = $this->signatures->verify(
            $payload->signature,
            $totalAmount,
            $transactionUuid,
            $productCode,
            $signedFieldNames
        );

        if (!$validSignature) {
            return new VerificationResult(false, $status, $referenceId, 'Invalid callback signature.', $data);
        }

        if ($context !== null) {
            $this->assertConsistent($context, $totalAmount, $transactionUuid, $productCode, $referenceId);
        }

        return new VerificationResult(true, $status, $referenceId, 'Callback verified.', $data);
    }

    private function assertConsistent(
        VerificationContext $context,
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
