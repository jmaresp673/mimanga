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
    public function fetchByRomajiAndLang(string $native, string $lang, string $romaji, string $english, string $type): array
    {
        $lang = strtoupper($lang);
        if (!in_array($lang, $this->supported, true)) {
            throw new InvalidArgumentException("Idioma no soportado: {$lang}");
        }

        if ($lang === 'ES') {
            return $this->fetchSpanish($native, $romaji, $english, $type);
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
        // Tambien eliminar "Comic-Books"
        $filtered = array_filter($filtered, fn($c) => !preg_match('/Comic-Books?/i', $c['title']));
        // Eliminar Edición Especial
        $filtered = array_filter($filtered, fn($c) => !preg_match('/(Edición\s+Especial)/i', $c['title']));
        // Eliminar "Guardians de la nit" como excepción
        $filtered = array_filter($filtered, fn($c) => !preg_match('/Guardians\s+de\s+la\s+nit/i', $c['title']));

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
    protected function fetchSpanish(string $native, string $romaji, string $english, string $type): array
    {
        // Limpiar romaji: eliminar signos y paréntesis
        $cleanRomaji = preg_replace('/[^A-Za-z0-9\s]/', '', $romaji);
        $cleanRomaji = preg_replace('/\s+/', ' ', $cleanRomaji);
        $romaji = trim($cleanRomaji);

        // Limpiar kanji o nativo y elimina "。"
        $native = preg_replace('/\s+/', ' ', $native);
        $native = preg_replace('/。/', '', $native);

        //
        // Llamada AJAX para scrappear el buscador de ListadoManga
        //
        try {
            $resp = Http::get('https://www.listadomanga.es/buscar.php', ['b' => $romaji]);
            // fallback, si no se encuentra el título en romaji, buscar por nativo
            if ($resp->json()['colecciones'] === []) {
                $resp = Http::get('https://www.listadomanga.es/buscar.php', ['b' => $native]);
                // fallback, si no se encuentra el título en nativo, buscar por inglés
                if ($resp->json()['colecciones'] === [] && !empty($english)) {
                    $resp = Http::get('https://www.listadomanga.es/buscar.php', ['b' => $english]);
                }
            }
//                    dd($resp->json(), $native, $english, $romaji);
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

        // Seleccionar la mejor opcion
        $best = $this->selectCollection($colecciones, strtoupper($type));
        $colecciones = $best ? $best : []; // si no hay coincidencias, devolver un array vacío

        if (empty($colecciones)) {
            return $colecciones;
        }

        $spanishTitle = $colecciones['title'];
        $detailUrl = $colecciones['url'];

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
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            // lanzar error de servidor
            abort(502, 'Ha ocurrido un error, por favor intentelo de nuevo mas tarde.');
        }

        $crawler = new Crawler($html);

        try {
            // Bloque de datos generales (primera tabla ventana* con <h2> título)
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

        ///////// SINÓPSIS //////////
        $sinopsis = 'No hay sinopsis disponible.'; // Valor por defecto

        try {
            $sinopsisTable = $crawler->filter('table[class^="ventana"]')->reduce(function (Crawler $node) {
                return $node->filter('h2:contains("Sinopsis")')->count() > 0;
            });

            if ($sinopsisTable->count() > 0) {
                // Eliminar el h2 y su contenido (sinopsis de....)
                $sinopsisTable->first()->filter('h2')->each(function (Crawler $node) {
                    $node->getNode(0)->parentNode->removeChild($node->getNode(0));
                });

                // Extraer contenido y limpiar
                $sinopsisText = $sinopsisTable->first()->filter('td.izq')->html();

                // Eliminar hr y espacios innecesarios
                $sinopsis = strip_tags($sinopsisText); // Eliminar cualquier etiqueta restante
                $sinopsis = str_replace('  ', ' ', $sinopsis); // Dobles espacios por uno
            }
        } catch (\Exception $e) {
            Log::error('Error extrayendo sinopsis: ' . $e->getMessage());
        }
        $general['sinopsis'] = $sinopsis;

//////////  DATOS VOLUMENES ///////////

        $nodes = $crawler->filter('table[style*="width: 184px"][class^="ventana"]');
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

                // Extraer datos
                if ($general['numbers_localized'] === '1') {
                    $mVol[1] = 1;
                } else {
                    preg_match('/nº?\s*(\d+)/i', $cleanText, $mVol);
                }
                preg_match('/(\d+)\s*p(?:á|a)ginas/i', $cleanText, $mPages);
                preg_match('/(\d+,\d{2})\s*€/i', $cleanText, $mPrice);

                // Procesar fecha de edición (día + mes año)
                $date = null;

                // Decodificar entidades HTML primero (ej: &ordm; a º)
                $raw = htmlspecialchars_decode($raw);

                // Intentar extraer fecha con día (formato: 29 Julio 2022)
                // en html: 29 <a ...>Julio 2022</a>
                if (preg_match('/(\d+)\s*<a[^>]*>\s*([A-Za-z]+)\s+(\d{4})\s*<\/a>/', $raw, $mDate)) {
                    $day = (int)$mDate[1];
                    $month = ucfirst(strtolower(trim($mDate[2])));
                    $year = (int)$mDate[3];
                } // Si falla, intentar extraer solo mes y año (formato: Julio 2022)
                elseif (preg_match('/<a[^>]*>\s*([A-Za-z]+)\s+(\d{4})\s*<\/a>/', $raw, $mDate)) {
                    $day = 1; // Día por defecto
                    $month = ucfirst(strtolower(trim($mDate[1])));
                    $year = (int)$mDate[2];
                } // Si no se detecta fecha, asignar valores por defecto
                else {
                    $day = 1;
                    $month = 'Enero';
                    $year = 2000; // Año por defecto
                }

                $months = [
                    'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                    'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                    'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
                ];

                // Validar mes y crear fecha
                try {
                    $date = isset($months[$month])
                        ? DateTime::createFromFormat('Y-m-d', sprintf('%04d-%02d-%02d', $year, $months[$month], $day))
                        : new DateTime('2000-01-01'); // Fecha por defecto si el mes es inválido

                    $date = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = '2000-01-01'; // Fecha por defecto ante cualquier error
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

            // Filtrar y formatear resultados
            $editions = array_values(array_filter($editions, function ($item) {
                return $item !== null && $item['volumen'] !== null && $item['portada'] !== null;
            }));
        } catch (InvalidArgumentException $e) {
            @dd('Error en el procesamiento: ' . $e->getMessage());
        }

        // 4) Devolver el array con los datos de edicion, titulo, y volumenes
        return [
            'title' => $spanishTitle, // titulo en español ( también está en general )
            'editions' => $editions, //  Listado de ediciones (volumen, páginas, precio, fecha, portada)
            'general' => $general,  // Todos los metadatos extraídos de la tabla principal
        ];
    }

    protected function fetchEnglish(string $english, string $type): array
    {

        // Placeholder para lógica de scraping en inglés
        // Aquí deberías implementar la lógica específica para buscar ediciones en inglés
        // similar a lo que se hace en fetchSpanish, pero adaptada a las fuentes en inglés.

        throw new InvalidArgumentException("Lógica para 'EN' aún no implementada.");
    }


    /**
     * Obtiene el ISBN de un volumen validando autor y número.
     *
     * @param string $seriesTitle Título limpio de la serie (p.ej. "Chainsaw Man")
     * @param string $expectedAuthors Lista CSV de autores esperados (p.ej. "Tatsuki Fujimoto")
     * @param int $volumeNumber Número de volumen que buscamos
     * @param string $lang Código de idioma para Google Books (en/fr)
     * @return string|null           ISBN_13 o ISBN_10 si se encuentra, null sino
     */
    public function fetchIsbnForVolume(string $seriesTitle, string $expectedAuthors, string $lang = 'en'): ?string
    {
        try {
            $authorsToMatch = array_map('trim', explode(',', $expectedAuthors));

            $resp = Http::get('https://www.googleapis.com/books/v1/volumes', [
                'q' => $seriesTitle,
                'langRestrict' => $lang,
                'maxResults' => 40,
            ]);

            if (!$resp->ok()) {
                return null;
            }

            foreach ($resp->json('items', []) as $item) {
                $info = $item['volumeInfo'] ?? [];

                // 1) Validar autor:
                $itemAuthors = $info['authors'] ?? [];
                if (!count(array_intersect($authorsToMatch, $itemAuthors))) {
                    return null; // No coincide con los autores esperados
                }

                // 2) Extraer titulo del title y numero de seriesInfo[bookDisplayNumber]
                $title = $info['title'] ?? '';
                // seriesInfo puede no existir, así que lo manejamos con un try-catch
                try {
                    $volumeNumber = $info['seriesInfo']['bookDisplayNumber'];

                    // también intentamos extraer el seriesId de seriesInfo
                    //seriesInfo[volumeSeries][0]['seriesId'] ?? null
                    $seriesInfo = $info['seriesInfo']['volumeSeries'][0]['seriesId'] ?? null;
                } catch (\Exception $e) {
                    $volumeNumber = 1; // Asignar 1 si no se encuentra
                    $seriesInfo = null; // Asignar null si no se encuentra
                }

                // 3) Validar número de volumen
                if (!preg_match('/' . preg_quote($seriesTitle, '/') . '/i', $title)) {
                    return null; // No coincide con el título de la serie, no es un volumen de la serie
                }

                // 3) Extraer el ISBN
                // Primero busca ISBN_13, si no lo encuentra busca ISBN_10
                foreach ($info['industryIdentifiers'] ?? [] as $id) {
                    if ($id['type'] === 'ISBN_13') {
                        return $id['identifier'];
                    }
                }
                foreach ($info['industryIdentifiers'] ?? [] as $id) {
                    if ($id['type'] === 'ISBN_10') {
                        return $id['identifier'];
                    }
                }
                // Si no se encuentra ningún ISBN, devolver null
            }

        } catch (\Exception $e) {
            Log::error('Error fetching ISBN: ' . $e->getMessage());
        }
        return null;
    }

}
