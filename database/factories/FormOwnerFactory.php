<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Tests\Models\FormOwner;


class FormOwnerFactory extends Factory
{
    protected $model = FormOwner::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->sentence(),
        ];

    }
}
