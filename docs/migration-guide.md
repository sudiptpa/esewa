# Migration Guide

## What changed

The package now leans on request and result models instead of passing loose arrays and strings through every layer.

The main differences are:

- checkout, callback, and status flows are centered around domain models
- request models support `toArray()` and `fromArray()`
- core identifiers and money values are normalized into typed value objects
- callback verification returns an explicit state
- retry behavior can be configured as a policy
- replay protection can use persistent zero-dependency storage

## Public Namespaces

- framework-agnostic client: `Sujip\\Esewa`
- Omnipay bridge: `Omnipay\\Esewa`

## Framework-Agnostic Client Migration

### Earlier style

Older integrations mostly passed strings around and treated request objects as thin containers.

### Current style

You can still pass strings to the constructors. The difference is that the models now normalize and validate those values more aggressively.

```php
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;
use Sujip\Esewa\Esewa;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: 'secret',
    environment: 'uat',
);

$request = new CheckoutRequest(
    amount: '100',
    taxAmount: '0',
    serviceCharge: '0',
    deliveryCharge: '0',
    transactionUuid: 'TXN-1001',
    productCode: 'EPAYTEST',
    successUrl: 'https://merchant.example.com/esewa/success',
    failureUrl: 'https://merchant.example.com/esewa/failure',
);
```

If you want stricter typing in your own code:

```php
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

## Model conversion

Models support array conversion, which is useful at controller boundaries, in queued jobs, and in test fixtures.

```php
$payload = $request->toArray();
$restored = CheckoutRequest::fromArray($payload);
```

The same pattern applies to:

- `VerificationExpectation`
- `TransactionStatusRequest`
- `CallbackPayload`
- `TransactionStatusPayload`

## Callback verification

Verification now returns a richer result object.

```php
$result = $client->callbacks()->verifyCallback($payload, $expectation);
```

Available states:

- `verified`
- `invalid_signature`
- `replayed`

That makes it easier to tell a bad signature from a replayed callback.

## Retry and replay protection

Retry behavior is no longer limited to a couple of scalar options. You can provide a policy object instead.

```php
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Support\FixedDelayRetryPolicy;

$options = new ClientOptions(
    retryPolicy: new FixedDelayRetryPolicy(maxRetries: 3, delayUs: 250000),
);
```

Replay protection can use persistent storage:

- `FilesystemIdempotencyStore`
- `PdoIdempotencyStore`

## Omnipay migration

The bridge remains under `Omnipay\\Esewa`.

Supported methods:

- `purchase()`
- `completePurchase()`
- `verifyPayment()`

## Production checklist

1. Keep `merchantCode` and `secretKey` in environment configuration.
2. Verify callbacks on the backend before fulfillment.
3. Compare verified values against stored order state.
4. Use filesystem or PDO-backed replay protection in production.
5. Reconcile uncertain states with transaction status checks.
6. Treat redirect success as a user-facing signal, not as proof of payment.
