<?php

namespace Stats4sd\OdkLink\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\XlsformTemplate;

class AddDemoEntries extends Command
{
    public $signature = 'odk:demo';

    public $description = 'Seeds the xlsform_templates table with an example ODK form';

    public function handle(): int
    {
        $file = file_get_contents(__DIR__ . '/../../tests/Files/ExampleOdk.xlsx');

        if(Storage::disk(config('odk-link.storage.xlsforms'))->exists('ExampleOdk.xlsx')) {
            Storage::disk(config('odk-link.storage.xlsforms'))->delete('ExampleOdk.xlsx');
        }

        Storage::disk(config('odk-link.storage.xlsforms'))->put('ExampleOdk.xlsx', $file);

        XlsformTemplate::create([
                'title' => 'Demo ODK Form',
                'xlsfile' => 'ExampleOdk.xlsx',
            ]);


        return self::SUCCESS;
    }
}
