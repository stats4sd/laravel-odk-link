<?php

namespace Stats4sd\OdkLink\Commands;

use Hoa\Math\Visitor\Arithmetic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Stats4sd\OdkLink\Http\Controllers\Admin\SubmissionCrudController;
use Stats4sd\OdkLink\Models\Platform;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Services\OdkLinkService;
use Stats4sd\OdkLink\Traits\HasXlsforms;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class CreatePlatformTestProject extends Command
{
    public $signature = 'odk:platform';
    public $description = 'This creates a "platform" entry in the database and a corresponding ODK Project on ODK Central. This special project is owned by the platform itself, not any user or team on the platform. This project is used exclusively for Xlsform Template testing, and should never be used for live data collection.';

    public function handle(): int
    {
        // create platform entry (and automatically create ODK Project) if it doesn't exist
        if(Platform::count() === 0) {
            Platform::create();

            $this->info('Platform entry created.');
        } else {
            $this->info('Platform entry already exists.');
        }
        $this->info('The ODK project is at: ' . Platform::first()->odkProject->getOdkLink());

        return self::SUCCESS;
    }
}
