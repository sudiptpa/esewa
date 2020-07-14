<?php

require __DIR__.'/vendor/autoload.php';

use Omnipay\Omnipay;

$gateway = Omnipay::create('Esewa_Secure');

$gateway->setMerchantCode('epay_payment');
$gateway->setTestMode(true);

$response = $gateway->purchase([
    'amount'         => 100,
    'deliveryCharge' => 0,
    'serviceCharge'  => 0,
    'taxAmount'      => 0,
    'totalAmount'    => 100,
    'productCode'    => 'ABAC2098',
    'returnUrl'      => 'https://merchant.com/payment/1/complete',
    'failedUrl'      => 'https://merchant.com/payment/1/failed',
])->send();

if ($response->isRedirect()) {
    $response->redirect();
}

// Verify Payment

$response = $gateway->verifyPayment([
    'amount'          => 100,
    'referenceNumber' => 'GDFG89',
    'productCode'     => 'ABAC2098',
])->send();

if ($response->isSuccessful()) {
    // Success
}

// Failed
