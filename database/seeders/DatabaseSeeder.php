<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Edition;
use App\Models\Genre;
use App\Models\Publisher;
use App\Models\Series;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\UserVolume;
use App\Models\Volume;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear autores
        $authors = Author::factory(10)->create();

        // Crear géneros
        $genres = Genre::factory(5)->create();

        // Crear publishers
        $publishers = Publisher::factory(5)->create();

        // Crear series
        $seriesList = Series::factory(8)->create();

        foreach ($seriesList as $series) {
            // Asignar autores aleatorios a la serie
            $series->authors()->attach(
                $authors->random(rand(1, 3))->pluck('id')->toArray(),
                ['role' => 'Writer']
            );

            // Asignar géneros aleatorios a la serie
            $series->genres()->attach(
                $genres->random(rand(1, 2))->pluck('id')->toArray()
            );
        }

        // Crear ediciones
        $editions = Edition::factory(20)->make()->each(function ($edition) use ($seriesList, $publishers) {
            $edition->series_id = $seriesList->random()->id;
            $edition->publisher_id = $publishers->random()->id;
            $edition->save();
        });

        // Crear volúmenes
        Volume::factory(40)->make()->each(function ($volume) use ($seriesList, $editions) {
            $volume->series_id = $seriesList->random()->id;
            $volume->edition_id = $editions->random()->id;
            $volume->save();
        });

        // Crear usuario de prueba
        $user = User::factory()->create([
            'email' => 'admin@example.com',
        ]);

        // Asignar volúmenes al usuario
        $volumes = Volume::inRandomOrder()->limit(10)->get();
        foreach ($volumes as $volume) {
            UserVolume::create([
                'user_id' => $user->id,
                'volume_id' => $volume->id,
                'readed' => fake()->boolean(70),
                'page' => fake()->numberBetween(0, $volume->total_pages ?? 300),
                'purchase_date' => fake()->optional()->date(),
                'note' => fake()->optional()->text(200),
                'rating' => fake()->optional()->numberBetween(1, 5),
            ]);
        }
    }
}
