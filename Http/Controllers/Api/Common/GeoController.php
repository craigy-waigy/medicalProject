<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\GeoService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class GeoController extends Controller
{
    /**
     * @var GeoService
     */
    protected $geoService;

    protected $searchService;

    /**
     * GeoController constructor.
     */
    public function __construct()
    {
        $this->geoService = new GeoService;
        $this->searchService = new SearchService;
    }

    /**
     * @api {get} /api/{locale}/geo/search/country поиск - получение стран (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchCountry
     * @apiGroup Geo
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {boolean} [is_visible] Видимость скрытых регионов
     *
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 192,
        "items": [
            {
                "id": 44,
                "name": "Албания",
                "latitude": "41",
                "longitude": "20",
                "crop_image": null,
                "alias": "albaniya-44",
                "telephone_code": "355",
                "country_code": "AL",
                "objects_count": 0
            },
        ]
    }
     /*
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function searchCountry(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'is_visible' => 'boolean|nullable',
            'has_object' => 'boolean|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $isVisible = $request->get('is_visible'); //Скрывать ли неактивные регионы
        $params['has_object'] = $request->get('has_object');
        $countries = $this->geoService->getCountries($page, $rowsPerPage, $searchKey, $sorting, $locale, $isVisible, $params);

        return response()->json($countries, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/search/city поиск - получение городов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchCity
     * @apiGroup Geo
     *
     * @apiParam {integer} [country_id] ID - страны
     * @apiParam {integer} [region_id] ID - региона
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {boolean} [is_visible] Видимость скрытых регионов
     *
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 6,
        "items": [
            {
                "id": 142878,
                "region_id": 6996,
                "country_id": 88,
                "name": "Елизаветинская",
                "latitude": "45.04616",
                "longitude": "38.79491",
                "crop_image": null,
                "alias": null,
                "objects_count": 0,
                "region": {
                "id": 6996,
                "name": "Краснодарский край"
            },
            "country": {
                    "id": 88,
                    "name": "Россия",
                    "telephone_code": "7",
                    "country_code": "RU"
                }
            }
        ]
    }
     /*
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function searchCity(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'is_visible' => 'boolean|nullable',
            'has_object' => 'boolean|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $countryId = $request->get('country_id');
        $regionId = $request->get('region_id');
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $isVisible = $request->get('is_visible'); //Скрывать ли неактивные регионы
        $params['has_object'] = $request->get('has_object');
        $cities = $this->geoService->getCities($countryId, $page, $rowsPerPage, $searchKey,  $regionId, $sorting, $locale, $isVisible, $params);

        return response()->json($cities, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/search/city/top получение крупных городов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName TopCity
     * @apiGroup Geo
     *
     * @apiParam {integer} country_id ID - страны
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 143677,
            "region_id": 7003,
            "country_id": 88,
            "name": "Москва",
            "latitude": "55.75222",
            "longitude": "37.61556",
            "objects_count": 0,
            "region": {
            "id": 7003,
            "name": "Москва"
        },
        "country": {
                "id": 88,
                "telephone_code": "7"
            }
        },
    ]
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getTopCity(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'required|integer',
            'rowsPerPage' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $countryId = $request->get('country_id');
        $cities = $this->geoService->getTopCity($countryId, $rowsPerPage, $locale);

        return response()->json($cities, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/country/{alias} получение страны (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetCountry
     * @apiGroup Geo
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 88,
        "name": "Россия",
        "description": "",
        "recomm": "",
        "latitude": "60",
        "longitude": "100",
        "image": "",
        "telephone_code": "7",
        "country_code": "RU",
        "profiles_block_title": "",
        "regions_block_title": "",
        "is_favorite": null,
        "recommendations": [],
        "seo": {
                "id": 22994,
                "country_id": 88,
                "h1": "Россия",
                "title": "Россия",
                "url": "russia",
                "meta_description": "Россия",
                "meta_keywords": "россия"
            }
    }



     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getCountry(string $locale, string $alias)
    {
        $country = $this->geoService->getCountry(null, $locale, $alias);

        return response()->json($country, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/search/region поиск - получение регионов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchRegion
     * @apiGroup Geo
     *
     * @apiParam {integer} [country_id] ID - страны
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {boolean} [is_visible] Видимость скрытых регионов
     *
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 3639,
        "items": [
        {
            "id": 6996,
            "country_id": 88,
            "name": "Краснодарский край",
            "latitude": "44.98811",
            "longitude": "38.97675",
            "crop_image": "/storage/regions_crop/drYG3ys0ZqTCZDyNKEWIpMgXk24LzRAInWdafI8M.jpeg",
            "alias": "krasnodarskiy_kray",
            "objects_count": 9,
            "country": {
                    "id": 88,
                    "name": "Россия",
                    "telephone_code": "7",
                    "country_code": "RU"
                }
        },
        ]
    }
     /*
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function searchRegion(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'is_visible' => 'boolean|nullable',
            'has_object' => 'boolean|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $countryId = $request->get('country_id');
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $isVisible = $request->get('is_visible'); //Скрывать ли неактивные регионы
        $params['has_object'] = $request->get('has_object');
        $regions = $this->geoService->getRegions($countryId, $page, $rowsPerPage, $searchKey,  $sorting, $locale, $isVisible, $params);

        return response()->json($regions, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/region/{alias} получение региона (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetRegion
     * @apiGroup Geo
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 6996,
        "country_id": 88,
        "name": "Краснодарский край",
        "description": "",
        "latitude": "44.98811",
        "longitude": "38.97675",
        "image": "/storage/regions/drYG3ys0ZqTCZDyNKEWIpMgXk24LzRAInWdafI8M.jpeg",
        "profiles_block_title": "",
        "cities_block_title": "",
        "is_favorite": null,
        "recommendations": [
        {
        "id": 1,
        "type": "region",
        "location_id": 6996,
        "recommendation": ""
        }
        ],
        "seo": {
            "id": 23028,
            "region_id": 6996,
            "h1": "Краснодарский край",
            "title": "Краснодарский край",
            "url": "krasnodarskiy_kray",
            "meta_description": "Краснодарский край",
            "meta_keywords": "краснодарский край"
        },
        "public_country": {
            "id": 88,
            "name": "Россия",
            "alias": "russia",
            "telephone_code": "7",
            "country_code": "RU"
        }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getRegion(string $locale, string $alias)
    {
        $region = $this->geoService->getRegion(null, $locale, $alias);

        return response()->json($region, 200);
    }

    /**
     * @api {get} /api/{locale}/geo/city/{alias} получение города (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetCity
     * @apiGroup Geo
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 142922,
        "country_id": 88,
        "region_id": 6996,
        "name": "Сочи",
        "description": "",
        "latitude": "43.59917",
        "longitude": "39.72569",
        "image": "/storage/regions/e8TxjomZD6MBvN4ATOTUkua35srmXW7lTDOafUR3.jpeg",
        "profiles_block_title": "",
        "is_favorite": null,
        "recommendations": [],
        "seo": {
                "id": 23044,
                "city_id": 142922,
                "h1": "Сочи",
                "title": "Сочи",
                "url": "sochi",
                "meta_description": "Сочи",
                "meta_keywords": "сочи"
            },
        "public_region": {
                "id": 6996,
                "name": "Краснодарский край",
                "alias": "krasnodarskiy_kray"
            },
        "public_country": {
                "id": 88,
                "name": "Россия",
                "alias": "russia",
                "telephone_code": "7",
                "country_code": "RU"
            }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getCity(string $locale, string $alias)
    {
        $city = $this->geoService->getCity(null, $locale, $alias);

        return response()->json($city, 200);
    }

    /**
     * @api {post} /api/{locale}/favorite/geography Добавление в избранное географии (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName AddGeography
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [city_id] ID города
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Добавлено в избранное"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function addFavorite(Request $request)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $countryId = $request->get('country_id');
        $regionId = $request->get('region_id');
        $cityId = $request->get('city_id');

        $this->searchService->addFavoriteGeo($userId, $countryId, $regionId, $cityId);

        return response()->json(['message' => 'Добавлено в избранное'], 200);
    }

    /**
     * @api {delete} /api/{locale}/favorite/geography Удаление из избранного географии (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName DeleteGeography
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [city_id] ID города
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Удалено из избранного"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteFavorite(Request $request)
    {
        $valid = Validator($request->all(), [
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $countryId = $request->get('country_id');
        $regionId = $request->get('region_id');
        $cityId = $request->get('city_id');

        $this->searchService->deleteFavoriteGeo($userId, $countryId, $regionId, $cityId);

        return response()->json(['message' => 'Удалено из избранного'], 200);
    }

    /**
     * @api {get} /api/{locale}/favorite/geography Получение списка избранной географии (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetGeography
     * @apiGroup Favorites
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] Количество на страницу
     * @apiParam {string} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
     *
    {
    "page": 1,
    "rowsPerPage": 10,
    "total": 3,
    "items": [
            {
                "id": 7,
                "region_id": 6977,
                "country_id": 88,
                "name": "Барнаул",
                "alias": "qwerty",
                "description": "ru",
                "image": "/storage/regions_crop/z9R0c65rokOlp4UxonaKLcCq3lL0RxYqaAWf7IQC.jpeg",
                "type": "city",
                "region": {
                    "id": 6977,
                    "name": "Алтайский Край"
                },
                "country": {
                    "id": 88,
                    "name": "Российская Федерация"
                },
                "seo": {
                "city_id": 7,
                "order": 3,
                "title": "title-ru"
                }
            },
            {
                "id": 3830,
                "country_id": 41,
                "name": "Oslomej",
                "alias": "wwew",
                "description": null,
                "image": "/storage/regions_crop/z9R0c65rokOlp4UxonaKLcCq3lL0RxYqaAWf7IQC.jpeg",
                "type": "region",
                "country": {
                "id": 41,
                    "name": "Республика Македония"
                },
                "seo": null
            },
            {
                "id": 1,
                "name": "Руанда",
                "alias": "country-alias",
                "description": "ru",
                "image": "/storage/regions_crop/z9R0c65rokOlp4UxonaKLcCq3lL0RxYqaAWf7IQC.jpeg",
                "type": "country",
                "seo": {
                "country_id": 1,
                    "order": 1,
                    "title": null
                }
            }
        ]
    }

     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function getFavorites(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $userId = Auth::user()->id;

        $result = $this->searchService->getFavoritesGeo($page, $rowsPerPage, $searchKey, $locale, $userId);

        return response()->json($result, 200);
    }
}
