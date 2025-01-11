# Laravel Last Modified ![test workflow](https://github.com/kudashevs/laravel-last-modified/actions/workflows/run-tests.yml/badge.svg)

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