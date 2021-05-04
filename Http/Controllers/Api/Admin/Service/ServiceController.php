<?php

namespace App\Http\Controllers\Api\Admin\Service;

use App\Exceptions\ApiProblemException;
use App\Services\ServicesService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * @var ServicesService
     */
    protected $servicesService;

    /**
     * ServiceController constructor.
     */
    public function __construct()
    {
        $this->servicesService = new ServicesService();
    }

    /**
     * @api {post} /api/admin/service/category Добавление категории
     * @apiVersion 0.1.0
     * @apiName SaveCategory
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name_ru Название на русск.
     * @apiParam {string} name_en Название на анг.
     * @apiParam {file} [image] Иконка категории
     * @apiParam {integer} [sorting] порядок сортировки
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name_ru": "Новый сервис",
        "name_en": "New servicw",
        "image": "/storage/service_icons/oZWnRamUVqOs3UmNJGr9zMVszNCXHWg0YTrce8TJ.jpeg",
        "active": true,
        "updated_at": "2019-01-22 14:16:46",
        "created_at": "2019-01-22 14:16:46",
        "id": 13
    }
     *
     * @param Request $request
     * @return \App\Models\ServiceCategory|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addCategory(Request $request)
    {
        $valid = Validator($request->all(),[
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->servicesService->addCategory($request);
    }

    /**
     * @api {get} /api/admin/service/category Поиск категорий
     * @apiVersion 0.1.0
     * @apiName SearchCategory
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 11,
        "items": [
            {
                "id": 2,
                "name_ru": "Активный отдых и спорт",
                "name_en": "Sport",
                "image": null,
                "active": true,
                "services_count": "9"
            },
            {
                "id": 3,
                "name_ru": "Услуги",
                "name_en": "Services",
                "image": null,
                "active": true,
                "services_count": "3"
            },
            {
                "id": 4,
                "name_ru": "Питание",
                "name_en": "Food",
                "image": null,
                "active": true,
                "services_count": "9"
            }
        ]
    }
     *
     * @param Request $request
     * @return array
     */
    public function searchCategory(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        return $this->servicesService->searchCategory($page, $rowsPerPage, $searchKey);
    }

    /**
     * @api {get} /api/admin/service/category/{categoryId} Получение категории
     * @apiVersion 0.1.0
     * @apiName GetCategory
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 6,
        "name_ru": "Spa и Велнес",
        "name_en": "SPA & Wellness",
        "image": "/storage/service_icons/h4BHghWhlnWR7CxRR87GSwz0GSPw7s2pogUbmHdO.jpeg",
        "active": true,
        "created_at": null,
        "updated_at": "2019-01-22 13:13:30",
        "services": [
            {
                "id": 17,
                "name_ru": "Финская сауна",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 18,
                "name_ru": "Русская баня",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 20,
                "name_ru": "Сауна",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 26,
                "name_ru": "Фитнес",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 30,
                "name_ru": "Массаж",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 39,
                "name_ru": "Баня",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 41,
                "name_ru": "Фитобар",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 42,
                "name_ru": "Бювет с минеральной водой",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 46,
                "name_ru": "Солярий",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            },
            {
                "id": 47,
                "name_ru": "Spa - процедуры",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 6,
                "filter_name_ru": null,
                "filter_name_en": null,
                "is_filter": false
            }
        ]
    }
     *
     * @param int $categoryId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getCategory(int $categoryId)
    {
        return $this->servicesService->getCategory($categoryId);
    }

    /**
     * @api {post} /api/admin/service/category/{categoryId} Редактирование категории
     * @apiVersion 0.1.0
     * @apiName EditCategory
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name_ru] Название на русск.
     * @apiParam {string} [name_en] Название на анг.
     * @apiParam {file} [image] Иконка категории
     * @apiParam {integer} [sorting] порядок сортировки
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "services": [
            "Обновлено"
        ]
    }
     *
     *
     * @param Request $request
     * @param int $categoryId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function editCategory(Request $request, int $categoryId)
    {
        $valid = Validator($request->all(),[
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'active' => 'boolean'
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $updated = $this->servicesService->editCategory($request, $categoryId);

        if ($updated){

            return response()->json(['services' =>
                ['Обновлено']
            ], 200);
        } else {

            return response()->json(['services' =>
                ['Не найдено']
            ], 404);
        }
    }

    /**
     * @api {delete} /api/admin/service/category/{categoryId} Удаление категории
     * @apiVersion 0.1.0
     * @apiName DeleteCategory
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "services": [
            "Удалено"
        ]
    }
     *
     * @param int $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCategory(int $categoryId)
    {
        $response = $this->servicesService->deleteCategory($categoryId);

        return response()->json($response['message'], $response['status']);
    }




    /**
     * @api {post} /api/admin/service Добавление услуги
     * @apiVersion 0.1.0
     * @apiName AddService
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name_ru Название на русск.
     * @apiParam {string} name_en Название на анг.
     * @apiParam {integer} service_category_id Id категории
     * @apiParam {string} [filter_name_ru] название фильтра на русс.
     * @apiParam {string} [filter_name_en] название фильтра на анг.
     * @apiParam {boolean} [is_filter] используется ли в фильтре
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "service_category_id": 4,
        "name_ru": "NmaeRu",
        "name_en": "NameEn",
        "filter_name_ru": "filterRu",
        "filter_name_en": "filterEn",
        "is_filter": false,
        "active": true,
        "updated_at": "2019-01-22 15:51:46",
        "created_at": "2019-01-22 15:51:46",
        "id": 54
    }
     *
     * @param Request $request
     * @return \App\Models\Service|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addService(Request $request)
    {
        $valid = Validator($request->all(),[
            'service_category_id' => 'required|integer',
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'filter_name_ru' => 'string|max:255|nullable',
            'filter_name_en' => 'string|max:255|nullable',
            'is_filter' => 'boolean',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $data = $request->only('service_category_id', 'name_ru', 'name_en', 'filter_name_ru',
            'filter_name_en', 'is_filter', 'active');

        return $this->servicesService->addService($data);
    }

    /**
     * @api {get} /api/admin/service Поиск услуг
     * @apiVersion 0.1.0
     * @apiName SearchService
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска

     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 46,
        "items": [
            {
                "id": 9,
                "name_ru": "Игровая площадка",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 1,
                "filter_name_ru": "Игровая площадка",
                "filter_name_en": null,
                "is_filter": false,
                "active": true,
                "category": {
                    "id": 9,
                    "name_ru": "Новый сервис",
                    "name_en": "New servicw",
                    "image": "/storage/service_icons/KLQnSCtZAh5FwQgqThj5tpQY0ttO5PMcji8BzBXu.jpeg",
                    "created_at": "2019-01-22 13:09:17",
                    "updated_at": "2019-01-22 13:09:17",
                    "sorting": 0
                }
            },
            {
                "id": 10,
                "name_ru": "Тренажерный зал",
                "name_en": null,
                "created_at": null,
                "updated_at": null,
                "service_category_id": 2,
                "filter_name_ru": "Тренажерный зал",
                "filter_name_en": null,
                "is_filter": false,
                "active": true,
                "category": {
                    "id": 10,
                    "name_ru": "Новый сервис",
                    "name_en": "New service",
                    "image": "/storage/service_icons/21IPs3no3LuuFC2Zkf7hcFPrmwgWXdfnaR3freWm.jpeg",
                    "created_at": "2019-01-22 13:09:22",
                    "updated_at": "2019-01-22 13:09:22",
                    "sorting": 0
                }
            }
        ]
    }
     *
     * @param Request $request
     * @return array
     */
    public function searchService(Request $request)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        return $this->servicesService->searchService($page, $rowsPerPage, $searchKey);
    }

    /**
     * @api {get} /api/admin/service/{serviceId} Получение услуги
     * @apiVersion 0.1.0
     * @apiName GetService
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 8,
        "name_ru": "Лобби-бар",
        "name_en": null,
        "created_at": null,
        "updated_at": "2019-05-16 11:34:09",
        "service_category_id": 4,
        "filter_name_ru": "Лобби-бар",
        "filter_name_en": null,
        "is_filter": false,
        "active": false,
        "alias": "lobbi-bar",
        "seo": {
            "id": 35142,
            "for": "service",
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
            "url": "lobbi-bar",
            "meta_description_ru": null,
            "meta_keywords_ru": null,
            "offer_id": null,
            "h1_en": null,
            "title_en": null,
            "meta_description_en": null,
            "meta_keywords_en": null,
            "about_id": null,
            "partner_id": null,
            "publication_id": null,
            "order": 320,
            "service_id": 8
        }
    }
     *
     * @param int $serviceId
     * @throws ApiProblemException
     * @return mixed
     */
    public function getService(int $serviceId)
    {
        return $this->servicesService->getService($serviceId);
    }

    /**
     * @api {put} /api/admin/service Редактирование услуги
     * @apiVersion 0.1.0
     * @apiName EditService
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name_ru] Название на русск.
     * @apiParam {string} [name_en] Название на анг.
     * @apiParam {integer} [service_category_id] Id категории
     * @apiParam {string} [filter_name_ru] название фильтра на русс.
     * @apiParam {string} [filter_name_en] название фильтра на анг.
     * @apiParam {boolean} [is_filter] используется ли в фильтре
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "services": [
            "Обновлено"
        ]
    }
     *
     * @param Request $request
     * @param int $serviceId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function editService(Request $request, int $serviceId)
    {
        $valid = Validator($request->all(),[
            'service_category_id' => 'integer',
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'filter_name_ru' => 'string|max:255|nullable',
            'filter_name_en' => 'string|max:255|nullable',
            'is_filter' => 'boolean',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $data = $request->only('service_category_id', 'name_ru', 'name_en', 'filter_name_ru',
            'filter_name_en', 'is_filter', 'active');
        $this->servicesService->editService($data, $serviceId);

        return response()->json(['services' =>
            ['Обновлено']
        ], 200);
    }

    /**
     * @api {delete} /api/admin/service/{serviceId} Удаление услуги
     * @apiVersion 0.1.0
     * @apiName DeleteService
     * @apiGroup AdminService
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "services": [
            "Удалено"
        ]
    }
     *
     * @param int $serviceId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteService(int $serviceId)
    {
        $this->servicesService->deleteService($serviceId);

        return response()->json(['services' =>
            ['Удалено']
        ], 200);
    }
}
