<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OdkProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker
        ];
    }
}
