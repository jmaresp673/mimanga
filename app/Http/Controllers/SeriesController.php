<?php

namespace App\Http\Controllers;

use App\Models\Series;
use App\Http\Requests\StoreSeriesRequest;
use App\Http\Requests\UpdateSeriesRequest;
use Illuminate\Support\Facades\Http;

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
    public function show(int $anilistId)
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

        // 4) Pasar a la vista
        return view('series.show', [
            'media' => $media,
            'mainAuthors' => $mainAuthors,
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
