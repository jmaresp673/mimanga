<?php

namespace App\Services;

use DateTime;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use InvalidArgumentException;

class EditionService
{
    protected array $supported = ['ES', 'EN'];

    /**
     * Fetch ediciones de una serie dado su título en KANJI y el idioma.
     *
     * @param string $native
     * @param string $romaji
     * @param string $lang Dos letras: ES / EN
     * @param string $type Tipo de serie: manga, novela
     * @return array  ['title' => string, 'editions' => array, 'general' => array]
     * @throws InvalidArgumentException
     */
    public function fetchByRomajiAndLang(string $native, string $lang, string $romaji, string $type): array
    {
        $lang = strtoupper($lang);
        if (!in_array($lang, $this->supported, true)) {
            throw new InvalidArgumentException("Idioma no soportado: {$lang}");
        }

        if ($lang === 'ES') {
            return $this->fetchSpanish($native, $romaji, $type);
        }

        // placeholder para EN
        throw new InvalidArgumentException("Lógica para '{$lang}' aún no implementada.");
    }


    /**
     * Obtiene la edición segun tipo e idioma (MANGA|NOVEL|MANHWA).
     *
     * @param array $collections Array de ['id'=>int, 'title'=>string, 'url'=>string]
     * @param string $type 'MANGA' o 'NOVEL'
     * @return array|null
     */
    protected function selectCollection(array $collections, string $type): ?array
    {
        // 1) Eliminar "Anime Comic(s)"
        $filtered = array_filter($collections, fn($c) => !preg_match('/Anime\s+Comics?/i', $c['title']));

        // 2) Descarta Català
//        $hasCast = collect($filtered)->contains(fn($c) => str_contains($c['title'], '(Castellano)'));
//        if ($hasCast) {
        $filtered = array_filter($filtered, fn($c) => !str_contains($c['title'], '(Català)'));
//        }

        // 3) Filtrar por type en paréntesis según MANGA, MANHWA, NOVELA(S)
        $typeMap = [
            'MANGA' => ['manga', 'manhwa'],
            'NOVEL' => ['novela', 'novelas'],
        ];
        $keywords = $typeMap[$type] ?? [];
//        dd($keywords);
        if (!empty($keywords && $keywords[0] === 'novela')) {
            $matches = array_filter($filtered, function ($c) use ($keywords) {
                foreach ($keywords as $n) {
                    if (preg_match('/\(' . preg_quote(ucfirst($n), '/') . '\)/i', $c['title'])) {
                        return true;
                    }
                }
                return false;
            });
//            dd( "colecciones", $matches);

            if (!empty($matches)) {
                $filtered = $matches;
            }
        }

//        dd($filtered);
        // 4) Si quedan varias, elegir la de título más corto
        if (!empty($filtered)) {
            usort($filtered, function ($a, $b) {
                $cleanA = trim(preg_replace('/\s*\([^)]*\)/', '', $a['title']));
                $cleanB = trim(preg_replace('/\s*\([^)]*\)/', '', $b['title']));
                return strlen($cleanA) <=> strlen($cleanB);
            });
            return array_shift($filtered);
        }
//        dd($filtered);
        return null;
    }


