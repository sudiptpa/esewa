<?php

declare(strict_types=1);

namespace Sujip\Esewa\Service;

use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\CallbackVerification;
use Sujip\Esewa\Domain\Verification\VerificationExpectation;
use Sujip\Esewa\Domain\Verification\VerificationState;
use Sujip\Esewa\Exception\FraudValidationException;

final class CallbackVerifier
{
    public function __construct(
        private readonly SignatureService $signatures,
        private readonly ClientOptions $options,
    )
    {
    }

    public function verify(CallbackPayload $payload, ?VerificationExpectation $context = null): CallbackVerification
    {
        $data = $payload->decodedData();

        $validSignature = $this->signatures->verify(
            $payload->signature,
            $data->totalAmount->value(),
            $data->transactionUuid->value(),
            $data->productCode->value(),
            $data->signedFieldNames
        );

        if (!$validSignature) {
            return new CallbackVerification(
                VerificationState::INVALID_SIGNATURE,
                false,
                $data->status,
                $data->transactionCode,
                'Invalid callback signature.',
                $data->raw
            );
        }

        if ($context !== null) {
            $this->assertConsistent(
                $context,
                $data->totalAmount->value(),
                $data->transactionUuid->value(),
                $data->productCode->value(),
                $data->transactionCode?->value()
            );
        }

        if ($this->options->preventCallbackReplay) {
            $idempotencyKey = $this->resolveIdempotencyKey($payload, $data->transactionCode);

            if ($this->options->idempotencyStore->has($idempotencyKey)) {
                return new CallbackVerification(
                    VerificationState::REPLAYED,
                    false,
                    $data->status,
                    $data->transactionCode,
                    'Duplicate callback detected.',
                    $data->raw
                );
            }

            $this->options->idempotencyStore->put($idempotencyKey);
        }

        return new CallbackVerification(
            VerificationState::VERIFIED,
            true,
            $data->status,
            $data->transactionCode,
            'Callback verified.',
            $data->raw
        );
    }

    private function assertConsistent(
        VerificationExpectation $context,
        string $totalAmount,
        string $transactionUuid,
        string $productCode,
        ?string $referenceId
    ): void {
        if ($context->totalAmount->value() !== $totalAmount) {
            throw new FraudValidationException('total_amount mismatch during callback verification.');
        }

        if ($context->transactionUuid->value() !== $transactionUuid) {
            throw new FraudValidationException('transaction_uuid mismatch during callback verification.');
        }

        if ($context->productCode->value() !== $productCode) {
            throw new FraudValidationException('product_code mismatch during callback verification.');
        }

        if ($context->referenceId !== null && $context->referenceId->value() !== $referenceId) {
            throw new FraudValidationException('reference_id mismatch during callback verification.');
        }
    }

    private function resolveIdempotencyKey(CallbackPayload $payload, ?\Sujip\Esewa\ValueObject\ReferenceId $referenceId): string
    {
        if ($referenceId !== null) {
            return 'ref:'.$referenceId->value();
        }

        return 'digest:'.hash('sha256', $payload->data.'|'.$payload->signature);
    }
}
