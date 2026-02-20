<?php

declare(strict_types=1);

namespace EsewaPayment\Config;

final class EndpointResolver
{
    public function checkoutFormUrl(Config $config): string
    {
        if ($config->checkoutFormUrl !== null && $config->checkoutFormUrl !== '') {
            return $config->checkoutFormUrl;
        }

        return match ($config->environment) {
            Environment::UAT => 'https://rc-epay.esewa.com.np/api/epay/main/v2/form',
            Environment::PRODUCTION => 'https://epay.esewa.com.np/api/epay/main/v2/form',
        };
    }

    public function statusCheckUrl(Config $config): string
    {
        if ($config->statusCheckUrl !== null && $config->statusCheckUrl !== '') {
            return $config->statusCheckUrl;
        }

        return match ($config->environment) {
            Environment::UAT => 'https://rc-epay.esewa.com.np/api/epay/transaction/status/',
            Environment::PRODUCTION => 'https://epay.esewa.com.np/api/epay/transaction/status/',
        };
    }
}
