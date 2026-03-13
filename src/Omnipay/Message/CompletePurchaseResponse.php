<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

final class CompletePurchaseResponse extends AbstractResponse
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(RequestInterface $request, array $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function isSuccessful(): bool
    {
        return (bool) ($this->data['valid'] ?? false) && (string) ($this->data['status'] ?? '') === 'COMPLETE';
    }

    public function getMessage(): string
    {
        return (string) ($this->data['message'] ?? '');
    }

    public function getTransactionReference(): ?string
    {
        $referenceId = $this->data['reference_id'] ?? null;

        return is_string($referenceId) && $referenceId !== '' ? $referenceId : null;
    }
}
