<?php

namespace Stats4sd\OdkLink\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;
use Stats4sd\OdkLink\Models\Submission;
use Stats4sd\OdkLink\Models\XlsformTemplate;
use Stats4sd\OdkLink\Models\XlsformVersion;


class SubmissionFactory extends Factory
{
    protected $model = Submission::class;

    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'uuid' => $this->faker->uuid(),
            'xlsform_version_id' => XlsformVersion::factory(),
            'submitted_at' => $this->faker->dateTimeThisYear(),
            'submitted_by' => $this->faker->name,
            'content' => json_encode(['test' => 'test']),
        ];

    }
}
