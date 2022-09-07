<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AddDemoEntries extends Command
{
    public $signature = 'odk:demo';

    public $description = 'Seeds the xlsform_templates table with an example ODK form';

    public function handle(): int
    {
        $file = new UploadedFile(__DIR__ . '/../../tests/Files/ExampleOdk.xlsx', 'ExampleOdk.xlsx');

        Storage::disk(config('odk-link.storage.xlsforms'))->put('ExampleOdk.xlsx', $file);

        XlsformTemplate::factory()
            ->create([
                'title' => 'Demo ODK Form',
                'xlsfile' => $file->getClientOriginalName(),
            ]);


        return self::SUCCESS;
    }
}

