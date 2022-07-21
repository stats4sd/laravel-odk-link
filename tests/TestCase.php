<?php

namespace Stats4sd\OdkLink\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Stats4sd\OdkLink\OdkLinkServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => '\\Stats4sd\\OdkLink\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            OdkLinkServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        Schema::dropAllTables();

        $migrations = [
            include __DIR__ . '/../database/migrations/create_xlsform_templates_table.php.stub',
            include __DIR__ . '/../database/migrations/create_submissions_table.php.stub',
            include __DIR__ . '/../database/migrations/create_xlsform_versions_table.php.stub',
            include __DIR__ . '/../database/migrations/create_xlsforms_table.php.stub',
            include __DIR__ . '/migrations/create_form_owners_table.php.stub',
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }

    }
}
