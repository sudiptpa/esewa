<?php

declare(strict_types=1);

namespace Omnipay\Esewa\Message;

use Sujip\Esewa\Client\CallbackService;
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\VerificationExpectation;
use Sujip\Esewa\Service\CallbackVerifier;
use Sujip\Esewa\Service\SignatureService;

final class CompletePurchaseRequest extends AbstractRequest
{
    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        $httpRequest = $this->httpRequest;
        if (isset($httpRequest->query) && is_object($httpRequest->query) && method_exists($httpRequest->query, 'all')) {
            /** @var array<string, mixed> $query */
            $query = $httpRequest->query->all();
            if ($query !== []) {
                return $query;
            }
        }

        if (isset($httpRequest->request) && is_object($httpRequest->request) && method_exists($httpRequest->request, 'all')) {
            /** @var array<string, mixed> $request */
            $request = $httpRequest->request->all();
            if ($request !== []) {
                return $request;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function sendData($data): CompletePurchaseResponse
    {
        $payload = CallbackPayload::fromArray($data);

        $service = new CallbackService(new CallbackVerifier(
            new SignatureService($this->getSecretKey()),
            new ClientOptions(preventCallbackReplay: false)
        ));

        $referenceId = $this->getReferenceNumber();
        $context = null;

        $transactionUuid = $this->getTransactionUuid();
        $productCode = $this->getProductCode() !== '' ? $this->getProductCode() : $this->getMerchantCode();
        $amount = $this->getAmount();
        $totalAmount = $this->getTotalAmount() !== '' ? $this->getTotalAmount() : ($amount ?? '');

        if ($transactionUuid !== '' && $productCode !== '' && $totalAmount !== '') {
            $context = new VerificationExpectation(
                totalAmount: $totalAmount,
                transactionUuid: $transactionUuid,
                productCode: $productCode,
                referenceId: $referenceId !== '' ? $referenceId : null,
            );
        }

        $result = $service->verifyCallback($payload, $context);

        return $this->response = new CompletePurchaseResponse($this, [
            'status' => $result->status->value,
            'valid' => $result->valid,
            'reference_id' => $result->referenceId?->value(),
            'message' => $result->message,
            'raw' => $result->raw,
        ]);
    }
}
