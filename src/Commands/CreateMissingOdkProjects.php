<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Traits\HasXlsforms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreateMissingOdkProjects extends Command
{
    public $signature = 'odk:create-missing';

    public $description = 'Checks through all models of the given type (should be form owners), and creates OdkProject entries if they are missing. Useful when adding this package to an existing project with existing form owners';

    public function handle(): int
    {
        //$model = $this->ask('Which model do you wish to update?');

        $classes = collect(file(base_path('vendor/composer/autoload_classmap.php', FILE_IGNORE_NEW_LINES)));

        $formOwnerClasses = [];

        // find all model classes with the HasXlsforms trait;
        foreach ($classes as $class) {
            if (Str::contains($class, 'App\\\\Models')) {

                $fqdn = Str::of($class)
                    ->before('=>')
                    ->replace("'", "")
                    ->replace('\\\\', '\\')
                    ->trim()
                    ->toString();

                if(in_array(HasXlsforms::class, class_uses_recursive($fqdn), true)) {
                    $formOwnerClasses[] = $fqdn;
                }

            }
        }

        foreach($formOwnerClasses as $class) {
            $entriesWithoutOdkProject = $class->doesntHave('odkProject')->get()
                ->each(function($owner) {
                    $owner->createLinkedOdkProject();
                });
        }


        return self::SUCCESS;
    }
}

