<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MangaSearchController extends Controller
{
    public function index()
    {
        $popular = $this->popular(request());
        $score = $this->score(request());
        $trending = $this->trending(request());
        return view('manga.search', [
            'popular' => $popular,
            'score' => $score,
            'trending' => $trending,
        ]);
    }

    /**
     * Obtener los autores principales para una serie.
     */
    private function getMainAuthors($staff)
    {
        $mainAuthors = [];
        $validRoles = [
            'story & art',
            'story',
            'art',
            'illustration',
            'original creator'
        ];
        $primaryOccupations = ['mangaka', 'writer', 'illustrator'];

        if (!empty($staff['nodes']) && !empty($staff['edges'])) {
            $processedAuthors = [];

            foreach ($staff['nodes'] as $index => $staffNode) {
                $role = strtolower($staff['edges'][$index]['role'] ?? '');
                $name = $staffNode['name']['full'] ?? 'Autor desconocido';
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
                        'type' => implode(', ', $uniqueRoles),
                        'name' => $name
                    ];

                    // Marcar autor como procesado
                    $processedAuthors[$authorKey] = true;
                }
            }
        }

        return $mainAuthors;
    }

    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255',
            'page' => 'nullable|integer|min:1',
        ]);

        $query = $request->input('query');
        $page = (int)$request->input('page', 1);

        $cacheKey = 'search_manga_' . md5($query . '_page_' . $page);

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($query, $page) {
            $graphqlQuery = <<<GQL
        query (\$search: String, \$page: Int) {
            Page(perPage: 9, page: \$page) {
                pageInfo {
                    total
                    perPage
                    currentPage
                    lastPage
                    hasNextPage
                }
                media(search: \$search, type: MANGA, sort: [POPULARITY_DESC]) {
                    id
                    title {
                        romaji
                        native
                    }
                    coverImage {
                        large
                    }
                    bannerImage
                    startDate {
                        year
                    }
                    endDate {
                        year
                    }
                    format
                    genres
                    status
                    chapters
                    volumes
                    averageScore
                    staff {
                        nodes {
                            id
                            name {
                                full
                            }
                            primaryOccupations
                        }
                        edges {
                            role
                        }
                    }
                    isAdult
                }
            }
        }
        GQL;

            $variables = [
                'search' => $query,
                'page' => $page,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graphql.anilist.co', [
                'query' => $graphqlQuery,
                'variables' => $variables,
            ]);

            if ($response->failed() || isset($response->json()['errors'])) {
                abort(500, 'Error al consultar Anilist.');
            }

            return $response->json('data.Page');
        });

        $results = $data['media'] ?? [];
        $pageInfo = $data['pageInfo'] ?? [];

        // Filtrar mangas +18
        $results = array_filter($results, function ($manga) {
            return empty($manga['isAdult']) || $manga['isAdult'] === false;
        });

        // Agregar autores principales
        $results = array_map(function ($manga) {
            return array_merge($manga, [
                'main_authors' => $this->getMainAuthors($manga['staff'])
            ]);
        }, $results);

