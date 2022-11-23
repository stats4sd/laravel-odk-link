# Laravel ODK Link 
[![Latest Version on Packagist](https://img.shields.io/packagist/v/stats4sd/laravel-odk-link.svg?style=flat-square)](https://packagist.org/packages/stats4sd/laravel-odk-link)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/stats4sd/laravel-odk-link/run-tests?label=tests)](https://github.com/stats4sd/laravel-odk-link/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/stats4sd/laravel-odk-link/Check%20&%20fix%20styling?label=code%20style)](https://github.com/stats4sd/laravel-odk-link/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/stats4sd/laravel-odk-link.svg?style=flat-square)](https://packagist.org/packages/stats4sd/laravel-odk-link)

This package enables you to connect your Laravel application to an ODK Central server, and to manage the deployment of ODK forms via a set of CRUD panels built with Laravel Backpack.

The benefit of this package is that it allows you to create a set of "xlsform templates", and then deploy many versions of each template to different "owners". An owner could be an individual user, or a team. This way, multiple groups can work independently and keep their data separate, while still using the same "xlsform templates", and enabling a central team to reeview data from all teams.  

> NOTE: This is still in development. It currently requires Laravel Backpack Pro, which is a paid product. We hope to be able to refactor this to not require a Backpack Pro licence in the future. 

## Installation

This package assumes you have already setup your project to use Laravel Backpack. If you haven't, please do so by following their installation guide: https://backpackforlaravel.com/docs/5.x/installation. 

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

This package comes with a set of CRUD panels designed to help you manage ODK forms and submissions. To add links to these into your Backpack Admin panel sidebar, run the included command:

```bash
php artisan odk:crud
```

You also need to add some Environment variables to your project. In your .env file, add the following:

```dotenv

### REQUIRED
# url of your ODK Central server
ODK_URL="https://example-odk-central-server.com"

# username and password of an administrator account for your ODK Central server
ODK_USERNAME="an-admin-user-account"
ODK_PASSWORD="your-password"

### OPTIONAL
# the FQDN of the PHP class in your application that holds the functions that process data 
DATA_PROCESSING_CLASS="\\App\\Services\\DatamapService"

# the name of the route that submission data should be POSTED to when ODK submissions are retrieved.
SUBMISSION_PROCESS_ENDPOINT="name.of.route"
```

## Use
TODO: write up full documentation.



## Credits

- [David Mills](https://github.com/dave-mills)

#TODO
- [All Contributors](../../contributors)

## License
#TODO
The MIT Licence (MIT). Please see [Licence File](LICENSE.md) for more information.
