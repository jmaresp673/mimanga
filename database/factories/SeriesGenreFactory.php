<?php

namespace Database\Factories;

use App\Models\Genre;
use App\Models\Series;
use App\Models\SeriesGenre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SeriesGenre>
 */
class SeriesGenreFactory extends Factory
{
    protected $model = SeriesGenre::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'series_id' => Series::factory(),
            'genre_id' => Genre::factory(),
        ];
    }
}
