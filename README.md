# Laravel Last Modified

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kudashevs/laravel-last-modified.svg)](https://packagist.org/packages/kudashevs/laravel-last-modified)
[![Run Tests](https://github.com/kudashevs/laravel-last-modified/actions/workflows/run-tests.yml/badge.svg)](https://github.com/kudashevs/laravel-last-modified/actions/workflows/run-tests.yml)
[![License MIT](https://img.shields.io/badge/License-MIT-green.svg)](LICENSE.md)

This Laravel package contains a handler for the If-Modified-Since request and Last-Modified response headers. 


## Installation

You can install the package via composer:
```bash
composer require kudashevs/laravel-last-modified
```

Then, register the middleware in the `app/Http/Kernel.php`:
```php
protected $middleware = [
    'web' => [
        ...
        \Kudashevs\LaravelLastModified\Middleware\LastModified::class,
    ],
];
```

You may also want to publish the configuration file (optional).
```php
php artisan vendor:publish --provider="Kudashevs\LaravelLastModified\Providers\LastModifiedServiceProvider"
```

After deploying your project, [check whether the headers are handled correctly](https://en.web-tool.org/check-last-modified/). 


## How it works

First, the middleware identifies the last modification time of a given url by parsing the request. It checks the following
origins: a first model in the view data, a first collection in the view data, a compiled view file, a view file, as a last
resort, a fallback. When the last modification time has been identified, the middleware sets the Last-Modified response header.

Then, it handles the `If-Modified-Since` request header using the data retrieved in the previous step.


## Configuration

After [publishing](#installation), the configuration settings are located in the `config/last-modified.php` file.

There configuration options are currently supported:
```
'enable'               # A boolean defines whether the middleware is enabled (default `true`).
'aggressive'           # A boolean defines whether the middleware returns a response immediately (default is `false`).
'fallback'             # A string defines the fallback time fot the Last-Modifier header.
```
, for more information please see the [configuration](config/last-modified.php) file.


## Testing

```bash
composer test
```


## References

- [RFC 7232: HTTP/1.1 Conditional Requests](https://datatracker.ietf.org/doc/html/rfc7232#section-3.3)
- [MDN If-Modified-Since](https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/If-Modified-Since)


## Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

 **Note:** Please make sure to update tests as appropriate.


## License

The MIT License (MIT). Please see the [License file](LICENSE.md) for more information.