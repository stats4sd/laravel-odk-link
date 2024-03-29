<?php

namespace Stats4sd\OdkLink\Commands;

use Hoa\Math\Visitor\Arithmetic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stats4sd\OdkLink\Http\Controllers\Admin\SubmissionCrudController;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Stats4sd\OdkLink\Traits\HasXlsforms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UpdateSubmissionsToUseIntegerIds extends Command
{
    public $signature = 'odk:upgrade';
    public $description = 'This is a 1-time change to migrate existing submission data to use integers as IDs instead of strings. This is required for applications when upgrading.';

    public function handle(): int
    {
        // add specific submission update migration
        Artisan::call("vendor:publish --tag=odk-link-migrations-v1-update-only");


        // setup Spatie Media Library
        Artisan::call('vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="migrations"');
        Artisan::call('migrate');
        Artisan::call('vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="config"');

        return self::SUCCESS;
    }
}
