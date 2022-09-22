# Laravel ODK Link 
- A version 2.0 update to the Kobo Link package, intended to be easier to set up and more flexible by allowing the user to choose between multiple ODK Aggregate services.

> # NOTE: This is still in development. Currently it requires Laravel Backpack Pro, which is a paid product. We are refactoring this to use the free version of Backpack to enable us to share this properly with an open licence.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stats4sd/laravel-odk-link.svg?style=flat-square)](https://packagist.org/packages/stats4sd/laravel-odk-link)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stats4sd/laravel-odk-link/run-tests?label=tests)](https://github.com/stats4sd/laravel-odk-link/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stats4sd/laravel-odk-link/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stats4sd/laravel-odk-link/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stats4sd/laravel-odk-link.svg?style=flat-square)](https://packagist.org/packages/stats4sd/laravel-odk-link)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-odk-link.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-odk-link)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require stats4sd/laravel-odk-link
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="odk-link-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="odk-link-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="odk-link-views"
```

## Usage

```php
$odkLink = new Stats4sd\OdkLink();
echo $odkLink->echoPhrase('Hello, Stats4sd!');
```

## Testing

```bash
composer test
```


## Contributing
#TODO


## Security Vulnerabilities
#TODO


## Credits

- [David Mills](https://github.com/dave-mills)

#TODO
- [All Contributors](../../contributors)

## License
#TODO
The MIT Licence (MIT). Please see [Licence File](LICENSE.md) for more information.
