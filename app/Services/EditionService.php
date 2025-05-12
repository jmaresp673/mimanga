<?php

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use InvalidArgumentException;

class EditionService
{
    /**
     * Idiomas soportados.
     */
    protected array $supported = ['ES', 'EN'];

    /**
     * Fetch ediciones de una serie dado su título en ROMAJI y el idioma.
     *
     * @param string $romaji
     * @param string $lang Dos letras: ES o EN
     * @return array  ['title' => string, 'editions' => array]
     * @throws InvalidArgumentException
     */
    public function fetchByRomajiAndLang(string $romaji, string $lang): array
    {
        $lang = strtoupper($lang);
        if (!in_array($lang, $this->supported, true)) {
            throw new InvalidArgumentException("Idioma no soportado: {$lang}");
        }

        if ($lang === 'ES') {
            return $this->fetchSpanish($romaji);
        }

        // placeholder para EN
        throw new InvalidArgumentException("Lógica para '{$lang}' aún no implementada.");
    }

    /**
     * Lógica de scraping de ediciones en español.
     */
    protected function fetchSpanish(string $romaji): array
    {
        // Limpiar romaji: eliminar signos y paréntesis
        $cleanRomaji = preg_replace('/[^A-Za-z0-9\s]/', '', $romaji);
        $cleanRomaji = preg_replace('/\s+/', ' ', $cleanRomaji);
        $romaji = trim($cleanRomaji);

        //
        // Llamada AJAX para scrappear el buscador de ListadoManga
        //
        $resp = Http::get('https://www.listadomanga.es/buscar.php', [
            'b' => $romaji,
        ]);

        if ($resp->failed()) {
            abort(502, 'No se pudo conectar al buscador de ListadoManga.');
        }

        $json = $resp->json();
        $colecciones = $json['colecciones'] ?? [];

        if (empty($colecciones)) {
            abort(404, 'No se encontró la serie en ListadoManga.es');
        }

        // Nos quedamos con la primera colección
        $first = $colecciones[0];
        $collectionId = $first['id'];
        $spanishTitle = $first['nombre'];

        //
        // Scrapear la ficha de colección
        //
        $detailUrl = "https://www.listadomanga.es/coleccion.php?id={$collectionId}";
        $html = Http::get($detailUrl)->body();

        $crawler = new Crawler($html);

        // Bloque de datos generales (primera tabla ventana_id1 con <h2> título)
        $infoHtml = $crawler
            ->filter('table.ventana_id1')
            ->eq(0)
            ->html();

        ///////// DATOS EDICION //////////
        ///
        // Extraemos con expresiones regulares los campos
        preg_match('/<h2>\s*(.*?)\s*<\/h2>/si', $infoHtml, $mTitle);                                                // Título en español
        preg_match('/<b>Editorial japonesa:<\/b>\s*<a[^>]*>\s*(.*?)\s*<\/a>/si', $infoHtml, $mJPub);                // Editorial japonesa
        preg_match('/<b>Editorial espa(?:&ntilde;|ñ)ola:<\/b>\s*<a\s+href="[^"]*"[^>]*>([^<]+)<\/a>\s*<a\s+href="([^"]+)"[^>]*>/si', $infoHtml, $mES);  // Editorial española (nombre y web oficial)
        preg_match('/<b>Formato:<\/b>\s*(.*?)(?=<br)/si', $infoHtml, $mFormat);                                     // Formato y dirección de lectura
        preg_match('/<b>Sentido de lectura:<\/b>\s*(.*?)(?=<br)/si', $infoHtml, $mDirection);                       // Sentido de lectura
        preg_match('/<b>Números en japonés:<\/b>\s*(\d+)/si', $infoHtml, $mJNums);                                  // Números editados en japonés y español
        preg_match('/<b>Números en castellano:<\/b>\s*(\d+)(?:.*?)(?=<br|<\/td>)/si', $infoHtml, $mSNums);

        $general = [
            'title' => trim($mTitle[1] ?? ''),
            'original_title' => $romaji, // Usamos la variable existente
            'jp_publisher' => trim($mJPub[1] ?? ''), // Solo el nombre
            'es_publisher' => [
                'name' => trim($mES[1] ?? ''), // Nombre de la editorial
                'web' => trim($mES[2] ?? '') // Primer enlace (web oficial)
            ],
            'format' => trim($mFormat[1] ?? ''),
            'reading_direction' => trim($mDirection[1] ?? ''),
            'numbers_jp' => $mJNums[1] ?? '',
            'numbers_es' => $mSNums[1] ?? '',
        ];
        //        @dd($general);


//////////  DATOS VOLUMENES ///////////

        $nodes = $crawler->filter('table[style*="width: 184px"][class="ventana_id1"]');
//        @dd($nodes->count());

        try {
            // Convertir los nodos a elementos DOM independientes
            $domElements = $nodes->getIterator()->getArrayCopy();

            $editions = array_map(function ($domElement) {
                $table = new Crawler($domElement);
                $cell = $table->filter('td.cen');
//                @dd($cell);

                // Validar elementos esenciales antes de procesar
                if ($cell->count() === 0) return null;
                $imgNode = $cell->filter('img.portada');
                if ($imgNode->count() === 0) return null;

                // Extraer portada
                $img = $imgNode->attr('src');

                // Procesar texto crudo
                $raw = $cell->html();
                $cleanText = strip_tags(preg_replace('/<a[^>]*>.*?<\/a>/', '', $raw));
                $cleanText = trim(preg_replace('/\s+/', ' ', $cleanText));

                // Extraer datos
                preg_match('/nº?\s*(\d+)/i', $cleanText, $mVol);
                preg_match('/(\d+)\s*p(?:á|a)ginas/i', $cleanText, $mPages);
                preg_match('/(\d+,\d{2})\s*€/i', $cleanText, $mPrice);

//                @dd($img, $mVol, $mPages, $mPrice[1]);
                // Procesar fecha
                $date = null;
                preg_match('/<a[^>]*>(\d+\s+[A-Za-z]+\s+\d{4})<\/a>/', $raw, $mDate);
                $fechaTexto = $mDate[1] ?? preg_replace('/.*?(\d+\s+[A-Za-z]+\s+\d{4}).*/', '$1', $cleanText);

                if ($fechaTexto) {
                    $months = [
                        'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
                        'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
                        'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
                    ];

                    if (preg_match('/(\d+)\s+([A-Za-z]+)\s+(\d{4})/', $fechaTexto, $mFecha)) {
                        $mes = ucfirst(strtolower($mFecha[2])); // Normalizar nombre del mes
                        try {
                            $date = DateTime::createFromFormat(
                                'j n Y',
                                sprintf('%d %d %d', $mFecha[1], $months[$mes] ?? 1, $mFecha[3])
                            )->format('Y-m-d');
                        } catch (\Exception $e) {
                            // Registrar error si es necesario
                        }
                    }
                }

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

//            @dd($editions);

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
}
