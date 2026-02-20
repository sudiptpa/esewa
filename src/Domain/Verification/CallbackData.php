<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Verification;

use EsewaPayment\Domain\Transaction\PaymentStatus;
use EsewaPayment\Exception\InvalidPayloadException;

final class CallbackData
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly string $totalAmount,
        public readonly string $transactionUuid,
        public readonly string $productCode,
        public readonly string $signedFieldNames,
        public readonly PaymentStatus $status,
        public readonly ?string $transactionCode,
        public readonly array $raw,
    ) {
    }

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): self
    {
        $totalAmount = (string) ($data['total_amount'] ?? '');
        $transactionUuid = (string) ($data['transaction_uuid'] ?? '');
        $productCode = (string) ($data['product_code'] ?? '');

        if ($totalAmount === '' || $transactionUuid === '' || $productCode === '') {
            throw new InvalidPayloadException('Callback data is missing required fields.');
        }

        return new self(
            totalAmount: $totalAmount,
            transactionUuid: $transactionUuid,
            productCode: $productCode,
            signedFieldNames: (string) ($data['signed_field_names'] ?? 'total_amount,transaction_uuid,product_code'),
            status: PaymentStatus::fromValue((string) ($data['status'] ?? null)),
            transactionCode: isset($data['transaction_code']) ? (string) $data['transaction_code'] : null,
            raw: $data,
        );
    }
}
