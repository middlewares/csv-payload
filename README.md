# middlewares/csv-payload

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
![Testing][ico-ga]
[![Total Downloads][ico-downloads]][link-downloads]

Extends [middlewares/payload][link-payload] to add support for parsing the CSV body of the request.

## Requirements

* PHP >= 7.0
* A [PSR-7](https://packagist.org/providers/psr/http-message-implementation) http message implementation ([Diactoros](https://github.com/zendframework/zend-diactoros), [Guzzle](https://github.com/guzzle/psr7), [Slim](https://github.com/slimphp/Slim), etc...)
* A [PSR-15 middleware dispatcher](https://github.com/middlewares/awesome-psr15-middlewares#dispatcher)

## Installation

This package is installable and autoloadable via Composer as [middlewares/csv-payload](https://packagist.org/packages/middlewares/csv-payload).

```sh
composer require middlewares/csv-payload
```

## CsvPayload

Parses the CSV payload of the request. Uses [league/csv][link-csv] to read the CSV values. Contains the following options to configure the CSV [`Reader`][link-csv-reader] object:

#### `delimiter($delimiter)`

To configure the CSV delimiter control character (one character only). If the submitted character is invalid an `InvalidArgumentException` exception is thrown.

#### `enclosure($enclosure)`

To configure the CSV enclosure control character (one character only). If the submitted character is invalid an `InvalidArgumentException` exception is thrown.

#### `escape($escape)`

To configure the CSV escape control character (one character only). If the submitted character is invalid an `InvalidArgumentException` exception is thrown.

#### `header($header)`

To configure the CSV header line. If the submitted header value is less than 0 an `InvalidArgumentException` exception is thrown.

#### `methods(array $methods)`

To configure the allowed methods. By default only the requests with the method `POST, PUT, PATCH, DELETE, COPY, LOCK, UNLOCK` are handled.

#### `contentType(array $contentType)`

To configure all Content-Type headers used in the request. By default is `text/csv`

#### `override($override = true)`

To override the previous parsed body if exists (`false` by default)

```php
$dispatcher = new Dispatcher([
    (new \Middlewares\Tests\CsvPayload())
        ->delimiter(";")
        ->enclosure("'")
        ->escape("\\")
]);

$response = $dispatcher->dispatch(new ServerRequest());
```

---

Please see [CHANGELOG](CHANGELOG.md) for more information about recent changes and [CONTRIBUTING](CONTRIBUTING.md) for contributing details.

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

[ico-version]: https://img.shields.io/packagist/v/middlewares/csv-payload.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-ga]: https://github.com/middlewares/csv-payload/workflows/testing/badge.svg
[ico-downloads]: https://img.shields.io/packagist/dt/middlewares/csv-payload.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/middlewares/csv-payload
[link-downloads]: https://packagist.org/packages/middlewares/csv-payload
[link-payload]: https://packagist.org/packages/middlewares/payload
[link-csv]: https://packagist.org/packages/league/csv
[link-csv-reader]: http://csv.thephpleague.com/9.0/reader/