//        dd($results);
        if ($request->ajax()) {
            return view('manga._cards', ['results' => $results])->render();
        }
        return view('manga.results', compact('results', 'query', 'pageInfo'));
    }

    /**
     * Mostrar recomendaciones de manga populares.
     */
    protected function popular(Request $request)
    {
        $perPage = 20;
        $page = 1;

        $cacheKey = "popular_manga_popular_page_{$page}_per_{$perPage}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($perPage, $page) {
            $graphql = <<<'GQL'
            query ($perPage: Int, $page: Int) {
              Page(perPage: $perPage, page: $page) {
                media(type: MANGA, sort: POPULARITY_DESC) {
                  id
                  title { romaji native english }
                  coverImage { large }
                  bannerImage
                  averageScore
                  isAdult
                  format
                  status
                  chapters
                  volumes
                  startDate { year }
                  endDate { year }
                  genres
                  staff {
                    nodes { id name { full } primaryOccupations }
                    edges { role }
                  }
                }
              }
            }
            GQL;

            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graphql.anilist.co', [
                'query' => $graphql,
                'variables' => [
                    'perPage' => $perPage,
                    'page' => $page,
                ],
            ]);

            if ($resp->failed() || isset($resp->json()['errors'])) {
                return [];
            }

            return $resp->json('data.Page.media') ?? [];
        });

        // Filtramos +18
        $results = array_filter($data, fn($m) => empty($m['isAdult']));

        // Añadimos autores principales
        $results = array_map(function ($manga) {
            return array_merge($manga, [
                'main_authors' => $this->getMainAuthors($manga['staff'])
            ]);
        }, $results);
        return [
            'popular' => $results,
        ];
    }

    /**
     * Mostrar recomendaciones de manga mas valorados.
     */
    protected function score(Request $request)
    {
        $perPage = 20;
        $page = 1;

        $cacheKey = "score_manga_page_{$page}_per_{$perPage}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($perPage, $page) {
            $graphql = <<<'GQL'
            query ($perPage: Int, $page: Int) {
              Page(perPage: $perPage, page: $page) {
                media(type: MANGA, sort: SCORE_DESC) {
                  id
                  title { romaji native english }
                  coverImage { large }
                  bannerImage
                  averageScore
                  isAdult
                  format
                  status
                  chapters
                  volumes
                  startDate { year }
                  endDate { year }
                  genres
                  staff {
                    nodes { id name { full } primaryOccupations}
                    edges { role }
                  }
                }
              }
            }
            GQL;

            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graphql.anilist.co', [
                'query' => $graphql,
                'variables' => [
                    'perPage' => $perPage,
                    'page' => $page,
                ],
            ]);

            if ($resp->failed() || isset($resp->json()['errors'])) {
                return [];
            }

            return $resp->json('data.Page.media') ?? [];
        });

        // Filtramos +18
        $results = array_filter($data, fn($m) => empty($m['isAdult']));

        // Añadimos autores principales
        $results = array_map(function ($manga) {
            return array_merge($manga, [
                'main_authors' => $this->getMainAuthors($manga['staff'])
            ]);
        }, $results);
        return [
            'score' => $results,
        ];
    }

    /**
     * Mostrar mangas en tendencia.
     */
    protected function trending(Request $request)
    {
        $perPage = 20;
        $page = 1;

        $cacheKey = "trending_manga_page_{$page}_per_{$perPage}";

        $data = Cache::remember($cacheKey, now()->addMinutes(30), function () use ($perPage, $page) {
            $graphql = <<<'GQL'
            query ($perPage: Int, $page: Int) {
              Page(perPage: $perPage, page: $page) {
                media(type: MANGA, sort: TRENDING_DESC) {
                  id
                  title { romaji native english }
                  coverImage { large }
                  bannerImage
                  averageScore
                  isAdult
                  format
                  status
                  chapters
                  volumes
                  startDate { year }
                  endDate { year }
                  genres
                  staff {
                    nodes { id name { full } primaryOccupations}
                    edges { role }
                  }
                }
              }
            }
            GQL;

            $resp = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graphql.anilist.co', [
                'query' => $graphql,
                'variables' => [
                    'perPage' => $perPage,
                    'page' => $page,
                ],
            ]);

            if ($resp->failed() || isset($resp->json()['errors'])) {
                return [];
            }

            return $resp->json('data.Page.media') ?? [];
        });

        // Filtramos +18
        $results = array_filter($data, fn($m) => empty($m['isAdult']));

        // Añadimos autores principales
        $results = array_map(function ($manga) {
            return array_merge($manga, [
                'main_authors' => $this->getMainAuthors($manga['staff'])
            ]);
        }, $results);
        return [
            'trending' => $results,
        ];
    }
}
