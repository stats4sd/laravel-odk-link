<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Models\XlsformVersion;
use Stats4sd\OdkLink\Tests\Models\FormOwner;


class XlsformVersionFactory extends Factory
{
    protected $model = XlsformVersion::class;

    public function definition(): array
    {
        return [
            'xlsform_id' => Xlsform::factory(),
            'version' => $this->faker->word(),
            'odk_version' => $this->faker->word(),
            'xlsfile' => $this->faker->file(
                __DIR__ . '/../../tests/Files/',
                Storage::disk(config('odk-link.storage.xlsforms'))->path(''),
                false)
            ,
        ];

    }
}
