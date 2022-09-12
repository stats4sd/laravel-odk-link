<?php

namespace Stats4sd\OdkLink;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stats4sd\OdkLink\Commands\AddCrudPanelLinksToSidebar;
use Stats4sd\OdkLink\Commands\AddDemoEntries;
use Stats4sd\OdkLink\Services\OdkLinkService;

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
            ->hasMigrations([
                '1_create_xlsform_templates_table',
                '2_create_xlsforms_table',
                '3_create_xlsform_versions_table',
                '4_create_submissions_table',
                '5_create_odk_projects_table',
                '6_create_app_users_table',
                '7_create_app_user_assignments_table',
            ])
            ->hasRoute('/odk-link')
            ->hasCommands([
                AddCrudPanelLinksToSidebar::class,
                AddDemoEntries::class,
            ]);

    }

    public function registeringPackage(): void
    {
        $this->app->singleton(OdkLinkService::class, function ($app) {
            return new OdkLinkService(config('odk-link.odk.base_endpoint'));
        });
    }
}
