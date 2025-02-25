# Simple Slim DTO

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pepperfm/ssd-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/ssd-for-laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/pepperfm/ssd-for-laravel.svg?style=flat-square)](https://packagist.org/packages/pepperfm/ssd-for-laravel)
![GitHub Actions](https://github.com/pepperfm/ssd-for-laravel/actions/workflows/main.yml/badge.svg)

Easily describe your data with minimal boilerplate.
No magic, no transformations — just pure, readable schema.

Typehint your data in camelCase.
No hidden transformations or data mutations.
Clean, readable, and intuitive code.
Just a simple class to map your data, following the KISS💋 principle.

## Installation

You can install the package via composer:

```bash
composer r pepperfm/ssd-for-laravel
```

## Usage

Extends your class from BaseDto and describe your data
```php
class ResponseWrapperDto extends BaseDto
{
    public array $data;

    public array $links;

    public array $metaData;
}
```

### Base case
Then you can create the object like this:
```php
ResponseWrapperDto::make([
    'data' => $response['data'],
    'links' => $response['links'],
    'meta_data' => $response['meta'],
])
```
```php
use Pepperfm\DonationAlerts\Attributes\ToIterable;

class ResponseWrapperDto extends BaseDto
{
    #[ToIterable(ResponseDataDto::class)]
    public array $data;

    public array $links;

    public array $metaData;
}
```
Output:
```php
/**
 * @var array<array-key, ResponseDataDto> $data 
 */
$data = $dto->data;
```

### Extended cases
```php
ResponseWrapperDto::make(literal(
    data: $response['data'],
    links: $response['links'],
    meta: $response['meta'],
))
```
```php
new ResponseWrapperDto(
    data: $response['data'],
    links: $response['links'],
    meta: $response['meta'],
)
```
```php
ResponseWrapperDto::make([
    'data' => $response['data'],
    'links' => $response['links'],
    'meta' => $response['meta'],
])
```
```php
new ResponseWrapperDto([
    'data' => $response['data'],
    'links' => $response['links'],
    'meta' => $response['meta'],
])
```
---
> [!WARNING]
> Only for collections (non-native array)

```php
use Pepperfm\DonationAlerts\Attributes\ToIterable;

class ResponseWrapperDto extends BaseDto
{
    #[ToIterable(ResponseDataDto::class, \Illuminate\Database\Eloquent\Collection::class)]
    public \Illuminate\Support\Collection $data;

    public array $links;

    public array $metaData;
}
```
Output:
```php
/** @var \Illuminate\Database\Eloquent\Collection<array-key, ResponseDataDto> $data */
$data = $dto->data;
```

> [!NOTE]
> In this version this package will transform any snake_case variables to camelCase
>
> I started with this because I like this approach

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email Damon3453@yandex.ru instead of using the issue tracker.

## Credits

-   [Dmitry Gaponenko](https://pepperfm.com)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).
