<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserVolumeRequest;
use App\Models\User;
use App\Models\Edition;
use App\Models\UserVolume;
use App\Http\Requests\StoreUserVolumeRequest;
use App\Models\Volume;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserVolumeController extends Controller
{
    /**
     * Comprobar el estado del volumen (adquirido/deseado o no)
     * @param User $user
     * @param Volume $volume
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(User $user, Volume $volume)
    {
        $relation = UserVolume::where('user_id', $user->id)
            ->where('volume_id', $volume->id)
            ->first();

        if (!$relation) {
            return response()->json(['status' => null]);
        }

        return response()->json([
            'status' => $relation->purchase_date ? true : false
        ]);
    }

    /**
     * Añadir un volumen a la biblioteca del usuario (comprado)
     * @param Request $request
     * @param User $user
     * @param Volume $volume
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToLibrary(Request $request, User $user, Volume $volume)
    {
        // Validar que el usuario autenticado es el dueño (si es necesario)
        if (Auth::id() !== $user->id) {
            abort(403);
        }

        // Crear o actualizar el registro
        $userVolume = UserVolume::updateOrCreate(
            ['user_id' => $user->id, 'volume_id' => $volume->id],
            ['purchase_date' => now(), 'readed' => false]
        );

        return response()->json([
            'message' => 'Volumen añadido a tu biblioteca',
            'data' => $userVolume
        ], 201);
    }

    /**
     * Añadir un volumen a la lista de deseos del usuario (purchase_date a null)
     * @param Request $request
     * @param User $user
     * @param Volume $volume
     * @return \Illuminate\Http\JsonResponse
     */
    public function addToWishlist(Request $request, User $user, Volume $volume)
    {
        if (Auth::id() !== $user->id) {
            abort(403);
        }

        $userVolume = UserVolume::updateOrCreate(
            ['user_id' => $user->id, 'volume_id' => $volume->id],
            ['purchase_date' => null] // Marca como deseado
        );

        return response()->json([
            'message' => 'Volumen añadido a tu lista de deseos',
            'data' => $userVolume
        ], 201);
    }

    public function destroy(User $user, Volume $volume)
    {
        if (Auth::id() !== $user->id) {
            abort(403);
        }

        UserVolume::where('user_id', $user->id)
            ->where('volume_id', $volume->id)
            ->delete();

        return response()->json(['message' => 'Volumen eliminado']);
    }

    /**
     * Funcion libary, usa userVolume para mostrar todos los volumenes del usuario
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $volumes = $user->volumes()
            ->with(['edition'])
            ->wherePivotNotNull('purchase_date')
            ->orderBy('updated_at', 'desc')
            ->get();

        $whislist = $user->volumes()
            ->with(['edition'])
            ->wherePivotNull('purchase_date')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('manga.library', [
            'volumes' => $volumes,
            'whislist' => $whislist,
        ]);
    }


}
