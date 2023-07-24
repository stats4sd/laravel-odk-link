<?php

namespace Stats4sd\OdkLink\Tests;

use App\Models\User;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Backpack\CRUD\BackpackServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\Concerns\InteractsWithViews;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\CreatesApplication;
use Orchestra\Testbench\TestCase as Orchestra;
use Stats4sd\OdkLink\OdkLinkServiceProvider;
use Stats4sd\OdkLink\Services\OdkLinkService;

abstract class TestCase extends Orchestra
{

    //use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => '\\Stats4sd\\OdkLink\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );

        app()->bind(OdkLinkService::class, fn() => new FakeOdkLinkService('/'));
    }

    protected function getPackageProviders($app): array
    {
        return [
            OdkLinkServiceProvider::class,
            BackpackServiceProvider::class,
            LivewireServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        Schema::dropAllTables();

        $migrationFiles = scandir(__DIR__ . '/../database/migrations/');

        foreach ($migrationFiles as $migrationFile) {
            if (!in_array($migrationFile, ['.', '..'])) {
                $migration = include __DIR__ . "/../database/migrations/$migrationFile";
                $migration->up();
            }
        }

        $testMigrationFiles = scandir(__DIR__ . '/migrations');

        foreach($testMigrationFiles as $testMigrationFile) {
            if(!in_array($testMigrationFile, ['.', '..'])) {
                $migration = include __DIR__ . "/migrations/$testMigrationFile";
                $migration->up();
            }
        }

    }

    public function setupAdminUser(): Models\User
    {
        return Models\User::create([
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
