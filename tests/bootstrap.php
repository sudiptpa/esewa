<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

if (!class_exists(\Omnipay\Common\AbstractGateway::class)) {
    require __DIR__ . '/Stubs/OmnipayCommon.php';
}
