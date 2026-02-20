# EsewaPayment PHP SDK

Framework-agnostic eSewa ePay v2 SDK for modern PHP applications.

[![CI](https://github.com/sudiptpa/esewa/actions/workflows/ci.yml/badge.svg)](https://github.com/sudiptpa/esewa/actions/workflows/ci.yml)
[![Latest Release](https://img.shields.io/github/v/release/sudiptpa/esewa?sort=semver)](https://github.com/sudiptpa/esewa/releases)
[![GitHub Downloads](https://img.shields.io/github/downloads/sudiptpa/esewa/total)](https://github.com/sudiptpa/esewa/releases)
[![PHP Version](https://img.shields.io/badge/php-8.1--8.5-777bb4.svg)](https://www.php.net/)
[![Packagist](https://img.shields.io/badge/packagist-sudiptpa%2Fesewa--payment-f28d1a.svg)](https://packagist.org/packages/sudiptpa/esewa-payment)
[![License](https://img.shields.io/github/license/sudiptpa/esewa)](LICENSE)

## Highlights

- ePay v2 checkout intent generation (`/v2/form`)
- HMAC-SHA256 + base64 signature generation and verification
- Callback verification with anti-fraud field consistency checks
- Status check API support with typed status mapping
- Model-first request/payload/result objects
- PSR-18 transport integration
- PHP `8.1` to `8.5` support
- PHPUnit + PHPStan + CI matrix

## Table of Contents

- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core API Shape](#core-api-shape)
- [Checkout Flow](#checkout-flow)
- [Callback Verification Flow](#callback-verification-flow)
- [Transaction Status Flow](#transaction-status-flow)
- [Configuration Patterns](#configuration-patterns)
- [Production Hardening](#production-hardening)
- [Laravel Integration (Secure)](#laravel-integration-secure)
- [Custom Transport and Testing](#custom-transport-and-testing)
- [Error Handling](#error-handling)
- [Development](#development)

## Installation

```bash
composer require sudiptpa/esewa-payment
```

For PSR-18 usage examples:

```bash
composer require symfony/http-client nyholm/psr7
```

## Quick Start

```php
<?php

declare(strict_types=1);

use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\ClientOptions;
use EsewaPayment\EsewaPayment;
use EsewaPayment\Infrastructure\Idempotency\InMemoryIdempotencyStore;
use EsewaPayment\Infrastructure\Transport\Psr18Transport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Psr18Client;

$client = EsewaPayment::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    transport: new Psr18Transport(new Psr18Client(), new Psr17Factory()),
    environment: 'uat', // uat|test|sandbox|production|prod|live
);
```

Add hardening options only when needed:

```php
<?php

declare(strict_types=1);

use EsewaPayment\Config\ClientOptions;
use EsewaPayment\EsewaPayment;
use EsewaPayment\Infrastructure\Idempotency\InMemoryIdempotencyStore;
use EsewaPayment\Infrastructure\Transport\Psr18Transport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\Psr18Client;

$client = EsewaPayment::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    transport: new Psr18Transport(new Psr18Client(), new Psr17Factory()),
    environment: 'uat',
    options: new ClientOptions(
        maxStatusRetries: 2,
        statusRetryDelayMs: 150,
        preventCallbackReplay: true,
        idempotencyStore: new InMemoryIdempotencyStore(),
        logger: new NullLogger(),
    ),
);
```

## Core API Shape

Main client and modules:

- `EsewaClient`
- `$client->checkout()`
- `$client->callbacks()`
- `$client->transactions()`

Primary model objects:

- `CheckoutRequest`
- `CheckoutIntent`
- `CallbackPayload`
- `VerificationExpectation`
- `CallbackVerification`
- `TransactionStatusRequest`
- `TransactionStatus`

Static convenience entry point:

```php
use EsewaPayment\EsewaPayment;

$client = EsewaPayment::make(
    merchantCode: 'EPAYTEST',
    secretKey: 'secret',
    transport: $transport,
);
```

## Checkout Flow

### 1) Build a checkout intent

```php
use EsewaPayment\Domain\Checkout\CheckoutRequest;

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

### 2) Render form fields in plain PHP

```php
$form = $intent->form();

echo '<form method="POST" action="' . htmlspecialchars($form['action_url'], ENT_QUOTES) . '">';

foreach ($form['fields'] as $name => $value) {
    echo '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES) . '" value="' . htmlspecialchars($value, ENT_QUOTES) . '">';
}

echo '<button type="submit">Pay with eSewa</button>';
echo '</form>';
```

### 3) Get fields directly as array

```php
$fields = $intent->fields(); // array<string,string>
```

## Callback Verification Flow

Never trust redirect success alone. Always verify callback payload and signature.

### 1) Build payload from callback request

```php
use EsewaPayment\Domain\Verification\CallbackPayload;

$payload = CallbackPayload::fromArray([
    'data' => $_GET['data'] ?? '',
    'signature' => $_GET['signature'] ?? '',
]);
```

### 2) Verify with anti-fraud expectation context

```php
use EsewaPayment\Domain\Verification\VerificationExpectation;

$verification = $client->callbacks()->verifyCallback(
    $payload,
    new VerificationExpectation(
        totalAmount: '100.00',
        transactionUuid: 'TXN-1001',
        productCode: 'EPAYTEST',
        referenceId: null, // optional
    )
);

if (!$verification->valid || !$verification->isSuccessful()) {
    // reject
}
```

### 3) Verify without context (signature only)

```php
$verification = $client->callbacks()->verifyCallback($payload);
```

## Transaction Status Flow

```php
use EsewaPayment\Domain\Transaction\TransactionStatusRequest;

$status = $client->transactions()->fetchStatus(new TransactionStatusRequest(
    transactionUuid: 'TXN-1001',
    totalAmount: '100.00',
    productCode: 'EPAYTEST',
));

if ($status->isSuccessful()) {
    // COMPLETE
}

echo $status->status->value; // PENDING|COMPLETE|FULL_REFUND|PARTIAL_REFUND|AMBIGUOUS|NOT_FOUND|CANCELED|UNKNOWN
```

## Configuration Patterns

### Environment aliases

- UAT: `uat`, `test`, `sandbox`
- Production: `production`, `prod`, `live`

### Endpoint overrides

Useful if eSewa documentation/endpoints differ by account region or rollout:

```php
$config = GatewayConfig::make(
    merchantCode: 'EPAYTEST',
    secretKey: 'secret',
    environment: 'uat',
    checkoutFormUrl: 'https://custom-esewa.example/api/epay/main/v2/form',
    statusCheckUrl: 'https://custom-esewa.example/api/epay/transaction/status/',
);
```

## Production Hardening

### Retry policy for status checks

`fetchStatus()` retries `TransportException` failures based on `ClientOptions`:

```php
new ClientOptions(
    maxStatusRetries: 2,  // total additional retry attempts
    statusRetryDelayMs: 150,
);
```

### Callback replay protection (idempotency)

Enable replay protection with an idempotency store:

```php
use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Infrastructure\Idempotency\InMemoryIdempotencyStore;

$options = new ClientOptions(
    preventCallbackReplay: true,
    idempotencyStore: new InMemoryIdempotencyStore(),
);
```

For production, implement `IdempotencyStoreInterface` with shared storage (Redis, DB, etc.) instead of in-memory storage.

### Logging hooks

Provide any PSR-3 logger in `ClientOptions`:

```php
use Psr\Log\LoggerInterface;

$options = new ClientOptions(logger: $logger); // $logger is LoggerInterface
```

Emitted event keys (via log context):

- `esewa.status.started`
- `esewa.status.retry`
- `esewa.status.completed`
- `esewa.status.failed`
- `esewa.callback.invalid_signature`
- `esewa.callback.replay_detected`
- `esewa.callback.verified`

## Laravel Integration (Secure)

### Supported Laravel Versions

This package is framework-agnostic and supports PHP `8.1` to `8.5`.

Laravel support is therefore:

- Laravel `10.x`, `11.x`, `12.x` when your app runtime is PHP `8.1` to `8.5`
- Other Laravel versions may work if they run on supported PHP and PSR dependencies, but are not the primary target matrix

### 1) Service container binding (single client, production options)

Create a provider (for example `app/Providers/EsewaServiceProvider.php`):

```php
use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Config\ClientOptions;
use EsewaPayment\Config\GatewayConfig;
use EsewaPayment\Contracts\IdempotencyStoreInterface;
use EsewaPayment\Infrastructure\Idempotency\InMemoryIdempotencyStore;
use EsewaPayment\Infrastructure\Transport\Psr18Transport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$this->app->singleton(IdempotencyStoreInterface::class, function () {
    // Replace with Redis/DB-backed implementation for multi-server production.
    return new InMemoryIdempotencyStore();
});

$this->app->singleton(EsewaClient::class, function ($app) {
    return new EsewaClient(
        GatewayConfig::make(
            merchantCode: config('services.esewa.merchant_code'),
            secretKey: config('services.esewa.secret_key'),
            environment: config('services.esewa.environment', 'uat'),
        ),
        new Psr18Transport(new Psr18Client(), new Psr17Factory()),
        new ClientOptions(
            maxStatusRetries: 2,
            statusRetryDelayMs: 150,
            preventCallbackReplay: true,
            idempotencyStore: $app->make(IdempotencyStoreInterface::class),
            logger: $app->make(\Psr\Log\LoggerInterface::class),
        ),
    );
});
```

### 2) Route design (do not trust success redirect)

Use separate callback verification endpoint and finalize order only after verification:

```php
use App\Http\Controllers\EsewaCallbackController;
use App\Http\Controllers\EsewaCheckoutController;
use Illuminate\Support\Facades\Route;

Route::post('/payments/esewa/checkout', [EsewaCheckoutController::class, 'store'])
    ->name('payments.esewa.checkout');

Route::get('/payments/esewa/success', [EsewaCheckoutController::class, 'success'])
    ->name('payments.esewa.success');

Route::get('/payments/esewa/failure', [EsewaCheckoutController::class, 'failure'])
    ->name('payments.esewa.failure');

Route::post('/payments/esewa/callback', [EsewaCallbackController::class, 'handle'])
    ->name('payments.esewa.callback');
```

### 3) Checkout controller (server-side source of truth)

```php
use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Domain\Checkout\CheckoutRequest;
use Illuminate\Http\Request;

final class EsewaCheckoutController
{
    public function store(Request $request, EsewaClient $esewa)
    {
        $order = /* create order in DB and generate transaction UUID */;

        $intent = $esewa->checkout()->createIntent(new CheckoutRequest(
            amount: (string) $order->amount,
            taxAmount: '0',
            serviceCharge: '0',
            deliveryCharge: '0',
            transactionUuid: $order->transaction_uuid,
            productCode: config('services.esewa.merchant_code'),
            successUrl: route('payments.esewa.success'),
            failureUrl: route('payments.esewa.failure'),
        ));

        return view('payments.esewa.redirect', [
            'action' => $intent->actionUrl,
            'fields' => $intent->fields(),
        ]);
    }
}
```

`resources/views/payments/esewa/redirect.blade.php`:

```blade
<form id="esewa-payment-form" method="POST" action="{{ $action }}">
    @foreach ($fields as $name => $value)
        <input type="hidden" name="{{ $name }}" value="{{ $value }}">
    @endforeach
</form>

<script>
    document.getElementById('esewa-payment-form').submit();
</script>
```

### 4) Callback controller with strict verification

```php
use EsewaPayment\Client\EsewaClient;
use EsewaPayment\Domain\Verification\CallbackPayload;
use EsewaPayment\Domain\Verification\VerificationExpectation;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EsewaCallbackController
{
    public function handle(Request $request, EsewaClient $esewa): Response
    {
        $payload = CallbackPayload::fromArray([
            'data' => (string) $request->input('data', ''),
            'signature' => (string) $request->input('signature', ''),
        ]);

        $order = /* lookup order using decoded transaction UUID */;
        $expectation = new VerificationExpectation(
            totalAmount: number_format((float) $order->payable_amount, 2, '.', ''),
            transactionUuid: $order->transaction_uuid,
            productCode: config('services.esewa.merchant_code'),
            referenceId: null,
        );

        $result = $esewa->callbacks()->verifyCallback($payload, $expectation);

        if (!$result->isSuccessful()) {
            return response('invalid', 400);
        }

        // Optional: double-check status endpoint before marking paid.
        // $status = $esewa->transactions()->fetchStatus(...);

        // Mark paid exactly once (idempotent DB update).
        // dispatch(new FulfillOrderJob($order->id));

        return response('ok', 200);
    }
}
```

### 5) Security checklist (Laravel)

- Keep `merchant_code` and `secret_key` only in `.env`
- Never trust only `success` redirect for payment finalization
- Verify callback signature and anti-fraud fields every time
- Enforce idempotent order updates in DB and callback processing
- Log verification failures and replay attempts
- Queue downstream fulfillment after verified payment

### Framework usage outside Laravel

- Register `GatewayConfig` as a service parameter object
- Inject `EsewaClient` into controllers/services
- Use `checkout`, `callbacks`, and `transactions` modules in your application service layer

## Custom Transport and Testing

You can use any custom transport implementing `TransportInterface`.

```php
use EsewaPayment\Contracts\TransportInterface;

final class FakeTransport implements TransportInterface
{
    public function get(string $url, array $query = [], array $headers = []): array
    {
        return ['status' => 'COMPLETE', 'ref_id' => 'REF-123'];
    }
}
```

Inject it:

```php
$client = new EsewaClient($config, new FakeTransport());
```

## Error Handling

Key exceptions:

- `InvalidPayloadException`
- `FraudValidationException`
- `TransportException`
- `ApiErrorException`
- Base: `EsewaException`

Typical handling:

```php
use EsewaPayment\Exception\ApiErrorException;
use EsewaPayment\Exception\EsewaException;
use EsewaPayment\Exception\FraudValidationException;
use EsewaPayment\Exception\InvalidPayloadException;
use EsewaPayment\Exception\TransportException;

try {
    $verification = $client->callbacks()->verifyCallback($payload, $expectation);
} catch (FraudValidationException|InvalidPayloadException $e) {
    // fail closed
} catch (TransportException|ApiErrorException $e) {
    // retry/report policy
} catch (EsewaException $e) {
    // domain fallback policy
}
```

## Development

```bash
composer test
composer stan
composer rector:check
```

## Author

- Sujip Thapa (<sudiptpa@gmail.com>)

## Contributing

Contributions are welcome.

If you would like to contribute:

1. Fork the repository and create a feature branch.
2. Add or update tests for your change.
3. Run quality checks locally (`composer test`, `composer stan`).
4. Open a pull request with a clear description.

Bug reports, security hardening ideas, docs improvements, and real-world integration examples are all appreciated.
