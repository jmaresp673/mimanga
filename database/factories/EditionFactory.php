<?php

namespace Database\Factories;

use App\Models\Edition;
use App\Models\Publisher;
use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Edition>
 */
class EditionFactory extends Factory
{
    /**
     *
     * @var string
     */
    protected $model = Edition::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->randomNumber(8) . $this->faker->randomElement(['ES', 'EN']),
            'series_id' => Series::factory(),
            'localized_title' => $this->faker->sentence(3),
            'publisher_id' => Publisher::factory(),
            'language' => $this->faker->randomElement(['ES', 'EN', 'JA']),
            'edition_total_volumes' => $this->faker->numberBetween(1, 50),
            'format' => $this->faker->randomElement(['Tankobon', 'Bunko', 'Kanzenban', 'Tapa blanda', 'Tapa dura']),
            'country_code' => $this->faker->countryCode,
        ];
    }
}
