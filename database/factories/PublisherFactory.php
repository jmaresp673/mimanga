<?php

namespace Database\Factories;

use App\Models\Publisher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publisher>
 */
class PublisherFactory extends Factory
{
    protected $model = Publisher::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'country' => $this->faker->country,
            'website' => $this->faker->url,
            'logo_url' => $this->faker->imageUrl(),
            'founded_year' => $this->faker->year(),
        ];
    }
}
