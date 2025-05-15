<?php

namespace Database\Factories;

use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Series>
 */
class SeriesFactory extends Factory
{
    protected $model = Series::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'romaji_title' => $this->faker->sentence(3),
            'native_title' => $this->faker->sentence(3),
            'synopsis' => $this->faker->paragraph(),
            'anilist_id' => $this->faker->unique()->numberBetween(10000, 99999),
            'status' => $this->faker->randomElement(['FINISHED', 'RELEASING', 'HIATUS']),
            'total_volumes' => $this->faker->numberBetween(1, 50),
            'cover_image_url' => $this->faker->imageUrl(),
            'banner_image_url' => $this->faker->imageUrl(),
            'start_year' => $this->faker->year(),
            'end_year' => $this->faker->optional()->year(),
            'type' => $this->faker->randomElement(['MANGA', 'NOVEL', 'ONE_SHOT']),
        ];
    }
}
