<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserVolume;
use App\Models\Volume;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserVolume>
 */
class UserVolumeFactory extends Factory
{
    protected $model = UserVolume::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'volume_id' => Volume::factory(),
            'readed' => $this->faker->boolean(),
            'page' => $this->faker->numberBetween(0, 400),
            'purchase_date' => $this->faker->optional()->date(),
            'note' => $this->faker->optional()->paragraph(),
            'rating' => $this->faker->optional()->numberBetween(1, 5),
        ];
    }
}
