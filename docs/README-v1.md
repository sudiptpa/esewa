# Omnipay: eSewa

**eSewa driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP. This package implements eSewa support for Omnipay.

[![StyleCI](https://github.styleci.io/repos/75586885/shield?branch=master&format=plastic)](https://github.styleci.io/repos/75586885)
[![Latest Stable Version](https://poser.pugx.org/sudiptpa/omnipay-esewa/v/stable)](https://packagist.org/packages/sudiptpa/omnipay-esewa)
[![Total Downloads](https://poser.pugx.org/sudiptpa/omnipay-esewa/downloads)](https://packagist.org/packages/sudiptpa/omnipay-esewa)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/sudiptpa/esewa/master/LICENSE)

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply require `league/omnipay` and `sudiptpa/omnipay-esewa` with Composer:

```
composer require league/omnipay sudiptpa/omnipay-esewa
```

## Basic Usage

### Purchase

```php
    use Omnipay\Omnipay;
    use Exception;

    $gateway = Omnipay::create('Esewa_Secure');

    $gateway->setMerchantCode('epay_payment');
    $gateway->setTestMode(true);

    try {
        $response = $gateway->purchase([
            'amount' => 100,
            'deliveryCharge' => 0,
            'serviceCharge' => 0,
            'taxAmount' => 0,
            'totalAmount' => 100,
            'productCode' => 'ABAC2098',
            'returnUrl' => 'https://merchant.com/payment/1/complete',
            'failedUrl' => 'https://merchant.com/payment/1/failed',
        ])->send();

        if ($response->isRedirect()) {
            $response->redirect();
        }
    } catch (Exception $e) {
        return $e->getMessage();
    }
```

After successful payment and redirect back to merchant site, you need to now verify the payment with another API request.

### Verify Payment

```php
    $gateway = Omnipay::create('Esewa_Secure');

    $gateway->setMerchantCode('epay_payment');
    $gateway->setTestMode(true);

    $response = $gateway->verifyPayment([
        'amount' => 100,
        'referenceNumber' => 'GDFG89',
        'productCode' => 'gadfg-gadf',
    ])->send();

    if ($response->isSuccessful()) {
        // Success
    }

    // Failed
```

## Laravel Integration

Please follow the [eSewa Online Payment Gateway Integration](https://sujipthapa.com/blog/esewa-online-payment-gateway-integration-with-php) and follow step by step guidlines.

## Official Doc

Please follow the [Official Doc](https://developer.esewa.com.np) to understand about the parameters and their descriptions.

## Contributing

Contributions are **welcome** and will be fully **credited**.

Contributions can be made via a Pull Request on [Github](https://github.com/sudiptpa/esewa).

## Support

If you are having general issues with Omnipay Esewa, drop an email to sudiptpa@gmail.com for quick support.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/sudiptpa/esewa/issues),
or better yet, fork the library and submit a pull request.

## License

This package is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
