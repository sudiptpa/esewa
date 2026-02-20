<?php

declare(strict_types=1);

namespace EsewaPayment\Domain\Transaction;

final class TransactionStatusRequest
{
    public function __construct(
        public readonly string $transactionUuid,
        public readonly string $totalAmount,
        public readonly string $productCode,
    ) {
        if ($transactionUuid === '' || $totalAmount === '' || $productCode === '') {
            throw new \InvalidArgumentException('transactionUuid, totalAmount and productCode are required.');
        }
    }
}
