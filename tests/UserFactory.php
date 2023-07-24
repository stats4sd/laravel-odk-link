<?php

namespace Stats4sd\OdkLink\Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\Factories\UserFactory as TestbenchUserFactory;
use Stats4sd\OdkLink\Tests\Models\User;

class UserFactory extends TestbenchUserFactory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'remember_token' => Str::random(10),
        ];
    }
}
