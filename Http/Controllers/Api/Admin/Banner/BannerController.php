<?php

namespace App\Http\Controllers\Api\Admin\Banner;

use App\Rules\IsArray;
use App\Rules\PageScopeRule;
use App\Services\BannerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BannerController extends Controller
{
    protected $bannerService;

    public function __construct()
    {
        $this->bannerService = new BannerService();
    }

    /**
     * @api {post} /api/admin/banner добавление баннера
     * @apiVersion 0.1.0
     * @apiName AddBanner
     * @apiGroup AdminBanner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name_ru Название на русс.
     * @apiParam {string} name_en Название на анг.
     * @apiParam {string} url_ru  Ссылка для перехода русской версии.
     * @apiParam {string} url_en  Ссылка для перехода английской версии.
     * @apiParam {file} image_ru  Изображение русской версии
     * @apiParam {file} image_en  Изображение английской версии
     * @apiParam {boolean} [active] Активность
     * @apiParam {boolean} [show_offer_request] Показывать "заказать предложение"
     * @apiParam {json}  page_scope Массив видимости ["main", "disease", "therapy", "about"]
     * @apiParam {string} [code_ru] Код анимации русс.
     * @apiParam {string} [code_en] Код анимации анг.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "name_ru": "Title Ru",
        "name_en": "Title En",
        "image_ru": "/storage/banners/R6Ih9UPZNbJLOUsnZHSgRlbLOJHbsi07RV8dPVfq.jpeg",
        "image_en": "/storage/banners/ydY4z6QO1jUNux6msSoc0jacK5nu28d3soc2ZJGA.jpeg",
        "url_ru": "url_ru",
        "url_en": "url_en",
        "active": false,
        "page_scope": [
            "main",
            "disease",
            "medical_profile"
        ],
        "count_shows": 0,
        "count_clicks": 0,
        "created_at": "2019-04-01 11:44:42",
        "updated_at": "2019-04-01 11:52:20"
        "code_ru": null,
        "code_en": null
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'url_ru' => 'required|string|max:255',
            'url_en' => 'required|string|max:255',
            'code_ru' => 'string|nullable',
            'code_en' => 'string|nullable',
            'active' => 'boolean',
            'show_offer_request' => 'boolean',
            'image_ru' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'page_scope' => ['required', new PageScopeRule ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $banner = $this->bannerService->add($request);

        return response()->json($banner, 201);
    }

    /**
     * @api {get} /api/admin/banner Получение и поиск баннеров
     * @apiVersion 0.1.0
     * @apiName SearchBanner
     * @apiGroup AdminBanner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [page_scope] Массив видимости ["main", "disease", "therapy", "about"]
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc", "created_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 1,
                "name_ru": "Title Ru",
                "name_en": "Title En",
                "image_ru": "/storage/banners/MVWcW0d5WFpssmf2ZAeKzDGNjXJrwKK5vRVzMllm.jpeg",
                "image_en": "/storage/banners/tuirbAsPB7A8IlQxV0pDqnd5q46IInCLD7wKSkym.jpeg",
                "url_ru": "url_ru",
                "url_en": "url_en",
                "active": false,
                "show_offer_request": false,
                "page_scope": [
                    "main",
                    "disease",
                    "medical_profile"
                ],
                "count_shows": 0,
                "count_clicks": 0,
                "created_at": "2019-04-01 11:43:32",
                "updated_at": "2019-04-01 11:43:32"
            },
            {
                "id": 2,
                "name_ru": "Title Ru",
                "name_en": "Title En",
                "image_ru": "/storage/banners/Hl6l3Q8hIWdqTOPKTaR2TYlwalBZ2DccGTDbtxaq.jpeg",
                "image_en": "/storage/banners/m1GfVB3PzJGfNI4YTzoE6uOqQQsSYas7VQA4enUb.jpeg",
                "url_ru": "url_ru",
                "url_en": "url_en",
                "active": false,
                "show_offer_request": false,
                "page_scope": [
                    "main",
                    "disease",
                    "medical_profile"
                ],
                "count_shows": 0,
                "count_clicks": 0,
                "created_at": "2019-04-01 11:43:52",
                "updated_at": "2019-04-01 11:43:52"
            }
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $valid = Validator($request->all(),[
            'page_scope' => [ new PageScopeRule ],
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');

        $pageScope = $request->get('page_scope');
        if (!is_array($pageScope)) $pageScope = json_decode($pageScope, true);

        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $banners = $this->bannerService->search($page, $rowsPerPage, $searchKey, $pageScope, $sorting);

        return response()->json($banners, 200);
    }

    /**
     * @api {post} /api/admin/banner/{bannerId} Редактирование баннера
     * @apiVersion 0.1.0
     * @apiName EditBanner
     * @apiGroup AdminBanner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name_ru] Название на русс.
     * @apiParam {string} [name_en] Название на анг.
     * @apiParam {string} [url_ru]  Ссылка для перехода русской версии.
     * @apiParam {string} [url_en]  Ссылка для перехода английской версии.
     * @apiParam {file} [image_ru]  Изображение русской версии
     * @apiParam {file} [image_en]  Изображение английской версии
     * @apiParam {boolean} [active] Активность
     * @apiParam {boolean} [show_offer_request] Показывать "заказать предложение"
     * @apiParam {json}  [page_scope] Массив видимости ["main", "disease", "therapy", "about"]
     * @apiParam {string} [code_ru] Код анимации русс.
     * @apiParam {string} [code_en] Код анимации анг.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "name_ru": "Title Ru",
        "name_en": "Title En",
        "image_ru": "/storage/banners/R6Ih9UPZNbJLOUsnZHSgRlbLOJHbsi07RV8dPVfq.jpeg",
        "image_en": "/storage/banners/ydY4z6QO1jUNux6msSoc0jacK5nu28d3soc2ZJGA.jpeg",
        "url_ru": "url_ru",
        "url_en": "url_en",
        "active": false,
        "show_offer_request": false,
        "page_scope": [
            "main",
            "disease",
            "medical_profile"
        ],
        "count_shows": 0,
        "count_clicks": 0,
        "created_at": "2019-04-01 11:44:42",
        "updated_at": "2019-04-01 11:52:20"
        "code_ru": null,
        "code_en": null
    }
     *
     * @param Request $request
     * @param int $bannerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function edit(Request $request, int $bannerId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'url_ru' => 'string|max:255',
            'url_en' => 'string|max:255',
            'code_ru' => 'string|nullable',
            'code_en' => 'string|nullable',
            'active' => 'boolean',
            'show_offer_request' => 'boolean',
            'image_ru' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'page_scope' => [ new PageScopeRule ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $banner = $this->bannerService->edit($request, $bannerId);

        return response()->json($banner, 200);
    }

    /**
     * @api {get} /api/admin/banner/{bannerId} Получение баннера
     * @apiVersion 0.1.0
     * @apiName GetBanner
     * @apiGroup AdminBanner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "name_ru": "Title Ru",
        "name_en": "Title En",
        "image_ru": "/storage/banners/R6Ih9UPZNbJLOUsnZHSgRlbLOJHbsi07RV8dPVfq.jpeg",
        "image_en": "/storage/banners/ydY4z6QO1jUNux6msSoc0jacK5nu28d3soc2ZJGA.jpeg",
        "url_ru": "url_ru",
        "url_en": "url_en",
        "active": false,
        "show_offer_request": false,
        "page_scope": [
            "main",
            "disease",
            "medical_profile"
        ],
        "count_shows": 0,
        "count_clicks": 0,
        "created_at": "2019-04-01 11:44:42",
        "updated_at": "2019-04-01 11:52:20"
        "code_ru": null,
        "code_en": null
    }
     *
     * @param int $bannerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(int $bannerId)
    {
        $banner = $this->bannerService->get($bannerId);

        return response()->json($banner, 200);
    }

    /**
     * @api {delete} /api/admin/banner/{bannerId} Удаление баннера
     * @apiVersion 0.1.0
     * @apiName DeleteBanner
     * @apiGroup AdminBanner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": [ "баннер удален"]
     }
     *
     * @param int $bannerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function delete(int $bannerId)
    {
        $this->bannerService->delete($bannerId);

        return response()->json([
            'message' => ['Баннер удален']
        ], 200);
    }

    /**
     * @api {get} /api/{locale}/banner Показ баннера (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ShowBanner
     * @apiGroup PublicBanner
     *
     * @apiParam {json}  page_scope Массив видимости ["main", "disease", "therapy", "about"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name": "Title En",
        "image": "/storage/banners/jsUEAj7JgtbsJJTfHUz3EttRlSNjCbxduSHuAU4G.jpeg",
        "url": "url_en"
        "show_offer_request": true
        "code": "<code></code>"
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getBannerPublic(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page_scope' => [ 'required', new PageScopeRule ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $pageScope = $request->get('page_scope');
        if (!is_array($pageScope)) $pageScope = json_decode($pageScope, true);

        $banner = $this->bannerService->getBannerPublic($locale, $pageScope);

        return response()->json($banner, 200);
    }
}
