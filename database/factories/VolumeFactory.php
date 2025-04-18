<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\Series;
use App\Models\Volume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Volume>
 */
class VolumeFactory extends Factory
{
    protected $model = Volume::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'series_id' => Series::factory(),
            'edition_id' => Edition::factory(),
            'volume_number' => $this->faker->numberBetween(1, 50),
            'total_pages' => $this->faker->numberBetween(100, 400),
            'isbn' => $this->faker->isbn13(),
            'release_date' => $this->faker->date(),
            'cover_image_url' => $this->faker->imageUrl(),
            'google_books_id' => $this->faker->uuid(),
            'buy_link' => $this->faker->url(),
        ];
    }
}
