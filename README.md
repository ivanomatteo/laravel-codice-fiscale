# Laravel Fiscal Code Validator

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ivanomatteo/laravel-codice-fiscale.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-codice-fiscale)
[![Total Downloads](https://img.shields.io/packagist/dt/ivanomatteo/laravel-codice-fiscale.svg?style=flat-square)](https://packagist.org/packages/ivanomatteo/laravel-codice-fiscale)

Laravel validator for italian fiscal code

## Installation

You can install the package via composer:

```bash
composer require ivanomatteo/laravel-codice-fiscale
```

## Usage

publish lang

``` bash
php artisan vendor:publish --provider "IvanoMatteo\LaravelCodiceFiscale\LaravelCodiceFiscaleServiceProvider"  --tag lang

```

``` php
/*
Fiscal code fields name:

    name
    familyName
    dateOfBirth
    sex
    cityCode

*/
$validated = Request::validate( [
    // first parameter: the field containing the fiscal code
    // second parameter: the corrisponding filed name for matching
    'dob' => 'required|codfisc:fiscalCode,dateOfBirth',
    'first_name' => 'required|codfisc:fiscalCode,name', 
    'last_name' => 'required|codfisc:fiscalCode,familyName',
    
    //second parameter: can be omitted if the filed name is alredy correct
    'sex' => 'required|codfisc:fiscalCode',
    'cityCode' => 'required|codfisc:fiscalCode',
    'fiscalCode' => 'required|codfisc',
]);

$validated = Request::validate( [
    'first_name' => 'required', 
    'last_name' => 'required',
    'dob' => 'required',
    'sex' => 'required',
    'cityCode' => 'required',
    
    // all rules on fiscal code
    'fiscalCode' => 'required|codfisc:first_name=name,last_name=familyName,dob=dateOfBirth,sex=sex,cityCode=cityCode',
]);

$validated = Request::validate( [    
    // ...
    // just check the format
    'fiscalCode' => 'required|codfisc',
]);

```

### Testing

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email ivanomatteo@gmail.com instead of using the issue tracker.

## Credits

- [Ivano Matteo](https://github.com/ivanomatteo)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Laravel Package Boilerplate

This package was generated using the [Laravel Package Boilerplate](https://laravelpackageboilerplate.com).