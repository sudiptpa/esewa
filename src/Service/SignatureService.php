<?php

declare(strict_types=1);

namespace EsewaPayment\Service;

final class SignatureService
{
    public function __construct(
        #[\SensitiveParameter]
        private readonly string $secretKey,
    ) {
    }

    public function generate(
        string $totalAmount,
        string $transactionUuid,
        string $productCode,
        string $signedFieldNames = 'total_amount,transaction_uuid,product_code'
    ): string {
        $fields = array_map('trim', explode(',', $signedFieldNames));

        $values = [
            'total_amount'     => $totalAmount,
            'transaction_uuid' => $transactionUuid,
            'product_code'     => $productCode,
        ];

        $parts = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $values)) {
                continue;
            }

            $parts[] = $field.'='.$values[$field];
        }

        $message = implode(',', $parts);

        return base64_encode(hash_hmac('sha256', $message, $this->secretKey, true));
    }

    public function verify(
        string $signature,
        string $totalAmount,
        string $transactionUuid,
        string $productCode,
        string $signedFieldNames = 'total_amount,transaction_uuid,product_code'
    ): bool {
        $generated = $this->generate($totalAmount, $transactionUuid, $productCode, $signedFieldNames);

        return hash_equals($generated, $signature);
    }
}
