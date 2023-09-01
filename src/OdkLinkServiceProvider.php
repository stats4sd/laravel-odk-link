<?php

namespace Stats4sd\OdkLink;

use Carbon\Carbon;
use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Stats4sd\OdkLink\Commands\AddCrudPanelLinksToSidebar;
use Stats4sd\OdkLink\Commands\AddDemoEntries;
use Stats4sd\OdkLink\Commands\CreateMissingOdkProjects;
use Stats4sd\OdkLink\Commands\CreatePlatformTestProject;
use Stats4sd\OdkLink\Commands\GenerateSubmissionRecords;
use Stats4sd\OdkLink\Commands\UpdateSubmissionsToUseIntegerIds;
use Stats4sd\OdkLink\Livewire\DatasetVariable;
use Stats4sd\OdkLink\Livewire\FormStructure;
use Stats4sd\OdkLink\Livewire\RequiredDataMedia;
use Stats4sd\OdkLink\Livewire\RequiredDataMediaUploader;
use Stats4sd\OdkLink\Livewire\RequiredFixedMediaUploader;
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
                '1_create_xlsform_subjects_table',
                '2_create_xlsform_templates_table',
                '3_create_xlsforms_table',
                '4_create_xlsform_versions_table',
                '5_create_submissions_table',
                '6_create_odk_projects_table',
                '7_create_app_users_table',
                '8_create_app_user_assignments_table',
                '9_create_platforms_table',
                '10_create_required_media_media_table',
                '11_create_datasets_table',
                '12_create_odk_datasets_table',
                '13_create_odk_entities_table',
            ])
            ->hasCommands([
                AddCrudPanelLinksToSidebar::class,
                AddDemoEntries::class,
                GenerateSubmissionRecords::class,
                CreateMissingOdkProjects::class,
                UpdateSubmissionsToUseIntegerIds::class,
                CreatePlatformTestProject::class,
            ]);

    }

    public function registeringPackage(): void
    {
        $this->app->singleton(OdkLinkService::class, function ($app) {
            return new OdkLinkService(config('odk-link.odk.base_endpoint'));
        });
    }

    public function bootingPackage(): void
    {
        Livewire::component('odk-link::required-fixed-media-uploader', RequiredFixedMediaUploader::class);
        Livewire::component('odk-link::required-data-media-uploader', RequiredDataMediaUploader::class);
        Livewire::component('odk-link::required-data-media', RequiredDataMedia::class);
        Livewire::component('odk-link::dataset-variable', DatasetVariable::class);
        Livewire::component('odk-link::form-structure', FormStructure::class);


    }

    public function boot()
    {
        //handle routes manually, as we want to let the user override the package routes in the main app:
        $this->publishes([
            __DIR__ . '/../routes/odk-link.php' => base_path('routes/backpack/odk-link.php')
        ], 'odk-link-routes');

        // if the user has published the routes file, do not register the package routes.
        if (file_exists(base_path('routes/backpack/odk-link.php'))) {
            $this->loadRoutesFrom(base_path('routes/backpack/odk-link.php'));
        } else {
            $this->loadRoutesFrom(__DIR__ . '/../routes/odk-link.php');
        }

        // publish optional upgrade-migrations on separate tag
        $updateFileNames = [
            $this->package->basePath("/../database/migrations/update_submissions_table.php.stub"),
            $this->package->basePath("/../database/migrations/update_xlsform_templates_table.php.stub"),
        ];

        foreach ($updateFileNames as $updateFileName) {
            $this->publishes([
                $updateFileName => $this->generateMigrationName(
                    'update_submissions_table.php',
                    Carbon::now()->addSecond()
                ),], "{$this->package->shortName()}-migrations-v1-update-only");
        }

        // publish front-end assets compiled with Vite
        $this->publishes([
            __DIR__ . '/../public/vendor/stats4sd/laravel-odk-link' => public_path('vendor/stats4sd/laravel-odk-link'),
        ], 'assets');

        return parent::boot();
    }
}
