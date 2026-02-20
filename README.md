# EsewaPayment PHP SDK

A modern, framework-agnostic eSewa ePay v2 SDK for PHP.

## Highlights

- ePay v2 checkout intent generation
- HMAC-SHA256 + base64 signature handling
- Callback/return payload verification
- Status check API client
- Anti-fraud field consistency checks
- PSR-18 transport architecture
- PHP 8.3 to 8.5 support

## Installation

```bash
composer require sudiptpa/esewa-payment
```

Optional adapters used in examples:

```bash
composer require symfony/http-client nyholm/psr7
```

## Quick Start

```php
use EsewaPayment\Client\EsewaGateway;
use EsewaPayment\Config\Config;
use EsewaPayment\Infrastructure\Transport\Psr18Transport;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$config = Config::fromArray([
    'merchant_code' => 'EPAYTEST',
    'secret_key' => 'YOUR_SECRET',
    'environment' => 'uat',
]);

$gateway = new EsewaGateway(
    $config,
    new Psr18Transport(new Psr18Client(), new Psr17Factory())
);
```

## Modules

- `checkout()->createIntent(...)`
- `callback()->verify(...)`
- `transactions()->status(...)`

## Development

```bash
composer test
composer stan
composer rector:check
```
