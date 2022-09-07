<?php

namespace Stats4sd\OdkLink\Tests;

use App\Models\User;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\BackpackServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\TestCase as Orchestra;
use Stats4sd\OdkLink\OdkLinkServiceProvider;

abstract class TestCase extends Orchestra
{

    use CreatesApplication;

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
            //BackpackServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        Schema::dropAllTables();

        $migrations = [
            include __DIR__ . '/../database/migrations/1_create_xlsform_templates_table.php.stub',
            include __DIR__ . '/../database/migrations/4_create_submissions_table.php.stub',
            include __DIR__ . '/../database/migrations/3_create_xlsform_versions_table.php.stub',
            include __DIR__ . '/../database/migrations/2_create_xlsforms_table.php.stub',
            include __DIR__ . '/migrations/create_form_owners_table.php.stub',
        ];

        foreach ($migrations as $migration) {
            $migration->up();
        }

    }


}
