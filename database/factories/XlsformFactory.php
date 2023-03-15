<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\Xlsform;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Tests\Models\FormOwner;


class XlsformFactory extends Factory
{
    protected $model = Xlsform::class;

    public function definition(): array
    {
        return [
            'owner_id' => FormOwner::factory(),
            'owner_type' => FormOwner::class,
            'xlsform_template_id' => XlsformTemplate::factory(),
            'xlsfile' => $this->faker->file(
                __DIR__.'/../../tests/Files/',
                Storage::disk(config('odk-link.storage.xlsforms'))->path(''),
                false)
            ,
        ];

    }
}
