<?php

namespace App\Http\Controllers\Api\Admin\About;

use App\Exceptions\ApiProblemException;
use App\Models\About;
use App\Rules\IsArray;
use App\Services\AboutService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    /**
     * @var AboutService
     */
    protected $aboutService;

    /**
     * AboutController constructor.
     */
    public function __construct()
    {
        $this->aboutService = new AboutService();
    }

    /**
     * @api {get} /api/admin/about/{aboutId}  Получение данных раздела О проекте
     * @apiVersion 0.1.0
     * @apiName GetAboutPage
     * @apiGroup AdminAboutPage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "title_ru": "TitleRu",
        "title_en": null,
        "image": null,
        "about_ru": "AboutRu",
        "about_en": null,
        "is_published": true,
        "publish_date": null,
        "alias": "alias-2",
        "parent": 0,
        "seo": {
            "id": 34493,
            "for": "about",
            "object_id": null,
            "news_id": null,
            "country_id": null,
            "region_id": null,
            "city_id": null,
            "disease_id": null,
            "therapy_id": null,
            "medical_profile_id": null,
            "h1_ru": null,
            "title_ru": null,
            "url": "qweqw",
            "meta_description_ru": null,
            "meta_keywords_ru": null,
            "offer_id": null,
            "h1_en": null,
            "title_en": null,
            "meta_description_en": null,
            "meta_keywords_en": null,
            "about_id": 2
        }
    }
     *
     * @param int $aboutId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $aboutId)
    {
        $about = $this->aboutService->get($aboutId);

        return response()->json($about, 200);
    }

    /**
     * @api {post} /api/admin/about  Добавление раздела "О проекте"
     * @apiVersion 0.1.0
     * @apiName AddAboutPage
     * @apiGroup AdminAboutPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [prent] ID родительского раздела
     * @apiParam {string} title_ru Заголовок на русском
     * @apiParam {string} title_en Заголовок на анг.
     * @apiParam {string} [about_ru] Описание на русском
     * @apiParam {string} [about_en] Описание на анг.
     * @apiParam {file} [image] Изображение
     * @apiParam {boolean} [is_published] Опубликовано или нет
     * @apiParam {boolean} [default_page] Главный радел
     * @apiParam {string} [publish_date] Дата публикации.
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "title_ru": "TitleRu",
        "title_en": null,
        "about_ru": "AboutRu",
        "about_en": null,
        "is_published": "1",
        "publish_date": null,
        "parent": "1",
        "id": 3
    }
     *
     * @param Request $request
     * @return About|\Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $valid = Validator($request->all(),[
            'parent' => 'integer|nullable',
            'image' => 'file|image|nullable',
            'title_ru' => 'required|string|max:255',
            'title_en' => 'string|max:255|nullable',
            'about_ru' => 'string|nullable',
            'about_en' => 'string|nullable',
            'is_published' => 'boolean',
            'default_page' => 'boolean',
            'publish_date' => 'string|max:255|nullable',
        ]);

        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $about = $this->aboutService->add($request);

        return response()->json($about, 201);
    }

    /**
     * @api {post} /api/admin/about/{aboutId}  Редактирование раздела "О проекте"
     * @apiVersion 0.1.0
     * @apiName EditAboutPage
     * @apiGroup AdminAboutPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [prent] ID родительского раздела
     * @apiParam {string} [title_ru] Заголовок на русском
     * @apiParam {string} [title_en] Заголовок на анг.
     * @apiParam {string} [about_ru] Описание на русском
     * @apiParam {string} [about_en] Описание на анг.
     * @apiParam {file} [image] Изображение
     * @apiParam {boolean} [is_published] Опубликовано или нет
     * @apiParam {boolean} [default_page] Главный раздел
     * @apiParam {string} [publish_date] Дата публикации.
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "title_ru": "TitleRu",
        "title_en": null,
        "image": "/storage/about/rC0XGKEqZAk5f35JjHZGI3nJDaAgkkScI8eg1UAE.jpeg",
        "about_ru": "AboutRu",
        "about_en": null,
        "is_published": true,
        "publish_date": null,
        "alias": null,
        "parent": 0
    }
     *
     * @param Request $request
     * @param $aboutId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function edit(Request $request, $aboutId)
    {
        $valid = Validator($request->all(),[
            'parent' => 'integer|nullable',
            'image' => 'file|image|nullable',
            'title_ru' => 'string|max:255',
            'title_en' => 'string|max:255|nullable',
            'about_ru' => 'string|nullable',
            'about_en' => 'string|nullable',
            'is_published' => 'boolean',
            'default_page' => 'boolean',
            'publish_date' => 'string|max:255|nullable',
        ]);

        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $about = $this->aboutService->edit($request, $aboutId);

        return response()->json($about, 200);
    }

    /**
     * @api {get} /api/admin/about  Получение списка "О проекте"
     * @apiVersion 0.1.0
     * @apiName SearchAboutPage
     * @apiGroup AdminAboutPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 2,
                "title_ru": "TitleRu",
                "title_en": null,
                "image": null,
                "about_ru": "AboutRu",
                "about_en": null,
                "is_published": true,
                "publish_date": null,
                "alias": "alias-2",
                "parent": 0,
                "about_parent": null
            },
            {
                "id": 3,
                "title_ru": "TitleRu",
                "title_en": null,
                "image": "/storage/about/rC0XGKEqZAk5f35JjHZGI3nJDaAgkkScI8eg1UAE.jpeg",
                "about_ru": "AboutRu",
                "about_en": null,
                "is_published": true,
                "publish_date": null,
                "alias": "alias-3",
                "parent": 2,
                "about_parent": {
                    "id": 2,
                    "title_ru": "TitleRu"
                }
            },
            {
                "id": 4,
                "title_ru": "TitleRu",
                "title_en": null,
                "image": null,
                "about_ru": "",
                "about_en": "",
                "is_published": true,
                "publish_date": null,
                "alias": "alias-4",
                "parent": 3,
                "about_parent": {
                    "id": 3,
                    "title_ru": "TitleRu"
                }
            }
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function search(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $abouts = $this->aboutService->search($page, $rowsPerPage, $searchKey, $sorting);

        return response()->json($abouts, 200);
    }

    /**
     * @api {delete} /api/admin/about/{aboutId}  Удаление раздела О проекте
     * @apiVersion 0.1.0
     * @apiName DeleteAboutPage
     * @apiGroup AdminAboutPage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": "раздел удален"
     }
     *
     * @param int $aboutId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function delete(int $aboutId)
    {
        $this->aboutService->delete($aboutId);

        return response()->json(['message' => 'раздел удален'], 200);
    }
}
