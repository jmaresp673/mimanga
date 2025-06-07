<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Models\Genre;
use App\Models\Series;
use App\Http\Requests\StoreSeriesRequest;
use App\Http\Requests\UpdateSeriesRequest;
use App\Services\EditionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeriesController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(array $media, array $mainAuthors, int $anilistId)
    {
        // Comprobar si la serie ya existe en la base de datos
        $existingSeries = Series::where('anilist_id', $anilistId)->first();
        if (!$existingSeries) {
            $series = new Series();
            $series->id = $anilistId;
            $series->title = $media['title']['english'] ?? $media['title']['romaji'];
            $series->romaji_title = $media['title']['romaji'];
            $series->native_title = $media['title']['native'] ?? null;
            $series->synopsis = $media['description'] ?? null;
            $series->anilist_id = $anilistId;
            $series->status = $media['status'] ?? null;
            $series->total_volumes = $media['volumes'] ?? null;
            $series->cover_image_url = $media['coverImage']['large'] ?? null;
            $series->banner_image_url = $media['bannerImage'] ?? null;
            $series->start_year = $media['startDate']['year'] ?? null;
            $series->end_year = $media['endDate']['year'] ?? null;
            $series->type = $media['format'] ?? null;

            // Guardar la serie en la base de datos
            $series->save();
            $existingSeries = $series;

            Log::info("Series created: {$series->id} - {$series->title}"); // <--- Añadir logging
        } else {
            $updated = $existingSeries->fill([
                'title' => $media['title']['english'] ?? $media['title']['romaji'],
                'romaji_title' => $media['title']['romaji'],
                'native_title' => $media['title']['native'] ?? null,
                'synopsis' => $media['description'] ?? null,
                'status' => $media['status'] ?? null,
                'total_volumes' => $media['volumes'] ?? null,
                'cover_image_url' => $media['coverImage']['large'] ?? null,
                'banner_image_url' => $media['bannerImage'] ?? null,
                'start_year' => $media['startDate']['year'] ?? null,
                'end_year' => $media['endDate']['year'] ?? null,
                'type' => $media['format'] ?? null,
            ])->isDirty();

            if ($updated) {
                $existingSeries->save();
                Log::info("Series updated: {$existingSeries->id} - {$existingSeries->title}");
            }
        }


        try {
            // Guardar los autores en la base de datos
            // id name anilist_id
            foreach ($mainAuthors as $authorData) {
                $author = Author::firstOrNew(['anilist_id' => $authorData['id']]);
                $author->fill(['name' => $authorData['name']]);
                if ($author->isDirty()) {
                    $author->save();
                    Log::info("Author created/updated: {$author->id} - {$author->name}");
                }
                $existingSeries->authors()->syncWithoutDetaching([$author->id => ['role' => $authorData['role']]]);
                Log::info("Author attached to series: {$existingSeries->id} - {$author->id} - {$authorData['role']}");
            }

            // Guardar los generos en la base de datos
            // id name
            foreach ($media['genres'] as $genreName) {
                $genre = Genre::firstOrNew(['name' => $genreName]);
                if ($genre->isDirty()) {
                    $genre->save();
                    Log::info("Genre created/updated: {$genre->id} - {$genre->name}");
                }
                $existingSeries->genres()->syncWithoutDetaching([$genre->id]);
                Log::info("Genre attached to series: {$existingSeries->id} - {$genre->id}");
            }
        } catch (\Exception $e) {
            Log::error('Error saving series: ' . $e->getMessage());
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(int $anilistId)
    {
        // 1) GraphQL query
        $graphql = <<<'GQL'
        query ($mediaId: Int) {
            Media(id: $mediaId, type: MANGA) {
                id
                coverImage { large }
                bannerImage
                description
                chapters
                format
                genres
                status
                title { english native romaji }
                volumes
                endDate {
                    year
                    month
                    day
                }
                startDate {
                    year
                    month
                    day
                }
                staff {
                    nodes { id name { full } primaryOccupations }
                    edges { role }
                }
            }
        }
        GQL;

        // 2) Ejecutar petición
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post('https://graphql.anilist.co', [
            'query' => $graphql,
            'variables' => ['mediaId' => $anilistId],
        ]);

        if ($response->failed() || $response->json('errors') !== null) {
            abort(404, 'Not Found');
        }

        $media = $response->json('data.Media');

        // extraer los autores principales de la serie
        $validRoles = ['story & art', 'story', 'art', 'illustration', 'original creator'];
        $primaryOccupations = ['mangaka', 'writer', 'illustrator'];
        $mainAuthors = [];
        if (!empty($media['staff']['nodes']) && !empty($media['staff']['edges'])) {
            $processedAuthors = [];

            foreach ($media['staff']['nodes'] as $index => $staffNode) {
                $role = strtolower($media['staff']['edges'][$index]['role'] ?? '');
                // Evitar procesar autores duplicados
                $name = strtolower($staffNode['name']['full'] ?? '');
                $id = $staffNode['id'] ?? null;
                // Verificar si ya procesamos este autor
                $authorKey = $id ?: $name;
                if (isset($processedAuthors[$authorKey])) {
                    continue;
                }

                //Descarta autores cuya primary occupation no este en la lista de primary occupations
                if (!array_intersect(
                    array_map('strtolower', $staffNode['primaryOccupations']),
                    array_map('strtolower', $primaryOccupations)
                )) {
                    continue;
                }

                $authorRoles = [];

                foreach ($validRoles as $validRole) {
                    if (str_contains($role, $validRole)) {
                        $authorRoles[] = $validRole;
                        break;
                    }
                }

                if (!empty($authorRoles)) {
                    // Eliminar roles duplicados y mantener orden
                    $uniqueRoles = array_unique($authorRoles);

                    $mainAuthors[] = [
                        'id' => $id,
                        'name' => $name,
                        'role' => implode(', ', $uniqueRoles)
                    ];

                    // Marcar autor como procesado
                    $processedAuthors[$authorKey] = true;
                }
            }
        }
        if (empty($mainAuthors)) {
            $mainAuthors[] = ['id' => null, 'name' => 'Unknow', 'role' => 'Author'];
        }
//        dd($anilistId, $media, $mainAuthors);
        // llamar al store de series, para almacenarla en db en caso de que no exista
        $this->store($media, $mainAuthors, $anilistId);

        // después de extraer los datos de la serie, llamamos al controlador de ediciones
        // para obtener las ediciones disponibles en español
        $esData = EditionController::search($media, $mainAuthors, $anilistId, 'ES');
        $enData = EditionController::search($media, $mainAuthors, $anilistId, 'EN');

//        dd($esData, $enData);
        return view('series.show', [
            'media' => $media,
            'mainAuthors' => $mainAuthors,
            'esData' => $esData ?? [],
            'enData' => $enData ?? [],
//            'general' => $esData['general'] ?? [],
//            'editions' => $esData['editions'] ?? [],
//            'spanishTitle' => $esData['title'] ?? null,
            // para evitar conflictos
            // en caso de que no haya una edicion para el idioma de ese manga, lo marcamos como null
        ]);
    }
}
