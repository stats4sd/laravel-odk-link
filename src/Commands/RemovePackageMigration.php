<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Stats4sd\OdkLink\OdkLinkServiceProvider;

class RemovePackageMigration extends Command
{
    public $signature = 'odk:remove-migrations';

    public $description = 'Removes the migration files published by this package. Intended for testing the package.';

    public function handle(): int

    {
        $this->info('Removing migrations...');

        // get list of files from inside src/database/migrations
        $migrationFileNames = collect(glob(__DIR__. '/../../database/migrations/*'))
            ->map(function ($file) {
                return basename($file);
            });


        foreach ($migrationFileNames as $migrationFileName) {
            $this->info("Removing $migrationFileName");

            // find the file that contains the $migrationFileName text
            $migrationFile = collect(glob(database_path('migrations/*')))
                ->filter(function ($file) use ($migrationFileName) {
                    // remove ".stub" from $migrationFileName
                    $migrationFileName = str_replace('.stub', '', $migrationFileName);

                    return str_contains($file, $migrationFileName);

                })
                ->first();

            // delete the file
            if ($migrationFile) {
                unlink($migrationFile);
            }

        }



        return self::SUCCESS;
    }
}
