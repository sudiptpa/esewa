<?php

declare(strict_types=1);

namespace EsewaPayment\Service;

use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Domain\Verification\CallbackVerification;
use EsewaPayment\Domain\Verification\VerificationExpectation;
use EsewaPayment\Exception\FraudValidationException;

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
            $data->totalAmount,
            $data->transactionUuid,
            $data->productCode,
            $data->signedFieldNames
        );

        if (!$validSignature) {
            $this->options->logger->warning('eSewa callback rejected due to invalid signature.', [
                'event' => 'esewa.callback.invalid_signature',
                'transaction_uuid' => $data->transactionUuid,
            ]);

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

        if ($this->options->preventCallbackReplay) {
            $idempotencyKey = $this->resolveIdempotencyKey($payload, $data->transactionCode);

            if ($this->options->idempotencyStore->has($idempotencyKey)) {
                $this->options->logger->warning('eSewa callback replay detected.', [
                    'event' => 'esewa.callback.replay_detected',
                    'transaction_uuid' => $data->transactionUuid,
                    'reference_id' => $data->transactionCode,
                ]);

                return new CallbackVerification(
                    false,
                    $data->status,
                    $data->transactionCode,
                    'Duplicate callback detected.',
                    $data->raw
                );
            }

            $this->options->idempotencyStore->put($idempotencyKey);
        }

        $this->options->logger->info('eSewa callback verified.', [
            'event' => 'esewa.callback.verified',
            'transaction_uuid' => $data->transactionUuid,
            'status' => $data->status->value,
            'reference_id' => $data->transactionCode,
        ]);

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
            $this->options->logger->warning('eSewa callback fraud validation failed.', [
                'event' => 'esewa.callback.fraud_reference_mismatch',
                'expected_reference_id' => $context->referenceId,
                'actual_reference_id' => $referenceId,
            ]);

            throw new FraudValidationException('reference_id mismatch during callback verification.');
        }
    }

    private function resolveIdempotencyKey(CallbackPayload $payload, ?string $referenceId): string
    {
        if ($referenceId !== null && $referenceId !== '') {
            return 'ref:'.$referenceId;
        }

        return 'digest:'.hash('sha256', $payload->data.'|'.$payload->signature);
    }
}