    /**
     * Lógica de scraping de ediciones en español.
     */
    protected function fetchSpanish(string $native, string $romaji, string $type): array
    {
//         Limpiar romaji: eliminar signos y paréntesis
        $cleanRomaji = preg_replace('/[^A-Za-z0-9\s]/', '', $romaji);
        $cleanRomaji = preg_replace('/\s+/', ' ', $cleanRomaji);
        $romaji = trim($cleanRomaji);
//        dd($romaji);

        // Limpiar kanji o nativo y elimina "。"
//        $cleanNative = preg_replace('/[-]/', '', $native);
        $native = preg_replace('/\s+/', ' ', $native);
        $native = preg_replace('/。/', '', $native);
//        dd($native);


        //
        // Llamada AJAX para scrappear el buscador de ListadoManga
        //
        try {
            $resp = Http::get('https://www.listadomanga.es/buscar.php', ['b' => $native]);
            if ($resp->failed()) {
                abort(502, 'No se pudo conectar al buscador de ListadoManga.');
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // lanzar 404
            abort(502, 'Ha ocurrido un error, por favor intentelo de nuevo mas tarde.');
        }

        $json = $resp->json();

        // Normalizar formato: mantener id, title, url
        $colecciones = array_map(fn($item) => [
            'id' => (int)$item['id'],
            'title' => trim($item['nombre']),
            'url' => 'https://www.listadomanga.es/coleccion.php?id=' . $item['id'],
        ], $json['colecciones']);
//        dd($colecciones);

        // Seleccionar la mejor opcion
        $best = $this->selectCollection($colecciones, strtoupper($type));
//        dd($best);
        $colecciones = $best ? $best : []; // si no hay coincidencias, devolver un array vacío

        if (empty($colecciones)) {
            return $colecciones;
//            abort(404, 'No se encontró la serie en ListadoManga.es');
        }

        $collectionId = $colecciones['id'];
        $spanishTitle = $colecciones['title'];
        $detailUrl = $colecciones['url'];

        // Nos quedamos con la primera colección
//        $first = $colecciones[0];
//        $collectionId = $first['id'];
//        $spanishTitle = $first['nombre'];
//        dd($spanishTitle, $collectionId);


        $jar = CookieJar::fromArray([
            'mostrarNSFW' => 'true',
        ], '.listadomanga.es');
        //
        // Scrapear la ficha de colección
        //
        try {
            $html = Http::withOptions([
                'cookies' => $jar,
                'headers' => [
                    'Referer' => 'https://www.listadomanga.es/buscador.php',
                    'User-Agent' => 'Mozilla/5.0 (compatible; MiScraper/1.0)',
                ],
            ])->get($detailUrl)->body();
//        $html = Http::get($detailUrl)->body();
//        dd($html);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // lanzar error de servidor
            abort(502, 'Ha ocurrido un error, por favor intentelo de nuevo mas tarde.');
        }

        $crawler = new Crawler($html);

        try {
            // Bloque de datos generales (primera tabla ventana* con <h2> título)
//            dd($crawler);
            $infoHtml = $crawler
                ->filter('table[class^="ventana"]')
                ->eq(0)
                ->html();
        } catch (InvalidArgumentException $e) {
            @dd('Error en el procesamiento html: ' . $e->getMessage());
        }


        ///////// DATOS EDICION //////////
        ///
        // Extraemos con expresiones regulares los campos
        preg_match('/<h2>\s*(.*?)\s*<\/h2>/si', $infoHtml, $mTitle);                                                // Título en español
        preg_match('/<b>Editorial (?:japonesa|surcoreana):<\/b>\s*<a[^>]*>\s*(.*?)\s*<\/a>/si', $infoHtml, $mJPub);                // Editorial japonesa
        preg_match('/<b>Editorial espa(?:&ntilde;|ñ)ola:<\/b>\s*<a\s+href="[^"]*"[^>]*>([^<]+)<\/a>\s*<a\s+href="([^"]+)"[^>]*>/si', $infoHtml, $mES);  // Editorial española (nombre y web oficial)
        preg_match('/<b>Formato:<\/b>\s*(.*?)(?=<br)/si', $infoHtml, $mFormat);                                     // Formato y dirección de lectura
        preg_match('/<b>Sentido de lectura:<\/b>\s*(.*?)(?=<br)/si', $infoHtml, $mDirection);                       // Sentido de lectura
        preg_match('/<b>Números en japonés:<\/b>\s*(\d+)/si', $infoHtml, $mJNums);                                  // Números editados en japonés y español
        preg_match('/<b>Números en castellano:<\/b>\s*(\d+)(?:.*?)(?=<br|<\/td>)/si', $infoHtml, $mSNums);
//        dd($mTitle, $mJPub, $mES, $mFormat, $mDirection, $mJNums, $mSNums);

        $general = [
            'title' => trim($mTitle[1] ?? ''),
            'original_title' => $romaji, // Usamos la variable existente
            'native_title' => $native, // Título en japonés / coreano
            'jp_publisher' => trim($mJPub[1] ?? ''), // Solo el nombre
            'localized_publisher' => [
                'name' => trim($mES[1] ?? ''), // Nombre de la editorial
                'web' => trim($mES[2] ?? '') // Primer enlace (web oficial)
            ],
            'format' => trim($mFormat[1] ?? ''),
            'type' => $type, // MANGA o NOVELA
            'reading_direction' => trim($mDirection[1] ?? ''),
            'numbers_jp' => $mJNums[1] ?? '',
            'numbers_localized' => $mSNums[1] ?? '',
        ];
//                @dd($general);


//////////  DATOS VOLUMENES ///////////

        $nodes = $crawler->filter('table[style*="width: 184px"][class^="ventana"]');
//        dd($nodes->count());

        try {
            // Convertir los nodos a elementos DOM independientes
            $domElements = $nodes->getIterator()->getArrayCopy();

            $editions = array_map(function ($domElement) use ($general) {
                $table = new Crawler($domElement);
                $cell = $table->filter('td.cen');

                // Validar elementos esenciales antes de procesar
                if ($cell->count() === 0) return null;
                $imgNode = $cell->filter('img.portada');
                if ($imgNode->count() === 0) return null;

                // Extraer portada
                $img = $imgNode->attr('src');

                // Procesar texto crudo
                $raw = $cell->html();
                $cleanText = strip_tags(preg_replace('/<a[^>]*>.*?<\/a>/', ' ', $raw), '<br>');
//                $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));
//                dd($cleanText);

                // Extraer datos
                if ($general['numbers_localized'] === '1') {
                    $mVol[1] = 1;
                } else {
                    preg_match('/nº?\s*(\d+)/i', $cleanText, $mVol);
                }
                preg_match('/(\d+)\s*p(?:á|a)ginas/i', $cleanText, $mPages);
                preg_match('/(\d+,\d{2})\s*€/i', $cleanText, $mPrice);
//                @dd($img, $mVol, $mPages, $mPrice[1]);

                // Procesar fecha de edición (día + mes año)
                $date = null;

                // en html: 29 <a ...>Julio 2022</a>
                if (preg_match('/(\d+)\s*<a[^>]*>\s*([A-Za-z]+)\s+(\d{4})\s*<\/a>/', $raw, $mDate)) {
                    $day = (int)$mDate[1];
                    $month = ucfirst(strtolower(trim($mDate[2])));
                    $year = (int)$mDate[3];

                    $months = [
                        'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                        'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                        'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
                    ];

                    if (isset($months[$month])) {
                        try {
                            $date = DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $months[$month], $day))
                                ->format('Y-m-d');
                        } catch (\Exception $e) {
                            $date = null;
                        }
                    }
                }


//                dd($mVol, $mPages, $mPrice, $date, $img);
                return [
                    'volumen' => $mVol[1] ?? null,
                    'paginas' => $mPages[1] ?? null,
                    'precio' => $mPrice[1] ?? null,
                    'fecha' => $date,
                    'portada' => $img,
                ];
            }, $domElements);

//            dd($editions);
            // Filtrar y formatear resultados
            $editions = array_values(array_filter($editions, function ($item) {
                return $item !== null && $item['volumen'] !== null && $item['portada'] !== null;
            }));

//            @dd($editions);

        } catch (InvalidArgumentException $e) {
            @dd('Error en el procesamiento: ' . $e->getMessage());
        }
        // 4) Devolver el array con los datos de edicion, titulo, y volumenes
//        dd($general);
        return [
            'title' => $spanishTitle, // titulo en español ( también está en general )
            'editions' => $editions, //  Listado de ediciones (volumen, páginas, precio, fecha, portada)
            'general' => $general,  // Todos los metadatos extraídos de la tabla principal
        ];
    }
}
