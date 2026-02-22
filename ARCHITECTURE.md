# Architecture

## Overview

`Sujip\Esewa` is a framework-agnostic eSewa ePay v2 SDK focused on checkout payload generation, callback verification, and transaction status validation.
The package follows a domain-oriented structure with clear service boundaries and pluggable transport/idempotency contracts.

## Project Map

```text
src/
  EsewaPayment.php
  Config/
    GatewayConfig.php          Gateway credentials + runtime options
    EndpointResolver.php       UAT/production endpoint resolution
    Environment.php
    ClientOptions.php
  Client/
    EsewaClient.php            Root client exposing service modules
    CheckoutService.php        Checkout/form payload workflow
    CallbackService.php        Callback decode + verification workflow
    TransactionService.php     Status check workflow
  Service/
    SignatureService.php       HMAC signature generation/verification
    CallbackVerifier.php       Anti-fraud + callback validation rules
  Domain/
    Checkout/
      CheckoutRequest.php
      CheckoutPayload.php
      CheckoutIntent.php
    Verification/
      CallbackPayload.php
      CallbackData.php
      CallbackVerification.php
      VerificationExpectation.php
    Transaction/
      TransactionStatusRequest.php
      TransactionStatusPayload.php
      TransactionStatus.php
      PaymentStatus.php
  Contracts/
    TransportInterface.php
    IdempotencyStoreInterface.php
  Infrastructure/
    Transport/Psr18Transport.php
    Idempotency/InMemoryIdempotencyStore.php
    Idempotency/NullIdempotencyStore.php
  Exception/
    EsewaException.php
    SignatureException.php
    InvalidPayloadException.php
    FraudValidationException.php
    ApiErrorException.php
    TransportException.php
tests/
  Unit/*
  Fakes/FakeTransport.php
  Fakes/FlakyTransport.php
  Fakes/ArrayLogger.php
```

## Runtime Flows

### 1) Checkout Flow

1. Build client via `EsewaPayment` using `GatewayConfig`.
2. `CheckoutService` validates request + options.
3. `SignatureService` signs required fields.
4. Service returns `CheckoutPayload` for HTML form redirect/post.

### 2) Callback Verification Flow

1. Callback payload is decoded into `CallbackPayload`/`CallbackData`.
2. `CallbackVerifier` validates signature and anti-fraud expectations.
3. `CallbackService` returns structured `CallbackVerification`.
4. Optional idempotency guard/store prevents duplicate side effects.

### 3) Transaction Status Flow

1. `TransactionService` sends status request using `TransportInterface`.
2. Payload is mapped into `TransactionStatusPayload` and `TransactionStatus` enums.
3. Caller applies business policy based on typed status.

## Security Model

- Signature validation is centralized in `SignatureService`.
- Callback validation enforces expectation checks (amount/product/uuid/reference).
- Fraud mismatches raise `FraudValidationException`.
- Invalid callback payloads raise `InvalidPayloadException`.
- Transport/API failures are isolated in typed exceptions.

## Extension Points

- Implement `TransportInterface` to integrate any HTTP client.
- Implement `IdempotencyStoreInterface` for Redis/DB-backed duplicate protection.
- Extend domain models and mapping rules without coupling to framework code.

## Testing Strategy

- Unit tests cover checkout, callback, transaction, endpoint resolution, and production hardening paths.
- Fake transports provide deterministic network behavior.
- Flaky transport tests validate failure handling.

## Contributor Notes

- Keep core package framework-agnostic.
- Preserve public API stability; prefer additive changes.
- Add tests and README updates for behavior changes.
- Keep configuration explicit and secure by default.
