<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\TherapyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class TherapyController extends Controller
{
    /**
     * @var TherapyService
     */
    protected $therapyService;

    /**
     * TherapyController constructor.
     */
    public function __construct()
    {
        $this->therapyService = new TherapyService();
    }

    /**
     * @api {get} /api/{locale}/therapy Получение и поиск мет. лечения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchTherapy
     * @apiGroup PublicMedical

     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer} [disease_id] Идентификатор заболевания
     * @apiParam {integer} [country_id] Идентификатор страны
     * @apiParam {integer} [region_id] Идентификатор региона
     * @apiParam {integer} [city_id] Идентификатор города
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc", "diseases_public_count": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 302,
        "items": [
            {
                "id": 12,
                "name": "Диспансерный прием (осмотр, консультация) врача-онколога детского",
                "url": "dfkjhgfdsdfghjjlj",
                "diseases_public_count": "9"
            },
            {
                "id": 44,
                "name": "Аэровоздействие",
                "url": null,
                "diseases_public_count": "41"
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function index(Request $request, string $locale)
    {
        $valid = Validator($request->all(),[
            'disease_id' => 'integer|nullable',
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response( $valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $filterParams = $request->only('disease_id', 'country_id', 'region_id', 'city_id');
        $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->therapyService->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, $locale);
    }

    /**
     * @api {get} /api/{locale}/therapy/{url} Получение мет. лечения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetTherapy
     * @apiGroup PublicMedical
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name": "Визуальный осмотр общетерапевтический",
        "description": "нет определения",
        "active": true,
        "alias": "vizualnyy-osmotr-obshcheterapevticheskiy-2",
        "medical_profiles_public": [
            {
                "id": 15,
                "alias": "bolezni-oporno-dvigatelnogo-apparata-15",
                "name": "Болезни опорно-двигательного аппарата"
            },
            {
                "id": 20,
                "alias": "reabilitatsiya-onkologicheskikh-zabolevaniy-20",
                "name": "Реабилитация онкологических заболеваний"
            },
            {
                "id": 22,
                "alias": "testovyy-profil-22",
                "name": "Тестовый профиль"
            },
            {
                "id": 10,
                "alias": "bolezni-zheludochno-kishechnogo-trakta-10",
                "name": "Болезни желудочно-кишечного тракта"
            }
        ],
        "seo": null,
        "diseases_public": [
            {
                "id": 2,
                "name": "КИШЕЧНЫЕ ИНФЕКЦИИ",
                "alias": "kishechnye-infektsii-2"
            },
            {
                "id": 3,
                "name": "Холера",
                "alias": "kholera-3"
            }
        ],
        "images": []
    }
     * @param string $locale
     * @param string $url
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $url)
    {
        $therapy = $this->therapyService->getTherapy(null, $url, $locale);

        return response()->json($therapy, 200);
    }

    /**
     * @api {get} /api/{locale}/favorite/therapy Получение списка избранных мет. лечения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetTherapies
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 2,
        "items": [
            {
                "id": 1,
                "name": "Сбор анамнеза и жалоб общетерапевтический",
                "alias": "sbor-anamneza-i-zhalob-obshcheterapevticheskiy",
                "diseases_public_count": "28"
            },
            {
                "id": 2,
                "name": "Визуальный осмотр общетерапевтический",
                "alias": "vizualnyy-osmotr-obshcheterapevticheskiy",
                "diseases_public_count": "28"
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getFavorites(Request $request, string $locale)
    {
        $valid = Validator($request->all(),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response( $valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $userId = Auth::user()->id;

        $therapies = $this->therapyService->getFavorites($page, $rowsPerPage, $searchKey, $sorting,
            $locale, $userId);

        return response()->json($therapies, 200);
    }

    /**
     * @api {post} /api/{locale}/favorite/therapy Добавление в избранное мет. лечения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName AddTherapies
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  therapy_id ID мет. лечения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Добавлено в избранное"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFavorite(Request $request)
    {
        $valid = Validator($request->all(), [
            'therapy_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $therapyId = $request->get('therapy_id');

        $this->therapyService->addFavorite($userId, $therapyId);

        return response()->json(['message' => 'Добавлено в избранное'], 200);
    }

    /**
     * @api {delete} /api/{locale}/favorite/therapy Удаление из избранного (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName DeleteTherapies
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  therapy_id ID мет. лечения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Удалено из избранного"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deleteFavorite(Request $request)
    {
        $valid = Validator($request->all(), [
            'therapy_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $medicalProfileId = $request->get('therapy_id');

        $this->therapyService->deleteFavorite($userId, $medicalProfileId);

        return response()->json(['message' => 'Удалено из избранного'], 200);
    }
}
