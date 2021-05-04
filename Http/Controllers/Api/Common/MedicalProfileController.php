<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\MedicalProfileService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MedicalProfileController extends Controller
{
    /**
     * @var MedicalProfileService
     */
    protected $medicalProfileService;

    /**
     * MedicalProfileController constructor.
     */
    public function __construct()
    {
        $this->medicalProfileService = new MedicalProfileService();
    }

    /**
     * @api {get} /api/{locale}/medical-profile Получение и поиск мед. профилей (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchMedProfile
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
        "total": 17,
        "items": [
            {
                "id": 6,
                "name": "Болезни эндокринной системы и обмена веществ",
                "url": null,
                "diseases_public_count": "465"
            },
            {
                "id": 5,
                "name": "nameRu",
                "url": null,
                "diseases_public_count": "617"
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

        $profiles = $this->medicalProfileService->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, $locale);

        return response()->json($profiles, 200);
    }

    /**
     * @api {get} /api/{locale}/medical-profile/{url} Получение мед. профиля (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetMedProfile
     * @apiGroup PublicMedical
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 20,
        "name": "Реабилитация онкологических заболеваний",
        "description": "<p>Онколо́гия (от греч. &oacute;nkos &mdash; тяжесть, груз) &mdash; раздел медицины, изучающий доброкачественные и злокачественные опухоли, механизмы и закономерности их возникновения и развития, методы их профилактики, диагностики и лечения[1].</p>\\r\\n<p>Онкологические болезни представляют собой обширный и разнородный класс заболеваний[2]. Онкологические заболевания являются системными и затрагивают, так или иначе, все органы и системы человека. Существует множество форм и вариантов течения рака. Хотя пациенты часто воспринимают онкологический диагноз как приговор, далеко не все даже злокачественные опухоли приводят к смерти[3]. Современные исследования продемонстрировали, что у каждого человека в организме регулярно возникают раковые клетки и микроопухоли, которые гибнут и рассасываются под воздействием системы противоопухолевого иммунитета[4][5].</p>",
        "active": true,
        "alias": "reabilitatsiya-onkologicheskikh-zabolevaniy-20",
        "therapies_public": [
            {
                "id": 239,
                "alias": "elektroforez-lekarstvennykh-sredstv-pri-boleznyakh-pecheni-i-zhelchevyvodyashchikh-putey-239",
                "name": "Электрофорез лекарственных средств при болезнях печени и желчевыводящих путей"
            }
        ],
        "seo": {
            "medical_profile_id": 20,
            "for": "medical-profile-page",
            "h1": null,
            "title": null,
            "meta_description": null,
            "meta_keywords": null
        },
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
     *
     * @param string $locale
     * @param string $url
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $url)
    {
        $profile = $this->medicalProfileService->getProfile(null, $url, $locale);

        return response()->json($profile, 200);
    }

    /**
     * @api {get} /api/{locale}/favorite/medical-profile Получение списка избранных мед. профилей (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetMedicalProfiles
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
                "id": 5,
                "name": "Болезни нервной системы",
                "alias": "bolezni-nervnoy-sistemy",
                "diseases_public_count": "103"
            },
            {
                "id": 6,
                "name": "Болезни эндокринной системы и обмена веществ",
                "alias": "bolezni-endokrinnoy-sistemy-i-obmena-veshchestv",
                "diseases_public_count": "121"
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

        $medicalProfiles = $this->medicalProfileService->getFavorites($page, $rowsPerPage, $searchKey, $sorting,
            $locale, $userId);

        return response()->json($medicalProfiles, 200);
    }

    /**
     * @api {post} /api/{locale}/favorite/medical-profile Добавление в избранное мед. профиля (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName AddMedicalProfiles
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  medical_profile_id ID мед. профиля
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
           'medical_profile_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $medicalProfileId = $request->get('medical_profile_id');

        $this->medicalProfileService->addFavorite($userId, $medicalProfileId);

        return response()->json(['message' => 'Добавлено в избранное'], 200);
    }

    /**
     * @api {delete} /api/{locale}/favorite/medical-profile Удаление из избранного мед. профиля (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName DeleteMedicalProfiles
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  medical_profile_id ID мед. профиля
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
            'medical_profile_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $medicalProfileId = $request->get('medical_profile_id');

        $this->medicalProfileService->deleteFavorite($userId, $medicalProfileId);

        return response()->json(['message' => 'Удалено из избранного'], 200);
    }
}
