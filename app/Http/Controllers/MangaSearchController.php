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
            $mainAuthors = [];

            if (!empty($manga['staff']['nodes']) && !empty($manga['staff']['edges'])) {
                $validRoles = [
                    'story & art',
                    'story',
                    'art',
                    'illustration',
                    'original creator'
                ];

                foreach ($manga['staff']['nodes'] as $index => $staff) {
                    $role = strtolower($manga['staff']['edges'][$index]['role'] ?? '');
                    $name = $staff['name']['full'] ?? 'Autor desconocido';
                    $id = $staff['id'] ?? null;

                    foreach ($validRoles as $validRole) {
                        if (str_contains($role, $validRole)) {
                            $mainAuthors[] = [
                                'id' => $id,
                                'type' => $validRole,
                                'name' => $name
                            ];

                            if ($validRole === 'story & art') {
                                break 2;
                            }
                        }
                    }
                }
            }

            return array_merge($manga, ['main_authors' => $mainAuthors]);
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
                    nodes { id name { full } }
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
        $results = array_map(function ($m) {
            $validRoles = ['story & art', 'story', 'art', 'illustration', 'original creator'];
            $main = [];
            if (!empty($m['staff']['nodes']) && !empty($m['staff']['edges'])) {
                foreach ($m['staff']['nodes'] as $i => $staff) {
                    $role = strtolower($m['staff']['edges'][$i]['role'] ?? '');
                    foreach ($validRoles as $vr) {
                        if (str_contains($role, $vr)) {
                            $main[] = [
                                'id' => $staff['id'],
                                'type' => $vr,
                                'name' => $staff['name']['full'] ?? 'Desconocido',
                            ];
                            if ($vr === 'story & art') break 2;
                        }
                    }
                }
            }
            $m['main_authors'] = $main;
            return $m;
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
                    nodes { id name { full } }
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
        $results = array_map(function ($m) {
            $validRoles = ['story & art', 'story', 'art', 'illustration', 'original creator'];
            $main = [];
            if (!empty($m['staff']['nodes']) && !empty($m['staff']['edges'])) {
                foreach ($m['staff']['nodes'] as $i => $staff) {
                    $role = strtolower($m['staff']['edges'][$i]['role'] ?? '');
                    foreach ($validRoles as $vr) {
                        if (str_contains($role, $vr)) {
                            $main[] = [
                                'id' => $staff['id'],
                                'type' => $vr,
                                'name' => $staff['name']['full'] ?? 'Desconocido',
                            ];
                            if ($vr === 'story & art') break 2;
                        }
                    }
                }
            }
            $m['main_authors'] = $main;
            return $m;
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
                    nodes { id name { full } }
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
        $results = array_map(function ($m) {
            $validRoles = ['story & art', 'story', 'art', 'illustration', 'original creator'];
            $main = [];
            if (!empty($m['staff']['nodes']) && !empty($m['staff']['edges'])) {
                foreach ($m['staff']['nodes'] as $i => $staff) {
                    $role = strtolower($m['staff']['edges'][$i]['role'] ?? '');
                    foreach ($validRoles as $vr) {
                        if (str_contains($role, $vr)) {
                            $main[] = [
                                'id' => $staff['id'],
                                'type' => $vr,
                                'name' => $staff['name']['full'] ?? 'Desconocido',
                            ];
                            if ($vr === 'story & art') break 2;
                        }
                    }
                }
            }
            $m['main_authors'] = $main;
            return $m;
        }, $results);

        return [
            'trending' => $results,
        ];
    }
}
