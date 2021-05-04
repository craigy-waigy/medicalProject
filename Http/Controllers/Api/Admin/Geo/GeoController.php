<?php

namespace App\Http\Controllers\Api\Admin\Geo;

use App\Exceptions\ApiProblemException;
use App\Models\Region;
use App\Rules\IsArray;
use App\Services\GeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GeoController extends Controller
{
    /**
     * @var GeoService
     */
    protected $geoService;

    /**
     * GeoController constructor.
     */
    public function __construct()
    {
        $this->geoService = new GeoService();
    }

    /**
     * @api {get} /api/admin/country поиск - получение стран
     * @apiVersion 0.1.0
     * @apiName SearchCountry
     * @apiGroup GeoAdmin
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
        "rowsPerPage": 3,
        "total": 192,
        "items": [
            {
                "id": 132,
                "name_ru": "Австрия",
                "name_en": "Republic of Austria",
                "latitude": "47.33333",
                "longitude": "13.33333",
                "crop_image": null,
                "alias": "avstriya-132",
                "is_visible": false,
                "profiles_block_title_ru": "",
                "profiles_block_title_en": "",
                "regions_block_title_ru": "",
                "regions_block_title_en": ""
            },
        ]
    }
     * @param Request $request
     * @return \App\Models\Country[]|\Illuminate\Database\Eloquent\Collection
     * @throws ApiProblemException
     */
    public function searchCountry(Request $request)
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

        return $this->geoService->getCountries($page, $rowsPerPage, $searchKey, $sorting);
    }

    /**
     * Создание страны
     *
     * @param Request $request
     * @return \App\Models\Country|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createCountry(Request $request)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'name_ru'=> 'unique:countries|required|string|max:255',
            'name_en'=> 'unique:countries|string|max:255',
            'slug'=> 'string|max:255',
            'is_visible'=> 'boolean',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->geoService->createCountry($request);
    }

    /**
     * @api {post} /api/admin/region создание региона
     * @apiVersion 0.1.0
     * @apiName CreateRegion
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {integer} country_id id страны
     * @apiParam {string} name_ru название на русском
     * @apiParam {string} name_en название на русском
     * @apiParam {boolean} [is_visible] видимость
     * @apiParam {string} [description_ru] описание на русском
     * @apiParam {string} [description_en] описание на английском
     * @apiParam {file} [image] изображение
     * @apiParam {file} [crop_image] среднего размера изображение
     * @apiParam {file} [image_en] изображение
     * @apiParam {file} [crop_image_en] среднего размера изображение
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {string} [profiles_block_title_ru] заголовок блоков профилей на русском
     * @apiParam {string} [profiles_block_title_en] заголовок блоков профилей на английском
     * @apiParam {string} [cities_block_title_ru] заголовок блоков городов на русском
     * @apiParam {string} [cities_block_title_en] заголовок блоков городов на английском
     * @apiParam {string} [cities_block_title_en] заголовок блоков городов на английском
     * @apiParam {string} [cities_block_title_en] заголовок блоков городов на английском
     * @apiParam {float} [latitude] широта локации
     * @apiParam {float} [longitude] долгота локации
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "country_id": "88",
        "name_ru": "Новый регион 24",
        "name_en": "nre region 34",
        "is_visible": true,
        "description_ru": "описание",
        "description_en": "desc",
        "tags_ru": [],
        "tags_en": [],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "cities_block_title_ru": "",
        "cities_block_title_en": "",
        "latitude": "15.11111",
        "longitude": "26.11111",
        "updated_at": "2020-04-20 14:30:04",
        "created_at": "2020-04-20 14:30:04",
        "id": 3
    }
     * @throws ApiProblemException
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function createRegion(Request $request)
    {
        $valid = Validator($request->all(),[
            'country_id' => 'required|integer|exists:countries,id',
            'name_ru'=> 'unique:regions|required|string|max:255',
            'name_en'=> 'unique:regions|string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'crop_image' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'crop_image_en' => 'file|image|max:5128|nullable',
            'is_visible'=> 'boolean',
            'tags_ru'=> [ new IsArray ],
            'tags_en'=> [ new IsArray ],
            'latitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
            'longitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
        ], [
            'latitude.regex' => 'Формат широты неверный (корректный формат 12.123456789)',
            'longitude.regex' => 'Формат долготы неверный (корректный формат 12.123456789)',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->geoService->createRegion($request);
    }

    /**
     * @api {post} /api/admin/country/{countryId} редактирование стран
     * @apiVersion 0.1.0
     * @apiName EditCountry
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {boolean} [is_visible] видимость
     * @apiParam {string} [description_ru] нипасание на русском
     * @apiParam {string} [description_en] нипасание на английском
     * @apiParam {string} [recomm_ru] рекомендации на русском
     * @apiParam {string} [recomm_en] рекомендации на английском
     * @apiParam {file} [image] изображение
     * @apiParam {file} [crop_image] среднего размера изображение
     * @apiParam {file} [image_en] изображение
     * @apiParam {file} [crop_image_en] среднего размера изображение
     *
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {string} [profiles_block_title_ru] заголовок блоков профилей на русском
     * @apiParam {string} [profiles_block_title_en] заголовок блоков профилей на английском
     * @apiParam {string} [regions_block_title_ru] заголовок блоков регионов на русском
     * @apiParam {string} [regions_block_title_en] заголовок блоков регионов на английском
     * @apiParam {float} [latitude] широта локации
     * @apiParam {float} [longitude] долгота локации
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name_ru": "test 10 Country Ru",
        "name_en": "test 10 Country En",
        "created_at": null,
        "updated_at": "2020-04-20 13:53:09",
        "is_visible": true,
        "description_ru": "Description 2 Ru",
        "description_en": "Description 2 En",
        "image": null,
        "crop_image": null,
        "geonameid": 51537,
        "country_code": "SO",
        "latitude": "6",
        "longitude": "48",
        "alias": "somali-2",
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "telephone_code": "252",
        "locale": "en",
            "tags_ru": [
            "курорты",
            "страны"
            ],
        "tags_en": [
            "resorts",
            "countries"
            ],
        "profiles_block_title_ru": "profiles_block_title_ru 1",
        "profiles_block_title_en": "profiles_block_title_en 3",
        "regions_block_title_ru": "regions_block_title_ru 2",
        "regions_block_title_en": "regions_block_title_en 4"
    }
     * @throws ApiProblemException
     * @param Request $request
     * @param int $countryId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateCountry(Request $request, int $countryId)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'crop_image' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'crop_image_en' => 'file|image|max:5128|nullable',
            'name_ru'=> 'string|max:255',
            'name_en'=> 'string|max:255',
            'recomm_ru' => 'string|nullable',
            'recomm_en' => 'string|nullable',
            'is_visible'=> 'boolean',
            'tags_ru'=> [ new IsArray ],
            'tags_en'=> [ new IsArray ],
            'latitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
            'longitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
        ], [
            'latitude.regex' => 'Формат широты неверный (корректный формат 12.123456789)',
            'longitude.regex' => 'Формат долготы неверный (корректный формат 12.123456789)',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $country = $this->geoService->updateCountry($request, $countryId);

        return response($country, 200);
    }

    /**
     * @api {get} /api/admin/country/{countryId} получение страны
     * @apiVersion 0.1.0
     * @apiName GetCountry
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name_ru": "test 10 Country Ru",
        "name_en": "test 10 Country En",
        "created_at": null,
        "updated_at": "2020-04-20 13:53:09",
        "is_visible": true,
        "description_ru": "Description 2 Ru",
        "description_en": "Description 2 En",
        "image": null,
        "crop_image": null,
        "geonameid": 51537,
        "country_code": "SO",
        "latitude": "6",
        "longitude": "48",
        "alias": "somali-2",
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "telephone_code": "252",
        "locale": "en",
        "tags_ru": [
            "курорты",
            "страны"
            ],
        "tags_en": [
            "resorts",
            "countries"
            ],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "regions_block_title_ru": "",
        "regions_block_title_en": "",
        "is_favorite": false,
        "recommendations": [],
        "seo": {
                "country_id": 2,
                "for": "country-page",
                "h1_ru": null,
                "h1_en": null,
                "title_ru": "Сомали",
                "title_en": "Somalia",
                "url": "somali-2",
                "meta_description_ru": null,
                "meta_description_en": null,
                "meta_keywords_ru": null,
                "meta_keywords_en": null
            }
    }
     * @throws ApiProblemException
     * @param int $countryId
     * @return mixed
     */
    public function getCountry(int $countryId)
    {
        return $this->geoService->getCountry($countryId);
    }

    /**
     * Удаление страны
     *
     * @param int $countryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCountry(int $countryId)
    {
        $delete = $this->geoService->deleteCountry($countryId);
        if ($delete){

            return response()->json(['geo' =>[
                'Удалено'
            ]], 200);
        } else {

            return response()->json(['geo' =>[
                'Не найдено'
            ]], 404);
        }
    }

    /**
     * @api {get} /api/admin/region поиск - получение регионов
     * @apiVersion 0.1.0
     * @apiName SearchRegion
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     * @apiParam {integer} [country_id] ID страны
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3639,
        "items": [
            {
                "id": 4153,
                "country_id": 1,
                "name_ru": "Southern Province",
                "name_en": "Southern Province",
                "latitude": "-2.33333",
                "longitude": "29.66667",
                "crop_image": null,
                "alias": null,
                "is_visible": false,
                "profiles_block_title_ru": "",
                "profiles_block_title_en": "",
                "cities_block_title_ru": "",
                "cities_block_title_en": "",
                "country": {
                    "id": 1,
                    "name_ru": "Руанда"
                }
            }
        ]
    }
     *
     * @throws ApiProblemException
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchRegion(Request $request)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $countryId = $request->get('country_id') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        return $this->geoService->getRegions($countryId, $page, $rowsPerPage, $searchKey, $sorting);
    }

    /**
     * @api {post} /api/admin/region/{regionId} редактирование региона
     * @apiVersion 0.1.0
     * @apiName EditRegion
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {boolean} [is_visible] видимость
     * @apiParam {string} [name_ru] название на русском
     * @apiParam {string} [name_en] название на русском
     * @apiParam {string} [description_ru] описание на русском
     * @apiParam {string} [description_en] описание на английском
     * @apiParam {string} [recomm_ru] рекомендации на русском
     * @apiParam {string} [recomm_en] рекомендации на английском
     * @apiParam {file} [image] изображение
     * @apiParam {file} [crop_image] среднего размера изображение
     * @apiParam {file} [image_en] изображение
     * @apiParam {file} [crop_image_en] среднего размера изображение
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {string} [profiles_block_title_ru] заголовок блоков профилей на русском
     * @apiParam {string} [profiles_block_title_en] заголовок блоков профилей на английском
     * @apiParam {string} [cities_block_title_ru] заголовок блоков городов на русском
     * @apiParam {string} [cities_block_title_en] заголовок блоков городов на английском
     * @apiParam {float} [latitude] широта локации
     * @apiParam {float} [longitude] долгота локации

     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 7219,
        "name_ru": "test 2 Region Ru",
        "name_en": "test 2 Region En",
        "country_id": 145,
        "created_at": null,
        "updated_at": "2020-04-20 14:03:28",
        "is_visible": true,
        "description_ru": "Description 22 Ru",
        "description_en": "Description 22 En",
        "image": null,
        "crop_image": null,
        "geonameid": 2523119,
        "latitude": "37.75",
        "longitude": "14.25",
        "alias": null,
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "tags_ru": [
            "курорты",
            "регионы",
            "Италия"
            ],
        "tags_en": [
            "resorts",
            "regions"
            ],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "cities_block_title_ru": "",
        "cities_block_title_en": ""
    }
     * @throws ApiProblemException
     * @param Request $request
     * @param int $regionId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateRegion(Request $request, int $regionId)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'crop_image' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'crop_image_en' => 'file|image|max:5128|nullable',
            'name_ru'=> 'string|max:255',
            'name_en'=> 'string|max:255',
            'recomm_ru' => 'string|nullable',
            'recomm_en' => 'string|nullable',
            'is_visible'=> 'boolean',
            'tags_ru'=> [ new IsArray ],
            'tags_en'=> [ new IsArray ],
            'latitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
            'longitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
        ], [
            'latitude.regex' => 'Формат широты неверный (корректный формат 12.123456789)',
            'longitude.regex' => 'Формат долготы неверный (корректный формат 12.123456789)',
        ]);
        if ($valid->fails())return response($valid->errors(), 400);

        $region = $this->geoService->updateRegion($request, $regionId);

        return response()->json($region, 200);
    }

    /**
     * @api {get} /api/admin/region/{regionId} получение региона
     * @apiVersion 0.1.0
     * @apiName GetRegion
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 7219,
        "name_ru": "test 2 Region Ru",
        "name_en": "test 2 Region En",
        "country_id": 145,
        "created_at": null,
        "updated_at": "2020-04-20 14:03:28",
        "is_visible": true,
        "description_ru": "Description 22 Ru",
        "description_en": "Description 22 En",
        "image": null,
        "crop_image": null,
        "geonameid": 2523119,
        "latitude": "37.75",
        "longitude": "14.25",
        "alias": null,
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "tags_ru": [
                "курорты",
                "регионы",
                "Италия"
            ],
        "tags_en": [
                "resorts",
                "regions"
            ],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "cities_block_title_ru": "",
        "cities_block_title_en": "",
        "is_favorite": false,
        "recommendations": [],
        "seo": null,
        "country": {
                "id": 145,
                "name_ru": "Италия"
            }
    }
     * @throws ApiProblemException
     * @param int $regionId
     * @return mixed
     */
    public function getRegion(int $regionId)
    {
        $region = $this->geoService->getRegion($regionId);

        return response()->json($region, 200);
    }

    /**
     * Удаление региона
     *
     * @param int $regionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRegion(int $regionId)
    {
        $deleted = $this->geoService->deleteRegion($regionId);
        if ($deleted){

            return response()->json(['geo' =>[
                'Удалено'
            ]], 200);
        } else {

            return response()->json(['geo' =>[
                'Не найдено'
            ]], 404);
        }
    }

    /**
     * @api {get} /api/admin/city поиск - получение городов
     * @apiVersion 0.1.0
     * @apiName SearchCity
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     * @apiParam {String} [country_id] ID страны
     * @apiParam {String} [region_id] ID региона
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 26,
        "items": [
            {
                "id": 12125,
                "country_id": 88,
                "region_id": 4751,
                "name_ru": "Марьино",
                "name_en": "Mar'ino",
                "latitude": "45.72922",
                "longitude": "34.26846",
                "crop_image": null,
                "alias": null,
                "is_visible": false,
                "profiles_block_title_ru": "",
                "profiles_block_title_en": "",
                "region": {
                "id": 4751,
                "name_ru": "Республика Крым"
            },
            "country": {
                    "id": 88,
                    "name_ru": "Россия"
                }
            }
        ]
    }
     *
     * @throws ApiProblemException
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchCity(Request $request)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $countryId = $request->get('country_id') ?? null;
        $regionId = $request->get('region_id') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $cities = $this->geoService->getCities($countryId, $page, $rowsPerPage, $searchKey, $regionId, $sorting);

        return response()->json($cities, 200);
    }

    /**
     * @api {post} /api/admin/city/{cityId} редактирование города
     * @apiVersion 0.1.0
     * @apiName EditCity
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {boolean} [is_visible] видимость
     * @apiParam {string} [description_ru] нипасание на русском
     * @apiParam {string} [description_en] нипасание на английском
     * @apiParam {string} [recomm_ru] рекомендации на русском
     * @apiParam {string} [recomm_en] рекомендации на английском
     * @apiParam {file} [image] изображение
     * @apiParam {file} [crop_image] среднего размера изображение
     * @apiParam {file} [image_en] изображение
     * @apiParam {file} [crop_image_en] среднего размера изображение
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {string} [profiles_block_title_ru] заголовок блоков профилей на русском
     * @apiParam {string} [profiles_block_title_en] заголовок блоков профилей на английском
     * @apiParam {float} [latitude] широта локации
     * @apiParam {float} [longitude] долгота локации
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 6,
        "name_ru": "test 6 CityRu",
        "name_en": "test 6 City En",
        "region_id": 3727,
        "country_id": "1",
        "created_at": null,
        "updated_at": "2020-04-20 14:04:20",
        "is_visible": false,
        "description_ru": "Description 6 Ru",
        "description_en": "Description 6 En",
        "image": null,
        "crop_image": null,
        "geonameid": 2093685,
        "latitude": "-4.3432",
        "longitude": "152.26867",
        "timezone": "Pacific/Port_Moresby",
        "alias": null,
        "population": 26273,
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "tags_ru": [
            "курорты",
            "города",
            "Папуа – Новая Гвинея",
            "Восточная Новая Британия"
            ],
        "tags_en": [
            "resorts",
            "cities"
            ],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": ""
    }
     * @throws ApiProblemException
     * @param Request $request
     * @param int $cityId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateCity(Request $request, int $cityId)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'crop_image' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'crop_image_en' => 'file|image|max:5128|nullable',
            'country_id' => 'integer|nullable',
            'name_ru'=> 'string|max:255',
            'name_en'=> 'string|max:255',
            'is_visible'=> 'boolean',
            'recomm_ru' => 'string|nullable',
            'recomm_en' => 'string|nullable',
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
            'latitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
            'longitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
        ], [
            'latitude.regex' => 'Формат широты неверный (корректный формат 12.123456789)',
            'longitude.regex' => 'Формат долготы неверный (корректный формат 12.123456789)',
        ]);
        if ($valid->fails()){ return response($valid->errors(), 400);
        }
        $city = $this->geoService->updateCity($request, $cityId);

        return response($city, 200);
    }

    /**
     * @api {get} /api/admin/city/{cityId} получение города
     * @apiVersion 0.1.0
     * @apiName GetCity
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 6,
        "name_ru": "test 6 CityRu",
        "name_en": "test 6 City En",
        "region_id": 3727,
        "country_id": 1,
        "created_at": null,
        "updated_at": "2020-04-20 14:04:20",
        "is_visible": false,
        "description_ru": "Description 6 Ru",
        "description_en": "Description 6 En",
        "image": null,
        "crop_image": null,
        "geonameid": 2093685,
        "latitude": "-4.3432",
        "longitude": "152.26867",
        "timezone": "Pacific/Port_Moresby",
        "alias": null,
        "population": 26273,
        "recomm_ru": null,
        "recomm_en": null,
        "image_en": null,
        "crop_image_en": null,
        "tags_ru": [
            "курорты",
            "города",
            "Папуа – Новая Гвинея",
            "Восточная Новая Британия"
            ],
        "tags_en": [
            "resorts",
            "cities"
            ],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "is_favorite": false,
        "recommendations": [],
        "seo": null,
        "region": {
                "id": 3727,
                "name_ru": "Восточная Новая Британия"
            },
        "country": {
                "id": 1,
                "name_ru": "Руанда"
            }
    }
     * @throws ApiProblemException
     * @param int $cityId
     * @return mixed
     */
    public function getCity(int $cityId)
    {
        $city = $this->geoService->getCity($cityId);

        return response()->json($city, 200);
    }

    /**
     * @api {post} /api/admin/city/ создание города
     * @apiVersion 0.1.0
     * @apiName CreateCity
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {integer} country_id id страны
     * @apiParam {integer} region_id id региона
     * @apiParam {string} name_ru название на русском
     * @apiParam {string} name_en название на русском
     * @apiParam {boolean} [is_visible] видимость
     * @apiParam {string} [description_ru] описание на русском
     * @apiParam {string} [description_en] описание на английском
     * @apiParam {file} [image] изображение
     * @apiParam {file} [crop_image] среднего размера изображение
     * @apiParam {file} [image_en] изображение
     * @apiParam {file} [crop_image_en] среднего размера изображение
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     * @apiParam {string} [profiles_block_title_ru] заголовок блоков профилей на русском
     * @apiParam {string} [profiles_block_title_en] заголовок блоков профилей на английском
     * @apiParam {float} [latitude] широта локации
     * @apiParam {float} [longitude] долгота локации
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    {
        "country_id": "88",
        "region_id": "7000",
        "name_ru": "test 3 CityRu",
        "name_en": "test 3 City En",
        "is_visible": false,
        "description_ru": "Description 2 Ru",
        "description_en": "Description 2 En",
        "tags_ru": [],
        "tags_en": [],
        "profiles_block_title_ru": "",
        "profiles_block_title_en": "",
        "latitude": "55.11111",
        "longitude": "66.11111",
        "updated_at": "2020-04-20 15:01:24",
        "created_at": "2020-04-20 15:01:24",
        "id": 190349
    }
     * @throws ApiProblemException
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function createCity(Request $request)
    {
        $valid = Validator($request->all(),[
            'country_id' => 'required|integer|exists:countries,id',
            'region_id' => 'required|integer|exists:regions,id',
            'name_ru'=> 'string|max:255',
            'name_en'=> 'string|max:255',
            'image' => 'file|image|max:5128|nullable',
            'crop_image' => 'file|image|max:5128|nullable',
            'image_en' => 'file|image|max:5128|nullable',
            'crop_image_en' => 'file|image|max:5128|nullable',
            'is_visible'=> 'boolean',
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
            'latitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
            'longitude' => 'regex:/^-?\d{1,2}\.?\d{0,9}$/|nullable',
        ], [
            'latitude.regex' => 'Формат широты неверный (корректный формат 12.123456789)',
            'longitude.regex' => 'Формат долготы неверный (корректный формат 12.123456789)',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        //проверим, принадлежит ли регион стране
        $regionId = $request->get('region_id');
        $countryId =$request->get('country_id');
        $region = Region::find($regionId);
        if ($region->country_id != $countryId){
            $response = [
                'status' => 400,
                'errors' => 'Регион не принадлежит стране',
            ];

            return response($response, $response['status']);
        }

        return $this->geoService->createCity($request);
    }

    /**
     * Удаление города
     *
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCity(int $cityId)
    {
        $deleted = $this->geoService->deleteCity($cityId);
        if ($deleted){

            return response()->json(['geo' =>[
                'Удалено'
            ]], 200);
        } else {
            return response()->json(['geo' =>[
                'Не найдено'
            ]], 404);
        }
    }

    /**
     * @api {delete} /api/admin/country/{countryId}/image удаление изображений страны
     * @apiVersion 0.1.0
     * @apiName DeleteImagesCountry
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} imageTypes Типы изображений ['image', 'crop_image', 'image_en', 'crop_image_en']
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "удалено"
    }
     *
     * @param Request $request
     * @param int $countryId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteCountryImage(Request $request, int $countryId)
    {
        $valid = Validator($request->all(), [
           'imageTypes' => ['required', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $imageTypes = $request->get('imageTypes');
        if (!is_array($imageTypes)) $imageTypes = json_decode($imageTypes, true);
        foreach ($imageTypes as $imageType){
            if (!in_array($imageType, ['image', 'crop_image', 'image_en', 'crop_image_en']))
                throw new ApiProblemException("Не поддерживаемый тип изображения $imageType", 422);
        }

        $this->geoService->deleteCountryImage($countryId, $imageTypes);

        return response()->json(['message' => 'удалено']);
    }

    /**
     * @api {delete} /api/admin/region/{regionId}/image удаление изображений региона
     * @apiVersion 0.1.0
     * @apiName DeleteImagesRegion
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} imageTypes Типы изображений ['image', 'crop_image', 'image_en', 'crop_image_en']
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "удалено"
    }
     *
     * @param Request $request
     * @param int $regionId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteRegionImage(Request $request, int $regionId)
    {
        $valid = Validator($request->all(), [
            'imageTypes' => ['required', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $imageTypes = $request->get('imageTypes');
        if (!is_array($imageTypes)) $imageTypes = json_decode($imageTypes, true);
        foreach ($imageTypes as $imageType){
            if (!in_array($imageType, ['image', 'crop_image', 'image_en', 'crop_image_en']))
                throw new ApiProblemException("Не поддерживаемый тип изображения $imageType", 422);
        }

        $this->geoService->deleteRegionImage($regionId, $imageTypes);

        return response()->json(['message' => 'удалено']);
    }

    /**
     * @api {delete} /api/admin/city/{cityId}/image удаление изображений городы
     * @apiVersion 0.1.0
     * @apiName DeleteImagesCity
     * @apiGroup GeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} imageTypes Типы изображений ['image', 'crop_image', 'image_en', 'crop_image_en']
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "удалено"
    }
     *
     * @param Request $request
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteCityImage(Request $request, int $cityId)
    {
        $valid = Validator($request->all(), [
            'imageTypes' => ['required', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $imageTypes = $request->get('imageTypes');
        if (!is_array($imageTypes)) $imageTypes = json_decode($imageTypes, true);
        foreach ($imageTypes as $imageType){
            if (!in_array($imageType, ['image', 'crop_image', 'image_en', 'crop_image_en']))
                throw new ApiProblemException("Не поддерживаемый тип изображения $imageType", 422);
        }
        $this->geoService->deleteCityImage($cityId, $imageTypes);

        return response()->json(['message' => 'удалено']);
    }
}
