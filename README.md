# eSewa PHP SDK

Framework-agnostic, zero-dependency PHP SDK for eSewa ePay v2, with an optional Omnipay v3 bridge.

[![CI](https://github.com/sudiptpa/esewa-sdk-php/actions/workflows/ci.yml/badge.svg)](https://github.com/sudiptpa/esewa-sdk-php/actions/workflows/ci.yml)
[![Latest Release](https://img.shields.io/github/v/release/sudiptpa/esewa-sdk-php?sort=semver)](https://github.com/sudiptpa/esewa-sdk-php/releases)
[![GitHub Downloads](https://img.shields.io/github/downloads/sudiptpa/esewa-sdk-php/total)](https://github.com/sudiptpa/esewa-sdk-php/releases)
[![PHP Version](https://img.shields.io/badge/php-8.2--8.5-777bb4.svg)](https://www.php.net/)
[![Packagist](https://img.shields.io/badge/packagist-sudiptpa%2Fomnipay--esewa-f28d1a.svg)](https://packagist.org/packages/sudiptpa/omnipay-esewa)
[![License](https://img.shields.io/github/license/sudiptpa/esewa-sdk-php)](LICENSE)

## Public API

This package exposes two public namespaces:

- `Sujip\\Esewa`
- `Omnipay\\Esewa`

## Highlights

- checkout, callback, and transaction flows built around request and result models
- typed value objects for amount, transaction UUID, product code, and reference ID
- `toArray()` and `fromArray()` where they are useful
- callback verification with explicit states: `verified`, `invalid_signature`, `replayed`
- zero-dependency core with built-in `CurlTransport`
- configurable retry policy and clock handling
- replay protection backed by filesystem or PDO storage
- optional PSR-18 transport support
- optional Omnipay v3 bridge
- PHP `8.2` to `8.5`

## Installation

```bash
composer require sudiptpa/omnipay-esewa
```

Optional PSR-18 usage:

```bash
composer require symfony/http-client nyholm/psr7
```

## Documentation

- [User Guide](docs/user-guide.md)
- [Migration Guide](docs/migration-guide.md)
- [Architecture](ARCHITECTURE.md)

## Quick Start

```php
<?php

declare(strict_types=1);

use Sujip\Esewa\Esewa;
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    environment: 'uat',
);

$intent = $client->checkout()->createIntent(new CheckoutRequest(
    amount: '100',
    taxAmount: '0',
    serviceCharge: '0',
    deliveryCharge: '0',
    transactionUuid: 'TXN-1001',
    productCode: 'EPAYTEST',
    successUrl: 'https://merchant.example.com/esewa/success',
    failureUrl: 'https://merchant.example.com/esewa/failure',
));
```

The request constructor accepts strings for convenience. Internally, those values are normalized into value objects. If you want stricter typing in your own code, build the value objects directly:

```php
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;
use Sujip\Esewa\ValueObject\Amount;
use Sujip\Esewa\ValueObject\ProductCode;
use Sujip\Esewa\ValueObject\TransactionUuid;

$request = new CheckoutRequest(
    amount: Amount::fromString('100'),
    taxAmount: Amount::fromString('0'),
    serviceCharge: Amount::fromString('0'),
    deliveryCharge: Amount::fromString('0'),
    transactionUuid: TransactionUuid::fromString('TXN-1001'),
    productCode: ProductCode::fromString('EPAYTEST'),
    successUrl: 'https://merchant.example.com/esewa/success',
    failureUrl: 'https://merchant.example.com/esewa/failure',
);
```

## Working With Models

The core models can be converted to arrays. That is mainly useful when you are crossing controller boundaries, queueing work, or saving fixtures for tests.

```php
$payload = $request->toArray();
$restored = CheckoutRequest::fromArray($payload);
```

The client stays small. Most integrations only need three modules:

- `$client->checkout()`
- `$client->callbacks()`
- `$client->transactions()`

## Callback Verification

```php
use Sujip\Esewa\Domain\Verification\CallbackPayload;
use Sujip\Esewa\Domain\Verification\VerificationExpectation;

$payload = CallbackPayload::fromArray([
    'data' => $_GET['data'] ?? '',
    'signature' => $_GET['signature'] ?? '',
]);

$result = $client->callbacks()->verifyCallback(
    $payload,
    new VerificationExpectation(
        totalAmount: '100.00',
        transactionUuid: 'TXN-1001',
        productCode: 'EPAYTEST',
    )
);

if ($result->state->value === 'replayed') {
    http_response_code(409);
    exit('Replay detected');
}

if (!$result->isSuccessful()) {
    http_response_code(400);
    exit('Invalid callback');
}
```

Do not treat the success redirect alone as proof of payment. Verify the callback on your backend and keep a status check as a fallback when something looks off.

## Transaction Status

```php
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest;

$status = $client->transactions()->fetchStatus(new TransactionStatusRequest(
    transactionUuid: 'TXN-1001',
    totalAmount: '100.00',
    productCode: 'EPAYTEST',
));

if ($status->isSuccessful()) {
    // mark paid
}
```

## Production Notes

For live traffic, turn on replay protection and use persistent storage:

```php
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Esewa;
use Sujip\Esewa\Infrastructure\Idempotency\FilesystemIdempotencyStore;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    environment: 'uat',
    options: new ClientOptions(
        preventCallbackReplay: true,
        idempotencyStore: new FilesystemIdempotencyStore(__DIR__ . '/storage/esewa-idempotency'),
    ),
);
```

Retry behavior is also configurable:

```php
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Support\FixedDelayRetryPolicy;

$options = new ClientOptions(
    retryPolicy: new FixedDelayRetryPolicy(
        maxRetries: 3,
        delayUs: 250000,
    ),
);
```

## Omnipay Bridge

```php
use Omnipay\Esewa\SecureGateway;

$gateway = new SecureGateway();
$gateway->setMerchantCode('EPAYTEST');
$gateway->setSecretKey($_ENV['ESEWA_SECRET_KEY']);
$gateway->setProductCode('EPAYTEST');
$gateway->setTestMode(true);
$gateway->setReturnUrl('https://merchant.example.com/esewa/success');
$gateway->setFailureUrl('https://merchant.example.com/esewa/failure');
```

Supported bridge flows:

- `purchase()`
- `completePurchase()`
- `verifyPayment()`

## Development

```bash
composer test
composer stan
composer rector:check
```
