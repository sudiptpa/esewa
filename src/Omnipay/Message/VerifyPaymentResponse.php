<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;

final class VerifyPaymentResponse extends AbstractResponse
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(RequestInterface $request, array $data)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function getResponseText(): string
    {
        return (string) ($this->data['status'] ?? '');
    }

    public function getReferenceId(): ?string
    {
        $referenceId = $this->data['ref_id'] ?? null;

        return is_string($referenceId) && $referenceId !== '' ? $referenceId : null;
    }

    public function isSuccessful(): bool
    {
        return $this->getResponseText() === 'COMPLETE';
    }
}
