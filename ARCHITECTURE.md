# Architecture

## Overview

The package is split into a framework-agnostic core SDK and an Omnipay bridge that sits on top of it.

The public namespaces are:

- `Sujip\\Esewa` for the framework-agnostic client
- `Omnipay\\Esewa` for the Omnipay bridge

The design is intentionally conservative:

- payment rules live in one place
- transport and storage stay replaceable
- callback verification stays deterministic
- the core stays zero-dependency by default

## Design choices

1. Core payment flows use dedicated models instead of loose arrays.
2. Money and identifiers are normalized early, so validation is not scattered.
3. The default runtime works with built-in pieces.
4. Callback verification and replay handling are part of the package, not left to application code.
5. Transport, clock, retry policy, and replay storage can be replaced without rewriting the domain layer.

## Project Map

```text
src/
  Esewa.php                     Static entry point
  Client/
    EsewaClient.php             Root client
    CheckoutService.php         Checkout workflow
    CallbackService.php         Callback workflow
    TransactionService.php      Status workflow
  Config/
    GatewayConfig.php           Credentials and endpoint settings
    EndpointResolver.php        UAT/production endpoint resolution
    Environment.php
    ClientOptions.php           Runtime options, retry policy, clock, replay config
  Contracts/
    Arrayable.php
    Hydratable.php
    ClockInterface.php
    RetryPolicyInterface.php
    TransportInterface.php
    IdempotencyStoreInterface.php
  Domain/
    Checkout/
      CheckoutRequest.php
      CheckoutPayload.php
      CheckoutIntent.php
    Verification/
      CallbackPayload.php
      CallbackData.php
      VerificationExpectation.php
      CallbackVerification.php
      VerificationState.php
    Transaction/
      TransactionStatusRequest.php
      TransactionStatusPayload.php
      TransactionStatus.php
      PaymentStatus.php
  ValueObject/
    Amount.php
    TransactionUuid.php
    ProductCode.php
    ReferenceId.php
  Service/
    SignatureService.php
    CallbackVerifier.php
  Infrastructure/
    Transport/
      CurlTransport.php
      Psr18Transport.php
    Idempotency/
      InMemoryIdempotencyStore.php
      NullIdempotencyStore.php
      FilesystemIdempotencyStore.php
      PdoIdempotencyStore.php
  Support/
    SystemClock.php
    FixedDelayRetryPolicy.php
  Omnipay/
    SecureGateway.php
    Message/*
```

## Layer breakdown

### 1. Entry Layer

`Esewa` is the convenience bootstrap.

Responsibilities:

- build `GatewayConfig`
- choose the transport
- construct `EsewaClient`

### 2. Client Layer

`EsewaClient` exposes the three functional modules:

- `checkout()`
- `callbacks()`
- `transactions()`

The client is intentionally thin. It wires the pieces together and leaves the actual rules to the domain and service layers.

### 3. Domain Layer

The domain layer is where most of the package behavior lives.

Responsibilities:

- validate request shape
- normalize primitives into value objects
- convert to and from arrays
- expose explicit result types

Examples:

- `CheckoutRequest` models a full checkout intent input
- `VerificationExpectation` models anti-fraud comparison context
- `TransactionStatusRequest` models reconciliation queries
- `CallbackVerification` models callback outcome with explicit `VerificationState`

### 4. Value Object Layer

Value objects exist to stop the usual payment bugs caused by passing strings around everywhere.

Current value objects:

- `Amount`
- `TransactionUuid`
- `ProductCode`
- `ReferenceId`

This keeps formatting rules and basic validation in one place.

### 5. Service Layer

The service layer holds logic that should not be duplicated across controllers or adapters.

- `SignatureService` generates and verifies signatures
- `CallbackVerifier` enforces signature validation, expectation matching, and replay protection

### 6. Infrastructure Layer

Infrastructure concerns stay behind contracts.

Transport:

- `CurlTransport` for the zero-dependency runtime path
- `Psr18Transport` for optional PSR-18 integration

Replay protection stores:

- `NullIdempotencyStore`
- `InMemoryIdempotencyStore`
- `FilesystemIdempotencyStore`
- `PdoIdempotencyStore`

### 7. Support Layer

Support abstractions keep time and retry behavior deterministic.

- `SystemClock`
- `FixedDelayRetryPolicy`

These are deliberately small. Their job is to keep time-dependent and retry-dependent logic testable.

## Runtime flows

### Checkout Flow

1. Build `CheckoutRequest`
2. `CheckoutService` computes total amount and signature
3. `CheckoutPayload` is created as a typed form payload
4. `CheckoutIntent` exposes action URL and form fields

### Callback Verification Flow

1. Build `CallbackPayload`
2. Decode into `CallbackData`
3. Verify signature with `SignatureService`
4. Validate expected values with `VerificationExpectation`
5. Check replay protection through `IdempotencyStoreInterface`
6. Return `CallbackVerification` with an explicit `VerificationState`

### Transaction Status Flow

1. Build `TransactionStatusRequest`
2. `TransactionService` performs the status request through `TransportInterface`
3. Retry behavior is delegated to `RetryPolicyInterface`
4. API payload is mapped to `TransactionStatusPayload`
5. Domain result is returned as `TransactionStatus`

## Model pattern

The model pattern here is intentionally simple:

- request models support `fromArray()` and `toArray()`
- result models expose typed fields and `toArray()`
- value objects encapsulate primitive validation

That keeps the SDK easy to use from plain PHP, while still making it easier to cross framework boundaries cleanly.

## Extensibility points

Transport:

- implement `TransportInterface`

Replay protection:

- implement `IdempotencyStoreInterface`

Time:

- implement `ClockInterface`

Retry behavior:

- implement `RetryPolicyInterface`

That gives applications room to swap in their own transport or persistence choices without adding mandatory packages to the core.

## Omnipay Bridge

`Omnipay\\Esewa` is kept separate on purpose.

Bridge responsibilities:

- adapt Omnipay request/response expectations
- reuse the same core domain and service logic
- avoid maintaining two different sets of payment rules

## Testing

The current test suite covers:

- domain validation
- checkout payload generation
- callback signature verification
- replay protection behavior
- retry policy behavior
- persistent idempotency stores
- Omnipay bridge behavior
- static analysis across the full source tree

The point of that coverage is simple: payment verification and reconciliation code should stay boring, predictable, and hard to regress.
