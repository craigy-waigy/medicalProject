<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\DiseaseService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DiseaseController extends Controller
{
    /**
     * @var DiseaseService
     */
    protected $diseaseService;

    /**
     * DiseaseController constructor.
     */
    public function __construct()
    {
        $this->diseaseService = new DiseaseService();
    }

    /**
     * @api {get} /api/{locale}/disease Поиск Заболеваний (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchDisease
     * @apiGroup PublicMedical
     *
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer}  [parent] Родитель
     * @apiParam {integer}  [profile_id] Идентификатор мед. профиля
     * @apiParam {integer}  [therapy_id] Идентификатор мет. лечения лечения
     * @apiParam {integer} [country_id] Идентификатор страны
     * @apiParam {integer} [region_id] Идентификатор региона
     * @apiParam {integer} [city_id] Идентификатор города
     * @apiParam {json}  [sorting] Массив сортировки {"medical_profiles_public_count": "asc", "therapies_public_count": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
    "page": 1,
    "rowsPerPage": 10,
    "total": 5,
    "items": [
            {
                "id": 3762,
                "parent": 3761,
                "name": "Реноваскулярная гипертензия",
                "alias": "renovaskulyarnaya-gipertenziya",
                "has_children": false,
                "description": "РГ – это один из видов вторичной артериальной гипертензии, обусловленный патологией почечных сосудов. Закономерно возникает вопрос о том, какая именно патология данных сосудов может лежать в основе заболевания.Реноваскулярная гипертензия (РГ) является одной из форм симптоматической артериальной гипертензии. В основе реноваскулярной гипертензии всегда легких одно- или двустороннее сужение просвета почечной артерии либо одной или нескольких крупных ветвей ее. В результате через участок артерии с патологически суженным отверстием в почку в единицу времени поступает меньше крови. Это приводит к развитию ишемии почечной ткани, выраженность которой зависит от степени стеноза пораженной артерии.",
                "open_new_page": false,
                "temporarily_disabled": false,
                "therapies_public_count": 42,
                "medical_profiles_public_count": 2
            },
            {
                "id": 3763,
                "parent": 3761,
                "name": "Гипертензия вторичная по отношению к другим поражениям почек",
                "alias": "gipertenziya-vtorichnaya-po-otnosheniyu-k-drugim-porazheniyam-pochek",
                "has_children": false,
                "description": "Ренопаренхиматозная артериальная гипертензия (АГ) — симптоматическая (вторичная) АГ, вызванная врождённым или приобретённым заболеванием почек (в первую очередь почечной паренхимы). Статистические данные. Ренопаренхиматозная АГ возникает в 2–3% случаев АГ (по данным специализированных клиник, в 4–5%).",
                "open_new_page": false,
                "temporarily_disabled": false,
                "therapies_public_count": 42,
                "medical_profiles_public_count": 2
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function index(Request $request, string $locale)
    {
        $valid = Validator($request->all(),[
            'profile_id' => 'integer|nullable',
            'therapy_id' => 'integer|nullable',
            'parent' => 'integer|nullable',
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        $profileId = $request->get('profile_id') ?? null;
        $therapyId = $request->get('therapy_id') ?? null;
        $parent = $request->get('parent') ?? null;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $filterParams = $request->only('country_id', 'region_id', 'city_id');

        return $this->diseaseService->search($page, $rowsPerPage, $searchKey,
            $profileId, $therapyId, $parent, $filterParams, $sorting, $locale);
    }

    /**
     * @api {get} /api/{locale}/disease/{url} Получение заболевания (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetDisease
     * @apiGroup PublicMedical
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 2,
        "name": "Кишечные инфекции",
        "description": "Группа инфекционных болезней, при которых наблюдается преимущественное поражение кишечника. Возбудителями острых кишечных инфекций являются различные виды бактерий и вирусов.",
        "active": true,
        "alias": "kishechnye-infektsii",
        "has_children": true,
        "parent": 1,
        "is_favorite": null,
        "seo": {
            "disease_id": 2,
            "for": "disease-page",
            "h1": null,
            "title": null,
            "meta_description": null,
            "meta_keywords": null
        },
        "medical_profiles_public": [
            {
                "id": 9,
                "name": "Болезни органов дыхания",
                "alias": "bolezni-organov-dykhaniya",
                "seo": {
                    "id": 22681,
                    "medical_profile_id": 9,
                    "title": null,
                    "order": 337
                }
            },
            {
                "id": 10,
                "name": "Болезни желудочно-кишечного тракта",
                "alias": "bolezni-zheludochno-kishechnogo-trakta",
                "seo": {
                    "id": 22672,
                    "medical_profile_id": 10,
                    "title": null,
                    "order": 328
                }
            }
        ],
        "therapies_public": [],
        "parent_info": {
            "id": 1,
            "parent": 0,
            "name": "Некоторые инфекционные и паразитарные болезни",
            "alias": "nekotorye-infektsionnye-i-parazitarnye-bolezni"
        }
    }
     *
     * @param string $locale
     * @param string $url
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $url)
    {
        $disease = $this->diseaseService->getDisease(null, $url, $locale);

        return response()->json($disease, 200);
    }

    /**
     * @api {get} /api/{locale}/favorite/disease Получение списка избранных заболеваний (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetDisease
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
                "id": 665,
                "parent": 659,
                "name": "Другие виды бластомикоза",
                "alias": "drugie-vidy-blastomikoza",
                "has_children": false,
                "therapies_public_count": "0",
                "medical_profiles_public_count": "1"
            },
            {
                "id": 1725,
                "parent": 1717,
                "name": "Других органов пищеварения",
                "alias": "drugikh-organov-pishchevareniya",
                "has_children": false,
                "therapies_public_count": "0",
                "medical_profiles_public_count": "2"
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\UnsupportLocaleException
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

        $diseases = $this->diseaseService->getFavorites($page, $rowsPerPage, $searchKey, $sorting,
            $locale, $userId);

        return response()->json($diseases, 200);
    }

    /**
     * @api {post} /api/{locale}/favorite/disease Добавление в избранное заболевания (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName AddDisease
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  disease_id ID заболевания
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
            'disease_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $diseaseId = $request->get('disease_id');

        $this->diseaseService->addFavorite($userId, $diseaseId);

        return response()->json(['message' => 'Добавлено в избранное'], 200);
    }

    /**
     * @api {delete} /api/{locale}/favorite/disease Удаление из избранного заболевания (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName DeleteDisease
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  disease_id ID заболевания
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
            'disease_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $diseaseId = $request->get('disease_id');

        $this->diseaseService->deleteFavorite($userId, $diseaseId);

        return response()->json(['message' => 'Удалено из избранного'], 200);
    }
}
