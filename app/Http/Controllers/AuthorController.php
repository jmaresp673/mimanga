<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Http\Requests\StoreAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use Illuminate\Support\Facades\Http;

class AuthorController extends Controller
{
    public function show($anilistId)
    {
        // Buscar el autor en base a su anilist_id
        $author = Author::where('anilist_id', $anilistId)->first();

        if (!$author) {
            // Si no existe en la DB, hacemos una petición rápida para obtener el nombre
            $graphqlQuery = <<<GQL
            query (\$id: Int) {
                Staff(id: \$id) {
                    name {
                        full
                    }
                }
            }
            GQL;

            $variables = [
                'id' => (int) $anilistId,
            ];

            $response = Http::post('https://graphql.anilist.co', [
                'query' => $graphqlQuery,
                'variables' => $variables,
            ]);

            $staff = $response->json('data.Staff');

            // Insertamos solo nombre y anilist_id
            if ($staff) {
                $author = Author::create([
                    'name' => $staff['name']['full'] ?? 'Desconocido',
                    'anilist_id' => $anilistId,
                ]);
            } else {
                abort(404, 'Author not found.');
            }
        }

        // Ahora hacemos la consulta extendida para mostrar datos en la vista
        $extendedData = $this->fetchAuthorDetails($anilistId);

        return view('authors.show', compact('author', 'extendedData'));
    }

    private function fetchAuthorDetails($anilistId)
    {
        $graphqlQuery = <<<GQL
        query (\$id: Int) {
            Staff(id: \$id) {
                name {
                    full
                    native
                }
                image {
                    medium
                }
                gender
                dateOfBirth {
                    year
                    month
                    day
                }
                age
            }
        }
        GQL;

        $variables = [
            'id' => (int) $anilistId,
        ];

        $response = Http::post('https://graphql.anilist.co', [
            'query' => $graphqlQuery,
            'variables' => $variables,
        ]);

        return $response->json('data.Staff');
    }
}
