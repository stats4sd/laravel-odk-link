<?php

namespace Stats4sd\OdkLink;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stats4sd\OdkLink\Commands\OdkLinkCommand;

class OdkLinkServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-odk-link')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_xlsform_templates_table.php.stub')
            ->hasMigration('create_xlsforms_table.php.stub')
            ->hasMigration('create_submissions_table.php.stub')
            ->hasCommand(OdkLinkCommand::class);
    }
}
