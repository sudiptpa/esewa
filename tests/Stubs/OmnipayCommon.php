<?php

declare(strict_types=1);

namespace Omnipay\Common\Message;

interface RequestInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array;

    public function send(): ResponseInterface;
}

interface ResponseInterface
{
    public function isSuccessful(): bool;

    public function getData(): mixed;
}

interface RedirectResponseInterface extends ResponseInterface
{
    public function isRedirect(): bool;

    public function getRedirectUrl(): string;

    public function getRedirectMethod(): string;

    /**
     * @return array<string, mixed>
     */
    public function getRedirectData(): array;
}

abstract class AbstractResponse implements ResponseInterface
{
    protected RequestInterface $request;

    /**
     * @var mixed
     */
    protected $data;

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getData(): mixed
    {
        return $this->data;
    }
}

abstract class AbstractRequest implements RequestInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $parameters = [];

    protected object $httpRequest;

    protected ?ResponseInterface $response = null;

    /**
     * @param mixed $httpRequest
     */
    public function __construct(mixed $httpClient = null, mixed $httpRequest = null)
    {
        unset($httpClient);

        $this->httpRequest = $httpRequest ?? new class {
            public object $query;
            public object $request;

            public function __construct()
            {
                $this->query = new class {
                    /**
                     * @return array<string, mixed>
                     */
                    public function all(): array
                    {
                        return [];
                    }
                };
                $this->request = new class {
                    /**
                     * @return array<string, mixed>
                     */
                    public function all(): array
                    {
                        return [];
                    }
                };
            }
        };
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function initialize(array $parameters = []): static
    {
        foreach ($parameters as $key => $value) {
            $this->setParameter((string) $key, $value);
        }

        return $this;
    }

    public function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? null;
    }

    public function setParameter(string $key, mixed $value): static
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function getAmount(): ?string
    {
        $amount = $this->getParameter('amount');

        return $amount === null ? null : (string) $amount;
    }

    public function getReturnUrl(): ?string
    {
        $returnUrl = $this->getParameter('returnUrl');

        return $returnUrl === null ? null : (string) $returnUrl;
    }

    public function setReturnUrl(string $value): static
    {
        return $this->setParameter('returnUrl', $value);
    }

    public function getTransactionId(): ?string
    {
        $transactionId = $this->getParameter('transactionId');

        return $transactionId === null ? null : (string) $transactionId;
    }

    public function getTestMode(): bool
    {
        return (bool) $this->getParameter('testMode');
    }

    public function send(): ResponseInterface
    {
        return $this->sendData($this->getData());
    }

    protected function validate(string ...$required): void
    {
        foreach ($required as $field) {
            $value = $this->getParameter($field);
            if ($value === null || $value === '') {
                throw new \InvalidArgumentException(sprintf('The %s parameter is required.', $field));
            }
        }
    }

    abstract public function sendData(mixed $data): ResponseInterface;
}

namespace Omnipay\Common;

abstract class AbstractGateway
{
    /**
     * @var array<string, mixed>
     */
    private array $parameters = [];

    /**
     * @return array<string, mixed>
     */
    abstract public function getDefaultParameters(): array;

    /**
     * @param class-string<\Omnipay\Common\Message\AbstractRequest> $class
     * @param array<string, mixed> $parameters
     */
    protected function createRequest(string $class, array $parameters = []): \Omnipay\Common\Message\AbstractRequest
    {
        $request = new $class();

        if (!$request instanceof \Omnipay\Common\Message\AbstractRequest) {
            throw new \InvalidArgumentException('Invalid Omnipay request class.');
        }

        $request->initialize(array_replace($this->getDefaultParameters(), $this->parameters, $parameters));

        return $request;
    }

    protected function getParameter(string $key): mixed
    {
        return $this->parameters[$key] ?? $this->getDefaultParameters()[$key] ?? null;
    }

    protected function setParameter(string $key, mixed $value): static
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function setTestMode(bool $value): static
    {
        return $this->setParameter('testMode', $value);
    }

    public function getTestMode(): bool
    {
        return (bool) $this->getParameter('testMode');
    }

    public function setReturnUrl(string $value): static
    {
        return $this->setParameter('returnUrl', $value);
    }
}
