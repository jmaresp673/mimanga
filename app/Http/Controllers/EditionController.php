<?php

namespace App\Http\Controllers;

use App\Models\Edition;
use App\Http\Requests\StoreEditionRequest;
use App\Http\Requests\UpdateEditionRequest;
use App\Models\Publisher;
use App\Models\Volume;
use App\Services\EditionService;
use DateTime;
use ErrorException;
use Illuminate\Support\Facades\Log;
use PhpParser\Error;

class EditionController extends Controller
{
    /**
     * Busca la edición mediante @param EditionService $editionService
     *
     * El id de cada edición se define con: anilist_id + Lang, ej: 123456ES
     * con este buscamos si existe la edición, si no existe la creamos.
     */
    public static function search(array $media, array $mainAuthors, int $anilistId, string $lang): array
    {
        try {
            $data = (new EditionService)->fetchByRomajiAndLang(
                $media['title']['native'],
                $lang,
                $media['title']['romaji'],
                $media['format'] ?? 'MANGA'
            ); // pasamos el romaji para buscar la edicion y el idioma
        } catch (ErrorException $e) {
            // Si no encuentra la edicion o hay algun problema, marcamos $spanishTitle como null
            // para no mostrarlo

            // hacemos un log del error en consola
            Log::error('Error fetching Spanish edition: ' . $e->getMessage());
        }

        // Si no existe la edicion, la creamos
        //id series_id localized_title	publisher_id language edition_total_volumes format country_code

        // Comprobar que se ha encontrado una edición en español y no está en null
        if (isset($data) && !empty($data)) {

            try {
                // Comporbar si existe la editora en la base de datos
                //	id	name country website
                $existingPublisher = Publisher::where('name', $data['general']['localized_publisher'])->first();
                if (!$existingPublisher) {
                    $publisher = new Publisher();
                    $publisher->name = $data['general']['localized_publisher']['name'];
                    $publisher->country = $lang;
                    $publisher->website = $data['general']['localized_publisher']['web'] ?? null;

                    $publisher->save();
                    $existingPublisher = $publisher;

                    Log::info("Publisher created: {$publisher->id} - {$publisher->name}"); // <--- Añadir logging
                } else {
                    $updatedPublisher = $existingPublisher->fill([
                        'name' => $data['general']['localized_publisher']['name'],
                        'country' => $lang,
                        'website' => $data['general']['localized_publisher']['web'] ?? null,
                    ])->isDirty();

                    if ($updatedPublisher) {
                        $existingPublisher->save();
                        Log::info("Publisher updated: {$existingPublisher->id} - {$existingPublisher->name}"); // <--- Añadir logging
                    }
                }
            } catch (ErrorException $e) {
                Log::error('Error saving publisher: ' . $e->getMessage());
            }

            try {
                $editionId = $anilistId . $lang;
                $existingEdition = Edition::where('id', $editionId)->first();
                if (!$existingEdition) {
                    $edition = new Edition();
                    $edition->id = $editionId;
                    $edition->series_id = $anilistId;
                    $edition->localized_title = $data['title'];
                    $edition->publisher_id = $existingPublisher->id;
                    $edition->language = $lang;
                    $edition->edition_total_volumes = $data['general']['numbers_localized'];
                    $edition->format = $data['general']['format'] ?? 'MANGA';
                    $edition->type = $data['general']['type'] ?? 'MANGA';
                    $edition->country_code = $lang;

                    $edition->save();
                    $existingEdition = $edition;

                    Log::info("Edition created: {$editionId} - {$existingEdition->localized_title}"); // <--- Añadir logging
                } else {
                    $updatedEdition = $existingEdition->fill([
                        'series_id' => $anilistId,
                        'localized_title' => $data['title'],
                        'publisher_id' => $existingPublisher->id,
                        'language' => $lang,
                        'edition_total_volumes' => $data['general']['numbers_localized'],
                        'format' => $data['general']['format'] ?? 'MANGA',
                        'type' => $data['general']['type'] ?? 'MANGA',
                        'country_code' => $lang,
                    ])->isDirty();

                    if ($updatedEdition) {
                        $existingEdition->save();
                        Log::info("Edition updated: {$$editionId} - {$existingEdition->localized_title}"); // <--- Añadir logging
                    }
                }
            } catch (ErrorException $e) {
                Log::error('Error saving edition: ' . $e->getMessage());
            }

            try {
                // LLamar al controlador de volumenes para almacenarlos en la base de datos
                // id series_id edition_id volume_number total_pages isbn price release_date cover_image_url
                foreach ($data['editions'] as $volumeData) {
                    // comprueba si volumenData tiene fecha a nulo, si la tiene es que es un volumen aun
                    // no editado/publicado, salta el volumen
                    if (!$volumeData['fecha']) {
                        continue;
                    }
                    $existingVolume = Volume::where('series_id', $anilistId)
                        ->where('edition_id', $editionId)
                        ->where('volume_number', $volumeData['volumen'])
                        ->first();
//                    dd($anilistId, $editionId,$volumeData['precio'], $volumeData['fecha']);
                    if (!$existingVolume) {
                        $volume = new Volume();
                        $volume->series_id = $anilistId;
                        $volume->edition_id = $editionId;
                        $volume->volume_number = $volumeData['volumen'];
                        $volume->total_pages = $volumeData['paginas'];
                        $volume->price = $volumeData['precio'];
                        $volume->release_date = $volumeData['fecha'];
                        $volume->cover_image_url = $volumeData['portada'];

//                        dd($data['editions'][0], $volumeData, $volume);
                        $volume->save();
                        Log::info("Volume created: {$volume->id} / {$volume->edition->localized_title} - {$volume->volume_number}");
                    } else {
                        $updatedVolume = $existingVolume->fill([
                            'total_pages' => $volumeData['paginas'],
                            'price' => $volumeData['precio'],
                            'release_date' => $volumeData['fecha'],
                            'cover_image_url' => $volumeData['portada'],
                        ])->isDirty();

                        if ($updatedVolume) {
                            $existingVolume->save();
                            Log::info("Volume updated: {$existingVolume->id} / {$existingVolume->edition->localized_title} - {$existingVolume->volume_number}");
                        }
                    }
                }
            } catch (ErrorException $e) {
                Log::error('Error saving volume: ' . $e->getMessage());
            }
        }
        return $data ?? [];
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
    public function store(StoreEditionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // Comprobar si la edición existe en la base de datos
//        $edition = Edition::where('id', $id)->first();
//        if (!$edition) {
//            return response()->json(['message' => 'Edition not found'], 404);
//        }
//
//        return response()->json($edition, 200);
//    }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Edition $edition)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEditionRequest $request, Edition $edition)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Edition $edition)
    {
        //
    }
}
