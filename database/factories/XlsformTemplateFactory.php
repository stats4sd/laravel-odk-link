<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\XlsformTemplate;


class XlsformTemplateFactory extends Factory
{
    protected $model = XlsformTemplate::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'xlsfile' => $this->faker->file(
                __DIR__.'/../../tests/Files/',
                Storage::disk(config('odk-link.storage.xlsforms'))->path(''),
                false)
            ,
            'description' => $this->faker->paragraph(),
            'media' => null,
            'csv_lookups' => null,
            'available' => $this->faker->boolean(),
            'owner_id' => null,
        ];

    }
}
