<?php

namespace App\Services;

use DateTime;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\Cast\Object_;
use Symfony\Component\DomCrawler\Crawler;
use InvalidArgumentException;
use Spatie\Browsershot\Browsershot;


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

        if ($lang === 'EN') {
            return $this->fetchEnglish($english, $type);
        }
        throw new InvalidArgumentException("Lógica para el idioma '{$lang}' aún no implementada.");
    }


    /**
     * Obtiene la edición segun tipo e idioma (MANGA|NOVEL|MANHWA).
     * SOLO PARA EDICION ESPAÑOLA LISTADOMANGA
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
                    // fallback alternativo, si no se encuentra el título en inglés, buscar por nativo despues de eliminar espacios en blanco
                    if ($resp->json()['colecciones'] === []) {
                        $nativeALT = preg_replace('/\s+/', '', $native);
                        $resp = Http::get('https://www.listadomanga.es/buscar.php', ['b' => $nativeALT]);
                    }
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
            Log::error('Error en el procesamiento: ' . $e->getMessage());
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
            Log::error('Error en el procesamiento: ' . $e->getMessage());
//            @dd( $e->getMessage());
        }

        // 4) Devolver el array con los datos de edicion, titulo, y volumenes
        return [
            'title' => $spanishTitle, // titulo en español ( también está en general )
            'editions' => $editions, //  Listado de ediciones (volumen, páginas, precio, fecha, portada)
            'general' => $general,  // Todos los metadatos extraídos de la tabla principal
        ];
    }

    /**
     * Lógica de scraping de ediciones en inglés.
     *
     * @param string $query
     * @param string $type
     * @return array
     */
    protected function fetchEnglish(string $query, string $type): array
    {
        $url = 'https://www.whakoom.com/search.aspx/Query';
        $cookies = [
            '.WHAKOOMUSER' => env('WHAKOOM_USER'),
            '.wklang' => 'es',
        ];

        // Headers para la petición AJAX (Para evitar bloqueos de CORS y asegurar que se trata como una petición AJAX)
        $headers = [
            'Content-Type' => 'application/json; charset=UTF-8',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-kl-saas-ajax-request' => 'Ajax_Request',
            'Origin' => 'https://www.whakoom.com',
            'Referer' => 'https://www.whakoom.com/search?s=' . urlencode($query) . '&fit=2&fl=9',
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36',
            'Sec-Ch-Ua' => '"Google Chrome";v="137", "Chromium";v="137", "Not/A)Brand";v="24"',
            'Sec-Ch-Ua-Mobile' => '?0',
            'Sec-Ch-Ua-Platform' => '"Windows"',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-origin',
            'Accept' => 'application/json, text/javascript, */*; q=0.01',
            // CAMBIO CRÍTICO AQUÍ: Borrar 'br' y 'zstd'
            'Accept-Encoding' => 'gzip, deflate',
            'Accept-Language' => 'es-ES,es;q=0.9'
        ];

        $payload = [
            'q' => $query,
            'ft' => 0,
            'fit' => '2', // 2 = "Colecciones"
            'fp' => '',
            'fl' => '9', // 9 = Ingles (USA)
            'p' => 1 // Página 1
        ];

        try {
            $jar = new CookieJar();
            foreach ($cookies as $name => $value) {
                $jar = CookieJar::fromArray(
                    array_map(fn($v) => (string)$v, $cookies),
                    'whakoom.com'
                );
            }

            $response = Http::withOptions([
                'cookies' => $jar,
                'headers' => $headers,
                'debug' => false,
                'timeout' => 30,
                // Forzar decodificación solo para gzip/deflate
                'decode_content' => ['gzip', 'deflate']
            ])->post($url, $payload);

            if ($response->successful()) {
                $jsonResponse = $response->json();

                if (isset($jsonResponse['d']['searchResult'])) {
                    $results = $this->processWhakoomResults($jsonResponse['d']['searchResult']);

                    if ($results) {
                        // llamamos a la funcion para extraer los datos y volumenes de la edicion
                        $dataVolumes = $this->processWhakoomEdition($results['url'], $jar, ($results['type'] === 'Tomo único'));

                        // Definimos los 3 datos de data (para devolverlos igual que en español)
                        $data['title'] = $results['title'];
                        $data['editions'] = $dataVolumes['editions'] ?? []; // Volúmenes extraídos
                        $data['general'] = [
                            'title' => $results['title'],
                            'sinopsis' => $dataVolumes['sinopsis'],
                            'localized_publisher' => [
                                'name' => $results['localized_publisher'],
                                'web' => '' // No hay web en los resultados
                            ],
                            'format' => $results['format'],
                            'type' => $results['type'],
                            'numbers_localized' => $results['numbers_localized'],
                        ];

                        return $data;
                    }
                }
            }

            return [
                'error' => 'Error HTTP: ' . $response->status(),
                'body' => $response->body() // Para diagnóstico
            ];

        } catch (\Exception $e) {
            return [
                'error' => 'Excepción: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString() // Para diagnóstico
            ];
        }
    }

    /**
     * Procesa los resultados de búsqueda de Whakoom.
     * Extrae todos los datos de la edicion excepto sinopsis y volumenes.
     *
     * @param string $html
     * @return array|mixed
     */
    protected function processWhakoomResults(string $html)
    {
        $crawler = new Crawler($html);
        $results = [];

        $crawler->filter('.sresult')->each(function (Crawler $node) use (&$results) {
            // 1. Extraer título y URL
            $titleNode = $node->filter('.title a');
            $title = $titleNode->count() > 0 ? $titleNode->text() : '';
            $url = $titleNode->count() > 0 ? $titleNode->attr('href') : '';
            $url = 'https://www.whakoom.com' . $url;

            // 2. Extraer imagen
            $image = $node->filter('.img img')->count() > 0
                ? $node->filter('.img img')->attr('src')
                : '';

            // 3. Extraer editorial
            $publisher = $node->filter('.pub')->count() > 0
                ? $node->filter('.pub')->text()
                : '';

            // 4. Extraer información de la edición (corregido)
            $editionInfo = '';
            $language = '';
            $type = '';
            $format = '';
            $volumes = 0;

            // Buscar en todos los párrafos la información clave
            $node->filter('p')->each(function (Crawler $pNode) use (&$editionInfo, &$language, &$type, &$format, &$volumes, $publisher) {
                $text = $pNode->text();

                // Patrones mejorados para detectar la información de edición
                if (preg_match('/(colección de|tomo único|collection of|single volume)/i', $text)) {
                    $editionInfo = $text;

                    // Extraer tipo (Colección de/Tomo único)
                    if (preg_match('/(colección de|collection of)/i', $text)) {
                        $type = 'Colección de';
                        // Extraer número de volúmenes
                        if (preg_match('/(\d+)\s*(números|volumes|volúmenes|vols|vol)/i', $text, $matches)) {
                            $volumes = (int)$matches[1];
                        }
                    } else {
                        $type = 'Tomo único';
                        $volumes = 1;
                    }

                    // Extraer formato (última parte después del punto)
                    if (preg_match('/\.\s*([^.]+)$/', $text, $matches)) {
                        $format = trim($matches[1]);
                    }
                }

                // Extraer idioma (después de la editorial)
                if (strpos($text, $publisher) !== false && preg_match('/,\s*(.+)$/', $text, $matches)) {
                    $language = trim($matches[1]);
                }
            });


            // Sustituimos el thumb de la imagen por large
            $image = str_replace('/thumb/', '/large/', $image);
            $results[] = [
                'title' => $title,
                'url' => $url,
                'image' => $image,
                'localized_publisher' => $publisher,
                'type' => $type, // Colección de, Tomo único, etc.
                'format' => $format, // Softcover, Hardcover, Digital, etc.
                'numbers_localized' => $volumes,
                'raw_info' => $editionInfo,
            ];
        });

        //filtrado de resultados, descartamos ediciones digitales y slipcase (format = Digital)
        $results = array_filter($results, function ($item) {
            return !str_contains($item['format'], 'Digital') && !str_contains($item['format'], 'digital')
                && !str_contains($item['format'], 'Slipcase') && !str_contains($item['format'], 'slipcase');
        });
//        dd($results);

        // Seleccionar la colección más relevante: la que tiene más volúmenes
        $results = array_values($results);
        if (empty($results)) {
            return [];
        }
        usort($results, fn($a, $b) => ($b['numbers_localized'] ?? 0) <=> ($a['numbers_localized'] ?? 0));
        return $results[0];
    }

    /**
     * Procesa la edición de Whakoom y extrae los datos relevantes.
     * Extrae la sinopsis y los volúmenes de la edición.
     *
     * @param string $html
     * @param CookieJar|null $jar
     * @param bool $isOneShot
     * @return array
     */
    protected function processWhakoomEdition(string $html, CookieJar $jar = null, bool $isOneShot = false): array
    {
        if ($jar === null) {
            $jar = CookieJar::fromArray([
                '.WHAKOOMUSER' => env('WHAKOOM_USER'),
                '.wklang' => 'es',
            ], '.whakoom.com');
        }

        $data = ['sinopsis' => '', 'editions' => []];

        try {
            $editionHtml = Http::withOptions([
                'cookies' => $jar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; MiScraper/1.0)',
                ],
            ])->get($html)->body();

            $crawlerSinopsis = new Crawler($editionHtml);
            $sinopsisNode = $crawlerSinopsis->filter('div.wiki-text');
            if ($sinopsisNode->count() > 0) {
                $parrafos = [];
                $sinopsisNode->filter('p')->each(function (Crawler $p) use (&$parrafos) {
                    $texto = trim($p->text());
                    $texto = str_replace('Argumento', '', $texto);
                    $parrafos[] = $texto;
                });
                $data['sinopsis'] = implode("\r\n", $parrafos);

                // Si sinopsis está vacía, asignar un valor por defecto
                if ($sinopsisNode->text() === '') {
                    $sinopsisNode->text(__('Sinopsis not available yet.'));
                } else{
                    $data['sinopsis'] = $sinopsisNode->text();
                }
            }

            // Si es un tomo único, no hay volúmenes, devolvemos los datos y el volumen 1
            if ($isOneShot) {
                $data['editions'] = [
                    [
                        'volumen' => 1,
                        'paginas' => null,
                        'precio' => null,
                        'fecha' => null,
                        'portada' => null, // Usamos la portada que ya tenemos en $results
                    ]
                ];
                return $data;
            }

        } catch (\Exception $e) {
            Log::error('Error extracting synopsis: ' . $e->getMessage());
        }

        try {
            $urlTodos = $html . "/todos";
            $volumeHtml = Http::withOptions([
                'cookies' => $jar,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (compatible; MiScraper/1.0)',
                ],
            ])->get($urlTodos)->body();

            // Extraer imagen y número de cada volumen (lista ul con clase v2-cover-list)
            $crawlerVolumes = new Crawler($volumeHtml);
            $editions = [];
            $crawlerVolumes->filter('ul.v2-cover-list > li')->each(function (Crawler $node) use (&$editions) {
                $img = $node->filter('img')->count() > 0 ? $node->filter('img')->attr('src') : null;
                $issueNumber = $node->filter('.issue-number')->count() > 0 ? $node->filter('.issue-number')->text() : null;
                if ($img && $issueNumber) {
                    $editions[] = [
                        'volumen' => preg_replace('/[^0-9]/', '', $issueNumber),
                        'portada' => str_replace('/small/', '/large/', $img),
                        'paginas' => null,
                        'precio' => null,
                        'fecha' => null,
                    ];
                }
            });
//            dd($editions, "ediciones");
            $data['editions'] = $editions;
        } catch (\Exception $e) {
            Log::error('Error extracting volúmenes: ' . $e->getMessage());
        }
        return $data;
    }
}
