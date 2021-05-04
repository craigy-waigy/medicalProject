<?php

namespace App\Http\Controllers\Api\Account\Patient;

use App\Models\FavoriteDisease;
use App\Models\FavoriteGeography;
use App\Models\FavoriteMedicalProfile;
use App\Models\FavoriteObject;
use App\Models\FavoriteTherapy;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    /**
     * @api {get} /api/{locale}/favorites Получение списка избранного (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetFavorites
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "medical_profiles": 2,
        "therapies": 2,
        "diseases": 2,
        "objects": 2,
        "geography": 3
    }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function get()
    {
        $userId = Auth::user()->id;
        $favorite = [
            'medical_profiles' => FavoriteMedicalProfile::where('user_id', $userId)->count(),
            'therapies' => FavoriteTherapy::where('user_id', $userId)->count(),
            'diseases' => FavoriteDisease::where('user_id', $userId)->count(),
            'objects' => FavoriteObject::where('user_id', $userId)->count(),
            'geography' => FavoriteGeography::where('user_id', $userId)->count(),
        ];

        return response()->json($favorite, 200);
    }
}
