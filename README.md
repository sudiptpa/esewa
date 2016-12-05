# Omnipay: eSewa

**eSewa driver for the Omnipay PHP payment processing library**

[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements NAB Transact support for Omnipay.

[![Build Status](https://travis-ci.org/sudiptpa/esewa.svg?branch=master)](https://travis-ci.org/sudiptpa/esewa)
[![GitHub license](https://img.shields.io/badge/license-MIT-blue.svg)](https://raw.githubusercontent.com/sudiptpa/esewa/master/LICENSE)

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "sudiptpa/esewa": "~2.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

Comming soon !


For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Contributing

Contributions are welcome and will be fully credited.
We accept contributions via Pull Requests

## Pull Requests

PSR-2 Coding Standard - The easiest way to apply the conventions is to install [PHP Code Sniffer](http://pear.php.net/package/PHP_CodeSniffer).

Add tests! - Your patch won't be accepted if it doesn't have tests.

Document any change in behaviour - Make sure the README.md and any other relevant documentation are kept up-to-date.

Consider our release cycle - We try to follow SemVer v2.0.0. Randomly breaking public APIs is not an option.

Create feature branches - Don't ask us to pull from your master branch.

One pull request per feature - If you want to do more than one thing, send multiple pull requests.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/sudiptpa/nabtransact/issues),
or better yet, fork the library and submit a pull request.
