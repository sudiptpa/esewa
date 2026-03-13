<?php

declare(strict_types=1);

namespace Sujip\Esewa\Domain\Verification;

use Sujip\Esewa\Contracts\Arrayable;
use Sujip\Esewa\Contracts\Hydratable;
use Sujip\Esewa\Domain\Transaction\PaymentStatus;
use Sujip\Esewa\Exception\InvalidPayloadException;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\ReferenceId;
use Sujip\Esewa\ValueObject\TransactionUuid;

final readonly class CallbackData implements Arrayable, Hydratable
{
    /**
     * @param array<string,mixed> $raw
     */
    public function __construct(
        public readonly Amount $totalAmount,
        public readonly TransactionUuid $transactionUuid,
        public readonly ProductCode $productCode,
        public readonly string $signedFieldNames,
        public readonly PaymentStatus $status,
        public readonly ?ReferenceId $transactionCode,
        public readonly array $raw,
    ) {
    }

    /** @param array<string,mixed> $data */
    public static function fromArray(array $data): static
    {
        $totalAmount = (string) ($data['total_amount'] ?? '');
        $transactionUuid = (string) ($data['transaction_uuid'] ?? '');
        $productCode = (string) ($data['product_code'] ?? '');

        if ($totalAmount === '' || $transactionUuid === '' || $productCode === '') {
            throw new InvalidPayloadException('Callback data is missing required fields.');
        }

        return new static(
            totalAmount: Amount::fromString($totalAmount),
            transactionUuid: TransactionUuid::fromString($transactionUuid),
            productCode: ProductCode::fromString($productCode),
            signedFieldNames: (string) ($data['signed_field_names'] ?? 'total_amount,transaction_uuid,product_code'),
            status: PaymentStatus::fromValue((string) ($data['status'] ?? null)),
            transactionCode: isset($data['transaction_code']) && (string) $data['transaction_code'] !== ''
                ? ReferenceId::fromString((string) $data['transaction_code'])
                : null,
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'total_amount' => $this->totalAmount->value(),
            'transaction_uuid' => $this->transactionUuid->value(),
            'product_code' => $this->productCode->value(),
            'signed_field_names' => $this->signedFieldNames,
            'status' => $this->status->value,
            'transaction_code' => $this->transactionCode?->value(),
        ];
    }
}
