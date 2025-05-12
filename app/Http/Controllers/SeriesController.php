<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Http\Requests\StoreSeriesRequest;
use App\Http\Requests\UpdateSeriesRequest;
use App\Services\EditionService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSeriesRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $anilistId, EditionService $editionService)
    {
        // 1) GraphQL query
        $graphql = <<<'GQL'
        query ($mediaId: Int) {
            Media(id: $mediaId, type: MANGA) {
                coverImage { large }
                description
                chapters
                format
                genres
                status
                title { english native romaji }
                volumes
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

        // 3) Extraer autores principales (story, art, story & art)
        // Queremos descartar traductores y otros roles redundantes de la API
        $validRoles = ['story & art', 'story', 'art'];
        $mainAuthors = [];
        if (!empty($media['staff']['nodes'])) {
            foreach ($media['staff']['nodes'] as $idx => $node) {
                $role = strtolower($media['staff']['edges'][$idx]['role'] ?? '');
                foreach ($validRoles as $vr) {
                    if (str_contains($role, $vr)) {
                        $mainAuthors[] = [
                            'id' => $node['id'],
                            'name' => $node['name']['full'],
                            'role' => $vr,
                        ];
                        // Si el rol es story & art, lo tomamos como autor principal y salimos
                        if ($vr === 'story & art') {
                            break 2;
                        }
                    }
                }
            }
        }
        if (empty($mainAuthors)) {
            $mainAuthors[] = ['id' => null, 'name' => 'Unknow', 'role' => 'Author'];
        }

        // después de extraer los datos de la serie, llamamos al servicio de ediciones
        // para obtener las ediciones disponibles en español
        try {
            $esData = $editionService->fetchByRomajiAndLang(
                $media['title']['romaji'],
                'ES'
            ); // pasamos el romaji para buscar la edicion y el idioma para que lo haga en español
        } catch (\InvalidArgumentException $e) {
            // Si no encuentra la edicion o hay algun problema, marcamos $spanishTitle como null
            // para no mostrarlo

            // hacemos un log del error en consola
            Log::error('Error fetching Spanish edition: ' . $e->getMessage());
//            abort(400, $e->getMessage());
        }

        return view('series.show', [
            'media' => $media,
            'mainAuthors' => $mainAuthors,
            // en caso de que no haya una edicion para el idioma de ese manga, lo marcamos como null
            // para evitar conflictos
            'spanishTitle' => $esData['title'] ?? null,
            'editions' => $esData['editions'] ?? [],
            'general' => $esData['general'] ?? [],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Series $series)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSeriesRequest $request, Series $series)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Series $series)
    {
        //
    }
}
