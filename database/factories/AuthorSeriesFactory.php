<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\AuthorSeries;
use App\Models\Series;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuthorSeries>
 */
class AuthorSeriesFactory extends Factory
{
    protected $model = AuthorSeries::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'series_id' => Series::factory(),
            'role' => $this->faker->randomElement(['Writer', 'Illustrator', 'Author']),
        ];
    }
}
