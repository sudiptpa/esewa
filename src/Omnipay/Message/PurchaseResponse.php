<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;
use Omnipay\Common\Message\RequestInterface;

final class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(RequestInterface $request, array $data, private readonly string $redirectUrl)
    {
        $this->request = $request;
        $this->data = $data;
    }

    public function isSuccessful(): bool
    {
        return false;
    }

    public function isRedirect(): bool
    {
        return true;
    }

    public function getRedirectUrl(): string
    {
        return $this->redirectUrl;
    }

    public function getRedirectMethod(): string
    {
        return 'POST';
    }

    /**
     * @return array<string, mixed>
     */
    public function getRedirectData(): array
    {
        return $this->getData();
    }
}
