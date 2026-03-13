# User Guide

There are two ways to use this package:

- `Sujip\\Esewa` for the framework-agnostic client
- `Omnipay\\Esewa` for the Omnipay bridge

## 1. Framework-Agnostic Client

### Create a client

```php
<?php

declare(strict_types=1);

use Sujip\Esewa\Esewa;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    environment: 'uat',
);
```

### Build requests

You can pass plain strings to the request models. Internally, the SDK normalizes those values into typed objects.

```php
use Sujip\Esewa\Domain\Checkout\CheckoutRequest;

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

If you want stricter input handling in your own codebase, construct the value objects directly:

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

### Convert models to arrays

Core models support `toArray()` and `fromArray()`.

```php
$payload = $request->toArray();
$restored = CheckoutRequest::fromArray($payload);
```

That is useful for:

- controller boundaries
- queue payloads
- config-driven integrations
- fixtures and tests

### Create a checkout intent

```php
$intent = $client->checkout()->createIntent($request);
$form = $intent->form();
```

### Verify callbacks

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
```

### Handle verification states

```php
switch ($result->state->value) {
    case 'verified':
        // continue
        break;

    case 'replayed':
        http_response_code(409);
        exit('Replay detected');

    case 'invalid_signature':
        http_response_code(400);
        exit('Invalid signature');
}
```

### Enable replay protection

For production, use persistent storage. The SDK ships with these store options:

- `FilesystemIdempotencyStore`
- `PdoIdempotencyStore`
- `InMemoryIdempotencyStore` for tests

Filesystem example:

```php
use Sujip\Esewa\Config\ClientOptions;
use Sujip\Esewa\Infrastructure\Idempotency\FilesystemIdempotencyStore;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    environment: 'uat',
    options: new ClientOptions(
        preventCallbackReplay: true,
        idempotencyStore: new FilesystemIdempotencyStore(__DIR__ . '/storage/esewa-idempotency'),
    )
);
```

PDO example:

```php
use PDO;
use Sujip\Esewa\Infrastructure\Idempotency\PdoIdempotencyStore;

$pdo = new PDO('sqlite:' . __DIR__ . '/storage/esewa.sqlite');
$store = new PdoIdempotencyStore($pdo);
```

### Reconcile transaction state

```php
use Sujip\Esewa\Domain\Transaction\TransactionStatusRequest;

$status = $client->transactions()->fetchStatus(new TransactionStatusRequest(
    transactionUuid: 'TXN-1001',
    totalAmount: '100.00',
    productCode: 'EPAYTEST',
));
```

### Customize retries

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

### Use a custom transport

The default transport is `CurlTransport`, so the core stays zero-dependency at runtime. If your application already has a PSR-18 client, you can swap it in.

```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Sujip\Esewa\Infrastructure\Transport\Psr18Transport;
use Symfony\Component\HttpClient\Psr18Client;

$client = Esewa::make(
    merchantCode: 'EPAYTEST',
    secretKey: $_ENV['ESEWA_SECRET_KEY'],
    transport: new Psr18Transport(new Psr18Client(), new Psr17Factory()),
    environment: 'uat',
);
```

## 2. Omnipay Bridge

Use the bridge if the rest of your payment layer already follows Omnipay conventions.

### Create the gateway

```php
use Omnipay\Esewa\SecureGateway;

$gateway = new SecureGateway();
$gateway->setMerchantCode('EPAYTEST');
$gateway->setSecretKey($_ENV['ESEWA_SECRET_KEY']);
$gateway->setProductCode('EPAYTEST');
$gateway->setTaxAmount('0');
$gateway->setServiceCharge('0');
$gateway->setDeliveryCharge('0');
$gateway->setTestMode(true);
$gateway->setReturnUrl('https://merchant.example.com/esewa/success');
$gateway->setFailureUrl('https://merchant.example.com/esewa/failure');
```

### Purchase flow

```php
$response = $gateway->purchase([
    'amount' => '100',
    'transactionId' => 'TXN-1001',
])->send();

if ($response->isRedirect()) {
    $redirectUrl = $response->getRedirectUrl();
    $fields = $response->getRedirectData();
}
```

### Complete purchase

```php
$response = $gateway->completePurchase([
    'transactionUuid' => 'TXN-1001',
    'totalAmount' => '100.00',
    'referenceNumber' => 'REF-1001',
])->send();

if ($response->isSuccessful()) {
    $reference = $response->getTransactionReference();
}
```

### Verify payment

```php
$response = $gateway->verifyPayment([
    'amount' => '100.00',
    'transactionId' => 'TXN-1001',
])->send();

if ($response->isSuccessful()) {
    $referenceId = $response->getReferenceId();
}
```

## 3. Production Guidance

1. Keep secrets outside source control.
2. Verify callbacks on the server before fulfillment.
3. Compare verified values with your stored order state.
4. Use persistent replay protection in production.
5. Treat redirect success as customer UX, not payment proof.
6. Reconcile ambiguous states with transaction status checks.

The common mistake here is trusting the browser redirect too early. eSewa can redirect a user back while your application still needs to verify the callback or reconcile the final state.

## 4. Error Handling

Core exceptions:

- `Sujip\\Esewa\\Exception\\EsewaException`
- `Sujip\\Esewa\\Exception\\InvalidPayloadException`
- `Sujip\\Esewa\\Exception\\SignatureException`
- `Sujip\\Esewa\\Exception\\FraudValidationException`
- `Sujip\\Esewa\\Exception\\TransportException`
- `Sujip\\Esewa\\Exception\\ApiErrorException`
