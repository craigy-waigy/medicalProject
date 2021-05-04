<?php

namespace App\Http\Controllers\Api\Pages;

use App\Exceptions\UnsupportLocaleException;
use App\Services\SearchService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rules\IsArray;
use Illuminate\Support\Facades\Auth;

class SearchController extends Controller
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * SearchController constructor.
     */
    public function __construct()
    {
        $this->searchService = new SearchService();
    }

    /**
     * @api {get} /api/{locale}/search/main Основной поиск (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Main
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objects": {
            "page": 1,
            "rowsPerPage": 5,
            "total": 1,
            "items": [
                {
                "id": 4,
                "title": "Test object 2"
                }
            ]
        },
        "geo": {
            "city": {
                "page": 1,
                "rowsPerPage": 5,
                "total": 0,
                "items": []
            },
            "region": {
                "page": 1,
                "rowsPerPage": 5,
                "total": 3640,
                "items": [
                    {
                    "id": 4541,
                    "name": "Hiiumaa"
                    },
                    {
                    "id": 4629,
                    "name": "Jelgava"
                    },
                    {
                    "id": 4985,
                    "name": "Ilocos"
                    },
                    {
                    "id": 5060,
                    "name": "Nay Pyi Taw"
                    },
                    {
                    "id": 5839,
                    "name": "Hovd"
                    }
                ]
            },
            "country": {
                "page": 1,
                "rowsPerPage": 5,
                "total": 192,
                "items": [
                    {
                    "id": 1,
                    "name": "Republic of Rwanda"
                    },
                    {
                    "id": 18,
                    "name": "Lebanon"
                    },
                    {
                    "id": 84,
                    "name": "Japan"
                    },
                    {
                    "id": 3,
                    "name": "Republic of Yemen"
                    },
                    {
                    "id": 4,
                    "name": "Republic of Iraq"
                    }
                ]
            }
        },
        "medical_profile": {
            "page": 1,
            "rowsPerPage": 5,
            "total": 17,
            "items": [
                {
                "id": 6,
                "name": "Diseases of the endocrine system and metabolism"
                },
                {
                "id": 5,
                "name": "nameEn"
                },
                {
                "id": 12,
                "name": "Diseases of the musculoskeletal system"
                },
                {
                "id": 22,
                "name": "test profile"
                },
                {
                "id": 4,
                "name": "nameEn"
                }
            ]
        },
        "disease": {
            "page": 1,
            "rowsPerPage": 5,
            "total": 11176,
            "items": [
                {
                "id": 9030,
                "name": ""
                },
                {
                "id": 9099,
                "name": ""
                },
                {
                "id": 9642,
                "name": ""
                },
                {
                "id": 9643,
                "name": ""
                },
                {
                "id": 9644,
                "name": ""
                }
            ]
        },
         "therapy": {
             "page": 1,
             "rowsPerPage": 5,
             "total": 302,
             "items": [
                 {
                 "id": 12,
                 "name": ""
                 },
                 {
                 "id": 44,
                 "name": ""
                 },
                 {
                 "id": 242,
                 "name": ""
                 },
                 {
                 "id": 50,
                 "name": ""
                 },
                 {
                 "id": 51,
                 "name": ""
                 }
             ]
        }
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function mainSearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|max:50|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;

        $result = $this->searchService->mainSearch($page, $rowsPerPage, $searchKey, $locale);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/all всепоиск (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName All
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 4,
        "total": 270,
        "items": [
            {
                "id": 56,
                "name": "Другие протозойные кишечные болезни",
                "description": "Инфекции, вызываемые паразитическими простейшими. Простейшие вызывают у человека, домашних и промысловых животных тяжёлые болезни. Известно около 50 видов простейших, вызывающих болезни у человека. Поражение населения протозойными инфекциями очень высокое. Простейшие паразитируют в различных органах и тканях: в крови, кишечнике, ЦНС, печени, лёгких и т.д. Возбудители передаются человеку алиментарным путём, через членистоногих переносчиков, половым путём.",
                "alias": "drugie-protozoynye-kishechnye-bolezni",
                "viewing_count": "0",
                "count_objects": 19,
                "score": 5.112724,
                "type": "disease",
                "seo": null
            },
            {
                "id": 299,
                "name": "Введение ректальных грязевых тампонов при болезнях мужских половых органов",
                "description": "Перед введением грязевого тампона больной освобождает мочевой пузырь и кишечник. Тампон вводят больному, находящемуся в коленно-локтевом положении или на левом боку. Для введения грязевого тампона используют металлический шприц с ректальным наконечником либо одноразовую тубу с лечебной грязью. Процедуру введения осуществляют медленно для предупреждения позыва на дефекацию. Наконечник, смазанный вазелином, вводят в прямую кишку на глубину 6-7 см, и шприц (тубу) медленно опорожняют. Пациент ложится на живот, а через 10-15 мин поворачивается на левый бок. Расход грязи на ректальный тампон составляет 250-350 г. Температура грязи 38-45°С. Тампон оставляют в прямой кишке на 30-60 мин, иногда на 2 часа, после чего он удаляется во время акта дефекации. Отдых не менее 50-60 мин. Процедуры проводят ежедневно, через день или четыре раза в неделю.\\nНа курс лечения 12-18 процедур.",
                "alias": "vvedenie-rektalnykh-gryazevykh-tamponov-pri-boleznyakh-muzhskikh-polovykh-organov",
                "viewing_count": "0",
                "count_objects": 0,
                "score": 3.494829,
                "type": "therapy",
                "seo": {
                    "therapy_id": 299,
                    "order": 468,
                    "title": null
                }
            },
            {
                "id": 88,
                "name": "<strong>Российская</strong> Федерация",
                "description": "Россия обладает всеми возможностями для эффективного курортного лечения. На ее просторах всегда можно подобрать курорт любой климатической зоны, располагающий всеми необходимыми лечебными факторами. В частности, в России можно принимать ванны из минеральных вод любого типа. В самом известном и небольшом по площади курортном&nbsp; регионе России - Кавказские Минеральные Воды, более 300 минеральных источников! Минеральные воды есть в самом западном регионе страны - на приморских курортах Калининградской области и на Дальнем Востоке. Азотно-кремнистые термы Камчатки, по мнению японских геронтологов, являются мощным геропротектором и могут составить конкуренцию аналогичным курортам страны восходящего солнца. Термальные воды есть даже за полярным кругом - на курорте Талая Магаданской области. Иловыми грязями богаты лечебные курорты Пятигорска. Сапропелевые грязи, в которых содержится много органических веществ и солей, применяются в санаториях Подмосковья и Средней полосы России.\r\nИ, безусловно, Россия &ndash; это климатические курорты, которые благотворно влияют на нервную систему, улучшают регуляцию жизненных процессов, укрепляют иммунитет. Так, например, курорты Подмосковья и Средней полосы России идеально подойдут для туристов, которые с трудом переносят акклиматизацию при переезде в зону более жаркого и влажного климата.\r\nЕстественные лечебные факторы России сочетаются с новейшими медицинскими технологиями и программами. Популярности отечественных курортов также способствуют более демократичные цены, отсутствие языкового барьера и визовых оформлений, живописные пейзажи и великолепные архитектурные памятники.Климат России весьма разнообразен из-за огромной территории страны. Она расположена в арктическом, субарктическом, умеренном и, частично, в субтропическом климатических поясах, хотя на большей части территории климат континентальный или умеренно континентальный с длинной холодной зимой и коротким нежарким летом. Разнообразие климата зависит также от особенностей рельефа и близости или удаленности океана.Самым холодным регионом является Сибирь, где в районе города Верхоянск находится &laquo;полюс холода&raquo; &ndash; средняя температура января здесь около -51&deg;С, а в феврале столбик термометра опускается иногда до -68&deg;С. В Европейской части климат более умеренный, а на Черноморском побережье &ndash; мягкий, средиземноморского типа. Приморские курорты Черноморского побережья России расположены в зоне сухих и влажных (Сочи) субтропиков.",
                "image": "/storage/regions_crop/1520336625.jpg",
                "alias": "rossiyskaya-federatsiya-88",
                "viewing_count": "2",
                "count_objects": "1",
                "score": 0.81427324,
                "type": "country"
            },
            {
                "id": 55,
                "title": "<strong>Санаторий</strong> Эдем",
                "stars": 4,
                "country_id": null,
                "region_id": null,
                "city_id": null,
                "lat": "51.982637",
                "lon": "84.961504",
                "alias": "jedemsanatorium",
                "heating_rating": "0",
                "min_price": null,
                "in_action": false,
                "doctor_online": null,
                "score": 0.1228559,
                "description": "Лечение в <strong>санатории</strong> производится при помощи слабоминерализованных радоновых ванн, богатых на биологически",
                "type": "object",
                "street_view_link": "ссылка на вид с улицы",
                "region": {
                    "id": 6996,
                    "name": "Краснодарский край"
                },
                "city": {
                    "id": 142922,
                    "name": "Сочи"
                },
                "country": {
                    "id": 88,
                    "name": "Россия"
                },
                "moderated_images": [
                    {
                        "id": 2670,
                        "object_id": 55,
                        "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                        "sorting_rule": 0,
                        "is_main": false
                    },
                    {
                        "id": 2669,
                        "object_id": 55,
                        "image": "/storage/object_gallery/thumbs-450x450_aVWNX8DiwEc03ivT.jpeg.jpeg",
                        "sorting_rule": 1,
                        "is_main": false
                    }
                ],
                "medical_profiles_public": [
                    {
                        "id": 5,
                        "name": "Болезни нервной системы",
                        "alias": "bolezni-nervnoy-sistemy"
                    }
                ],
                "country": null,
                "region": null,
                "city": null
            },
            /*
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function searchAll(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|max:50|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        $result = $this->searchService->searchAll($page, $rowsPerPage, $searchKey, $locale);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/resort поиск курортов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Resort
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 4,
        "total": 87,
        "items": [
            {
                "id": 6964,
                "country_id": 88,
                "name": "Костромская Область",
                "description": null,
                "alias": null,
                "viewing_count": "0",
                "count_objects": "0",
                "score": 6.021441,
                "type": "region",
                "country": {
                    "id": 88,
                    "name": "Российская Федерация"
                }
            },
            {
                "id": 6965,
                "country_id": 88,
                "name": "Калужская Область",
                "description": null,
                "alias": null,
                "viewing_count": "0",
                "count_objects": "0",
                "score": 6.021441,
                "type": "region",
                "country": {
                    "id": 88,
                    "name": "Российская Федерация"
                }
            },
            {
                "id": 6966,
                "country_id": 88,
                "name": "Липецкая Область",
                "description": null,
                "alias": null,
                "viewing_count": "0",
                "count_objects": "0",
                "score": 6.021441,
                "type": "region",
                "country": {
                    "id": 88,
                    "name": "Российская Федерация"
                }
            },
            {
                "id": 4751,
                "country_id": 88,
                "name": "Республика Крым",
                "description": null,
                "alias": null,
                "viewing_count": "0",
                "count_objects": "0",
                "score": 6.021441,
                "type": "region",
                "country": {
                    "id": 88,
                    "name": "Российская Федерация"
                }
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function searchResort(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|max:50|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        $result = $this->searchService->searchResort($page, $rowsPerPage, $searchKey, $locale);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/main/prompt Подсказки для основного поиска (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName MainSearchPrompt
     * @apiGroup Searches
     *
     *
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     [
        "prompt1",
        "prompt2",
        "prompt3",
        "prompt4",
        "prompt5"
     ]
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function mainSearchPrompt(Request $request)
    {
        $valid = Validator($request->all(), [
            'searchKey' => 'string|nullable',
            'rowsPerPage' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        $searchKey = $request->get('searchKey');
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;

        $searchPrompt = $this->searchService->mainSearchPrompt($searchKey, $rowsPerPage);

        return response()->json($searchPrompt, 200);
    }

    /**
     * @api {get} /api/{locale}/search/city Поиск городов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName City
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {array} [ids] Массив идентификаторов городов
     * @apiParam {boolean} [has_object] Возвращать в ответе города где есть санатории
     * @apiParam {json} [sorting] сотрировка (например {"count_objects":"desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 0,
        "items": [

        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function citySearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'ids' => [ new IsArray ],
            'has_object' => 'boolean',
            'country_id' => 'nullable|integer',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $hasObjects = $request->get('has_object');
        $ids = $request->get('ids');
        if (!is_array($ids)) $ids = json_decode($ids, true);
        $params['country_id'] = $request->get('country_id');
        $params['sorting'] = $request->get('sorting');
        if (!is_array($params['sorting'])) $params['sorting'] = json_decode($params['sorting'], true);

        $result = $this->searchService->citySearch($page, $rowsPerPage, $searchKey, $locale, true, $ids,
            null, $hasObjects, $params);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/region Поиск регионов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Region
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [ids] Массив идентификаторов регионов
     * @apiParam {boolean} [has_object] Возвращать в ответе регионы где есть санатории
     * @apiParam {json} [sorting] сотрировка (например {"count_objects":"desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 3640,
        "items": [
            {
            "id": 4541,
            "name": "Хийумаа",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 4629,
            "name": "Елгава",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 4985,
            "name": "Илокос",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 5060,
            "name": "Nay Pyi Taw",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 5839,
            "name": "Hovd",
            "description": null,
            "count_objects": 0
            }
        ]
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function regionSearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'ids' => [ new IsArray ],
            'has_object' => 'boolean',
            'country_id' => 'nullable|integer',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $ids = $request->get('ids');
        if (!is_array($ids)) $ids = json_decode($ids, true);
        $hasObjects = $request->get('has_object');
        $params['country_id'] = $request->get('country_id');
        $params['sorting'] = $request->get('sorting');
        if (!is_array($params['sorting'])) $params['sorting'] = json_decode($params['sorting'], true);

        $result = $this->searchService->regionSearch($page, $rowsPerPage, $searchKey, $locale, true, $ids,
            null, $hasObjects, $params);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/country Поиск стран (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Country
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [ids] Массив идентификаторов стран
     * @apiParam {boolean} [has_object] Возвращать в ответе страны где есть санатории
     * @apiParam {json} [sorting] сотрировка (например {"count_objects":"desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 192,
        "items": [
            {
            "id": 1,
            "name": "Руанда",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 18,
            "name": "Ливан",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 84,
            "name": "Япония",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 3,
            "name": "Йемен",
            "description": null,
            "count_objects": 0
            },
            {
            "id": 4,
            "name": "Ирак",
            "description": null,
            "count_objects": 0
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function countrySearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'ids' => [ new IsArray ],
            'has_object' => 'boolean',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $ids = $request->get('ids');
        if (!is_array($ids)) $ids = json_decode($ids, true);
        $hasObjects = $request->get('has_object');
        $params['sorting'] = $request->get('sorting');
        if (!is_array($params['sorting'])) $params['sorting'] = json_decode($params['sorting'], true);

        $result = $this->searchService->countrySearch($page, $rowsPerPage, $searchKey, $locale, true, $ids, null, $hasObjects, $params);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/medical-profile Поиск мед. профилей (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName MedicalProfile
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {boolean} [basic] Если требуется получить список базовых профилей
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 1,
        "total": 1,
        "items": [
            {
                "id": 7,
                "name": "Болезни органов зрения",
                "description": "<p>Заболевания глаз &ndash; это функциональные и органические поражения зрительного анализатора, из-за которых человек начинает хуже видеть, а также патологии придаточного аппарата глаза.</p><p>Любое заболевание органов слуха и зрения негативно отражается на состоянии человека, его образе жизни и доставляет довольно ощутимый дискомфорт.</p><p><strong>Классификация заболеваний органов зрения</strong></p><p>Заболевания органов зрения обширны, поэтому для удобства их делят на несколько больших разделов.</p><p>Согласно общепринятой классификации все патологии органов зрения (в том числе заболевание органов зрения у детей) подразделяются на следующие группы:</p><ul><li>патологии зрительного нерва;</li><li>заболевания слезных путей, век, глазницы;</li><li>глаукома;</li><li>болезни конъюнктивы;</li><li>патологии глазных мышц;</li><li>болезни радужной оболочки, склеры, роговицы;</li><li>слепота;</li><li>болезни хрусталика;</li><li>патологии стекловидного тела и глазного яблока;</li><li>болезни сосудистой оболочки и сетчатки.</li></ul>",
                "alias": "bolezni-organov-zreniya",
                "count_objects": 1,
                "seo": {
                    "medical_profile_id": 7,
                    "order": 195,
                    "title": null
                },
                "images": [
                    {
                        "id": 13,
                        "medical_profile_id": 7,
                        "description": "weqwe",
                        "image": "/storage/medical_profile/dFRDYw74HBrfVlioT4XKBUFzU2vbH4NexH5X1JAw.jpeg"
                    },
                    {
                        "id": 14,
                        "medical_profile_id": 7,
                        "description": "/storage/medical_profile/dFRDYw74HBrfVlioT4XKBUFzU2vbH4NexH5X1JAw.jpeg",
                        "image": "image"
                    }
                ]
            }
        ]
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function medicalProfileSearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(),[
           'page' => 'integer|nullable',
           'rowsPerPage' => 'integer|nullable',
           'searchKey' => 'string|nullable',
           'basic' => 'boolean|nullable'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $params['basic'] = $request->get('basic');
        $params['city_id'] = $request->get('city_id');
        $params['region_id'] = $request->get('region_id');
        $params['country_id'] = $request->get('country_id');

        $result = $this->searchService->medicalProfileSearch($page, $rowsPerPage, $searchKey, $locale, true, null, $params);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/disease Поиск заболеваний (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Disease
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [objectIds] Массив ID объектов [34,32,30]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 11176,
        "items": [
            {
                "id": 9030,
                "name": "Открытая рана бедра",
                "description": "",
                "count_objects": 0
            }
        ]
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function diseaseSearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'objectIds' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $objectIds = $request->get('objectIds');
        if (!is_array($objectIds))
            $objectIds = json_decode($objectIds, true);

        $result = $this->searchService
            ->diseaseSearch($page, $rowsPerPage, $searchKey, $locale, true, null, $objectIds);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/therapy Поиск мет. лечения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Therapy
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [ids] Идентификаторы методов лечения
     * @apiParam {array} [objectIds] Массив ID объектов [34,32,30]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 301,
        "items": [
            {
                "id": 78,
                "name": "",
                "description": "no description available",
                "alias": "mioelektrostimulyatsiya",
                "count_objects": 0,
                "seo": {
                    "therapy_id": 78,
                    "order": 207,
                    "title": null
                }
            },
            {
                "id": 128,
                "name": "Explanation, description, interpretation of ECG data",
                "description": "no description available",
                "alias": "rasshifrovka-opisanie-interpretatsiya-elektrokardiograficheskikh-dannykh",
                "count_objects": 0,
                "seo": {
                    "therapy_id": 128,
                    "order": 160,
                    "title": null
                }
            },
            {
                "id": 175,
                "name": "Reception (examination, consultation) Cardiologist Primary",
                "description": "no description available",
                "alias": "priem-osmotr-konsultatsiya-vracha-kardiologa-pervichnyy",
                "count_objects": 0,
                "seo": {
                    "therapy_id": 175,
                    "order": 140,
                    "title": null
                }
            },
            {
                "id": 72,
                "name": "sinusoidal modulated current therapy",
                "description": "no description available",
                "alias": "vozdeystvie-sinusoidalnymi-modulirovannymi-tokami",
                "count_objects": 0,
                "seo": {
                    "therapy_id": 72,
                    "order": 218,
                    "title": null
                }
            },
            {
                "id": 257,
                "name": "Reception (examination, consultation) doctor - orthopedist primary",
                "description": "no description available",
                "alias": "priem-osmotr-konsultatsiya-vracha-ortopeda-pervichnyy",
                "count_objects": 0,
                "seo": {
                    "therapy_id": 257,
                    "order": 49,
                    "title": null
                }
            }
        ]
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function therapySearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'ids' => [ new IsArray ],
            'objectIds' => [ new IsArray ],

        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $ids = $request->get('ids');
        if (!is_array($ids)) $ids = json_decode($ids, true);
        $objectIds = $request->get('objectIds');
        if (!is_array($objectIds))
            $objectIds = json_decode($objectIds, true);

        $result = $this->searchService
            ->therapySearch($page, $rowsPerPage, $searchKey, $ids , $locale,true, null, $objectIds);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/object Расширенный поиск объектов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ObjectExtended
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [medical_profiles] массив id мед. профилей -[8,10,12,21]
     * @apiParam {array} [diseases] массив id заболеваний -[8,10,12,21]
     * @apiParam {array} [services] массив id услуг - [8,10,12,21]
     * @apiParam {array} [therapies] массив id мет. лечения - [8,10,12,21]
     * @apiParam {integer} [object_id] id - объекта-санатория для поиска рядом с санаторием
     * @apiParam {integer} [city_id] id - города
     * @apiParam {integer} [region_id] id - региона
     * @apiParam {integer} [country_id] id - страны
     * @apiParam {numeric} [lat] широта
     * @apiParam {numeric} [lon] долгота
     * @apiParam {boolean} [in_action] по акции
     * @apiParam {array} [stars] Количество звезд (Если передано 1 значение - фильтруется по критерию больше или равно количеству звезд,
     * если больше одного значения - то по критерию санатории имеющие указанное количество звезд)
     * @apiParam {json}  [sorting] Массив сортировки {"popular": "asc"}, {"expensive": "asc"}
     * @apiParam {boolean} [on_main_page] Санатории на главной странице (true если запрос с главной страницы)
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 5,
        "total": 5,
        "items": [
            {
                "id": 4,
                "title": "Тестовый объект 2",
                "stars": 5,
                "moderated_images": [],
                "medical_profiles_public": [],
                "country": null,
                "region": null,
                "city": null
            },
            {
                "id": 5,
                "title": "название объекта-7",
                "stars": 1,
                "moderated_images": [],
                "medical_profiles_public": [
                    {
                        "id": 5,
                        "name": "nameRu",
                        "pivot": {
                            "object_id": 5,
                            "medical_profile_id": 5
                        }
                    }
                ],
                "country": {
                    "id": 88,
                    "name": "Russian Federation"
                },
                "region": {
                    "id": 7008,
                    "name": "Orlovskaya Oblast'"
                },
                "city": null
            },
            {
                "id": 6,
                "title": "название объекта-7",
                "stars": 2,
                "moderated_images": [],
                "medical_profiles_public": [
                    {
                        "id": 12,
                        "name": "Болезни костно-мышечной системы",
                        "pivot": {
                            "object_id": 6,
                            "medical_profile_id": 12
                        }
                    }
                ],
                "country": {
                    "id": 88,
                    "name": "Russian Federation"
                },
                "region": {
                    "id": 7008,
                    "name": "Orlovskaya Oblast'"
                },
                "city": null
            },
            {
            "id": 7,
            "title": "название объекта-7",
            "stars": 3,
            "moderated_images": [],
            "medical_profiles_public": [
            {
            "id": 5,
            "name": "nameRu",
            "pivot": {
            "object_id": 7,
            "medical_profile_id": 5
            }
            }
            ],
            "country": null,
            "region": null,
            "city": null
            },
            {
            "id": 8,
            "title": "название объекта-7",
            "stars": 4,
            "moderated_images": [],
            "medical_profiles_public": [],
            "country": {
                "id": 88,
                "name": "Russian Federation"
            },
            "region": {
                "id": 7008,
                "name": "Orlovskaya Oblast'"
            },
            "city": null
            }
        ]
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function objectSearch(Request $request, string $locale)
    {
        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();

        $valid = Validator($request->all(), [
            'services' => [ new isArray ],
            'medical_profiles' => [ new isArray ],
            'therapies' => [ new isArray ],
            'diseases' => [ new isArray ],
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'object_id' => 'integer|nullable',
            'stars' => [ new IsArray ],
            'sorting' => [ new IsArray ],
            'lat' => 'numeric|nullable',
            'lon' => 'numeric|nullable',
            'in_action' => 'boolean|nullable',
            'on_main_page' => 'boolean|nullable',
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $params = $request->only('services', 'medical_profiles', 'stars', 'therapies',
            'city_id', 'region_id', 'country_id', 'popular', 'in_action', 'expensive', 'diseases', 'on_main_page', 'object_id');
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $result = $this->searchService->extendedObjectSearch($page, $rowsPerPage, $searchKey, $sorting, $locale, $params, $lat, $lon);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/search/geography Поиск географии (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Geography
     * @apiGroup Searches
     *
     *
     * @apiParam {integer} [rowsPerPage] количество результатов каждого типа списка (количество городов, регионов, стран)
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {array} [objectIds] Массив ID объектов [34,32,30]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 7,
            "region_id": 6977,
            "country_id": 88,
            "name": "Барнаул",
            "alias": "qwerty",
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
            "id": 4629,
            "country_id": 33,
            "name": "Елгава",
            "alias": "region-alias",
            "type": "region",
            "country": {
                "id": 33,
                "name": "Латвия"
            },
            "seo": {
                "region_id": 4629,
                "order": 2,
                "title": null
            }
        },
        {
            "id": 1,
            "name": "Руанда",
            "alias": "country-alias",
            "type": "country",
            "seo": {
                "country_id": 1,
                "order": 1,
                "title": null
            }
        }
    ]
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     */
    public function geographySearch(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [

            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'objectIds' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json(['errors' => $valid->errors()], 400);

        if (!in_array($locale, ['ru', 'en'])) throw new UnsupportLocaleException();
        $rowsPerPage = $request->get('rowsPerPage') ?? 5;
        $searchKey = $request->get('searchKey') ?? null;
        $objectIds = $request->get('objectIds');
        if (!is_array($objectIds))
            $objectIds = json_decode($objectIds, true);

        $result = $this->searchService->geographySearch( $rowsPerPage, $searchKey, $locale,
            null, null, null, $objectIds);

        return response()->json($result, 200);
    }

    /**
     * @api {get} /api/{locale}/favorite/object Получение списка избранных объектов (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetObjects
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
                "id": 44,
                "title": "Санаторий Сосновый бор",
                "stars": 3,
                "country_id": 18,
                "region_id": 4388,
                "city_id": 118006,
                "lat": "58.247175",
                "lon": "49.53481",
                "alias": "sosnoviy-bor",
                "moderated_images": [
                    {
                        "id": 2291,
                        "object_id": 44,
                        "image": "/storage/object_gallery/T0lUDpd0dxpXD8oN.jpeg",
                        "sorting_rule": 0,
                        "is_main": false
                    },
                    {
                        "id": 2290,
                        "object_id": 44,
                        "image": "/storage/object_gallery/mClTxljELxUiMVDA.jpeg",
                        "sorting_rule": 1,
                        "is_main": false
                    }
                ],
                "medical_profiles_public": [
                    {
                        "id": 5,
                        "name": "Болезни нервной системы",
                        "alias": "bolezni-nervnoy-sistemy"
                    },
                    {
                        "id": 10,
                        "name": "Болезни желудочно-кишечного тракта",
                        "alias": "bolezni-zheludochno-kishechnogo-trakta"
                    }
                ],
                "country": {
                    "id": 18,
                    "name": "Ливан",
                    "alias": null
                },
                "region": {
                    "id": 4388,
                    "name": "Ujar Rayon",
                    "alias": null
                },
                "city": {
                    "id": 118006,
                    "name": "Сан-Хосе",
                    "alias": null
                }
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws UnsupportLocaleException
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

        $medicalProfiles = $this->searchService->getFavorites($page, $rowsPerPage, $searchKey, $sorting,
            $locale, $userId);

        return response()->json($medicalProfiles, 200);
    }

    /**
     * @api {post} /api/{locale}/favorite/objects Добавление в избранное объекта (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName AddObjects
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
            'object_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $objectId = $request->get('object_id');

        $this->searchService->addFavorite($userId, $objectId);

        return response()->json(['message' => 'Добавлено в избранное'], 200);
    }

    /**
     * @api {delete} /api/{locale}/favorite/objects Удаление из избранного объекта (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName DeleteObjects
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
            'object_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $objectId = $request->get('object_id');

        $this->searchService->deleteFavorite($userId, $objectId);

        return response()->json(['message' => 'Удалено из избранного'], 200);
    }


    /**
     * @api {get} /api/{locale}/search/object-by-url Расширенный поиск объектов по URL (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ObjectExtendedByUrl
     * @apiGroup Searches
     *
     * @apiDescription срока url передается в соответствующий параметр, и не дожна начинаться с  / .<br>
     * <b>Схема URL:</b> <br>
     *  [ акции или скидки ] / [ блок с алиасами ] / [ рядом с чем - нибудь ( beside-sanatori-solnechnyi ) ] /  [ звездность ] /  [ mood-тег ] /<br>
     *  координаты, пагинация и сортировка на кленте передаются параметрами запроса в URL ( динамическая часть, будет скрываться от индексации ) <br>
     * <br>
     * <b>Блок с алиасами</b> <br>
     * Блок алиасов формируют алиасы географии, мед. профилей, мет. лечения и услуг, и отделяются друг от друга слэшем "/".<br>
     * Каждый элемент списка содержит в объекте seo порядок следования алиаса в url "order",  и "title" для формирования семантического шаблона в мета тэге title <br>
     * Порядок алиасов должен выстраиваться по возрастанию, задается свойством в объекте seo в элементах списков. При несоответсвии порядка будет возвращена ошибка с кодом 404 и описанием. <br>
     * <br>
     * <b>Блок акции или скидки</b> <br>
     * На данный момент к url добавляется: <b>{../discount/...}</b> в соответсвующем блоке если необходима фильтрация "По акции"
     * <br>
     * <br>
     * <b>Блок звездность</b>
     * <br>
     * блок состоит из возрастающей последовательности звездности: <br>
     * <b>{.../stars-1/stars-3/stars-4/...}</b><br>
     * <br>
     * <b>Блок Mood-тег</b>
     * <br>
     * блок состоит из возрастающей последовательности mood-тегов(алиасов mood тегов): <br>
     * <b>{.../mood-alias/mood-alias/mood-alias/...}</b><br>
     * <br>
     * <b>Блок "рядом с чем нибудь"</b>
     * <br>
     * Формат: <b>../beside-{alias}/...</b><br>
     * Подставляется тот алиас рядом с чем необходимо искать. Это может быть алиас: страны, региона, города и санатория.
     * Координаты при этом отправлять не нужно, они берутся из соответствующей сущности. <br>
     * Для поиска <b>Рядом с пользователем</b> необходимо отправить кооординаты в параметрах запроса ( в динамической части url ) <br>
     * <br>
     * <b>Например: </b> discount/bolezni-nervnoy-sistemy/aerovozdeystvie/analiz-mochi-obshchiy/animatsiya-10/beside-veshenskij/stars-3/stars-4
     * <br>
     * <br>
     * При нарушении порядка возвращается HTTP статус 404 и описание ошибки
     *
     * @apiParam {string} [url] Url - фильтр
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {integer} [page] номер страницы

     * @apiParam {numeric} [lat] широта
     * @apiParam {numeric} [lon] долгота
     *
     * @apiParam {json}  [sorting] Массив сортировки {"popular": "asc"}, {"expensive": "asc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
     {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 54,
                "title": "Cанаторий Вешенский",
                "stars": 3,
                "country_id": null,
                "region_id": null,
                "city_id": null,
                "lat": "49.630401",
                "lon": "41.707245",
                "alias": "veshenskij",
                "heating_rating": "0",
                "full_rating": "0",
                "min_price": null,
                "radius": 2040735,
                "is_favorite": false,
                "moods": [
                    {
                        "id": 2,
                        "name": "Мать и дитя",
                        "alias": "mother-and-child",
                        "image": "/storage/moods/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg",
                        "crop_image": "/storage/moods_crop/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg"
                    },
                    {
                        "id": 3,
                        "name": "Релакс",
                        "alias": "relax",
                        "image": "/storage/moods/BGETtAscXIDG0tZO5v3X0kDte7FV7RRgjhLS1Frf.jpeg",
                        "crop_image": "/storage/moods_crop/BGETtAscXIDG0tZO5v3X0kDte7FV7RRgjhLS1Frf.jpeg"
                    }
                ],
                "doctor_online": true,
                "moderated_images": [
                    {
                        "id": 2666,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_IghdaGGI5hGuvnm2.jpeg.jpeg",
                        "sorting_rule": 0,
                        "is_main": false
                    },
                    {
                        "id": 2664,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_HIEPfogWaxtmx3Md.jpeg.jpeg",
                        "sorting_rule": 1,
                        "is_main": false
                    },
                    {
                        "id": 2658,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_jZW7loDJadYDT3Vh.jpeg.jpeg",
                        "sorting_rule": 2,
                        "is_main": false
                    },
                    {
                        "id": 2656,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_9JnqFbZ1iIPeMZ8T.jpeg.jpeg",
                        "sorting_rule": 3,
                        "is_main": false
                    },
                    {
                        "id": 2659,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_idLFue74D6MpH9EM.jpeg.jpeg",
                        "sorting_rule": 4,
                        "is_main": false
                    },
                    {
                        "id": 2657,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_NwJxgLGrl9f0k8Gb.jpeg.jpeg",
                        "sorting_rule": 5,
                        "is_main": false
                    },
                    {
                        "id": 2661,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_MiSS7ixuIGiOl0AE.jpeg.jpeg",
                        "sorting_rule": 6,
                        "is_main": false
                    },
                    {
                        "id": 2662,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_L0tk5jc8Yy6Xrbp6.jpeg.jpeg",
                        "sorting_rule": 7,
                        "is_main": false
                    },
                    {
                        "id": 2663,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_55VXeTVKuiKfnZxo.jpeg.jpeg",
                        "sorting_rule": 8,
                        "is_main": false
                    },
                    {
                        "id": 2660,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_TUwiLAI240wHV0Zi.jpeg.jpeg",
                        "sorting_rule": 9,
                        "is_main": false
                    },
                    {
                        "id": 2665,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_iMOZ4rWuCQmetgd6.jpeg.jpeg",
                        "sorting_rule": 10,
                        "is_main": false
                    },
                    {
                        "id": 2655,
                        "object_id": 54,
                        "image": "/storage/object_gallery/thumbs-450x450_t4tmchIMfXRumGni.jpeg.jpeg",
                        "sorting_rule": 11,
                        "is_main": false
                    }
                ],
                "medical_profiles_public": [
                    {
                        "id": 5,
                        "name": "Болезни нервной системы",
                        "alias": "bolezni-nervnoy-sistemy"
                    },
                    {
                        "id": 10,
                        "name": "Болезни желудочно-кишечного тракта",
                        "alias": "bolezni-zheludochno-kishechnogo-trakta"
                    },
                    {
                        "id": 6,
                        "name": "Болезни эндокринной системы и обмена веществ",
                        "alias": "bolezni-endokrinnoy-sistemy-i-obmena-veshchestv"
                    },
                    {
                        "id": 12,
                        "name": "Болезни костно-мышечной системы",
                        "alias": "bolezni-kostno-myshechnoy-sistemy"
                    }
                ],
                "country": null,
                "region": null,
                "city": null
            }
        ],
        "filter_response": {
            "therapies": [],
            "medical_profiles": [],
            "diseases": [
                {
                    "id": 13,
                    "name": "Другие сальмонеллезные инфекции",
                    "alias": "disease-alias",
                    "seo": {
                        "disease_id": 13,
                        "order": 508,
                        "title": null
                    }
                }
            ],
            "services": [],
            "country": null,
            "region": null,
            "city": null,
            "discount": true,
            "beside": {
                "id": 40,
                "name": "Санаторий Жемчужина Зауралья",
                "alias": "jz",
                "latitude": "56.099596",
                "longitude": "63.55196",
                "type": "object"
            },
            "stars": [
                3
            ],
            "sorting": null,
            "page": 1,
            "latitude": "56.099596",
            "longitude": "63.55196",
            "block_order": [
                "discount",
                "aliases",
                "beside",
                "stars"
            ],
            "moods": [
                "mother-and-child",
                "ok"
            ],
            "multiple_geography": [
                {
                    "id": 7022,
                    "country_id": 88,
                    "name": "Воронежская область",
                    "alias": "voronezhskaya_obl",
                    "seo": {
                        "id": 23010,
                        "region_id": 7022,
                        "title": "Воронежская область",
                        "order": 200
                    },
                    "country": {
                        "id": 88,
                        "name": "Россия"
                    }
                },
                {
                    "id": 6990,
                    "country_id": 88,
                    "name": "Кабардино-Балкария",
                    "alias": "kabardino_balkarskaya",
                    "seo": {
                        "id": 23019,
                        "region_id": 6990,
                        "title": "Кабардино-Балкария",
                        "order": 203
                    },
                    "country": {
                        "id": 88,
                        "name": "Россия"
                    }
                },
                {
                    "id": 6986,
                    "country_id": 88,
                    "name": "Свердловская область",
                    "alias": "sverdlovskaya_obl",
                    "seo": {
                        "id": 23043,
                        "region_id": 6986,
                        "title": "Свердловская область",
                        "order": 224
                    },
                    "country": {
                        "id": 88,
                        "name": "Россия"
                    }
                }
            ]
        },
        "templates": {
            "discount": {
                "for": "discount",
                "title": "'",
                "meta_description": "template",
                "text": "template"
            },
            "beside": {
                "for": "beside",
                "title": "asdas",
                "meta_description": "template",
                "text": "template"
            }
        },
        "custom_seo": {
            "id": 1,
            "url": "discount\/disease-alias\/beside-jz\/stars-3",
            "title": "title_ru",
            "meta_description": "description_ru",
            "text": "description_ru"
        },
        "filter_data": {
            "medical_profiles": [
                5,
                10,
                6,
                12
            ],
            "services": [
                21,
                22,
                19
            ],
            "stars": [
                3
            ],
            "in_action": false,
            "objectIds": [
                54
            ],
            "multiple_geography": [
                {
                    "id": 7013,
                    "country_id": 88,
                    "name": "Саратовская область",
                    "alias": "saratovskaya_obl",
                    "count_objects": "44",
                    "seo": {
                        "id": 23042,
                        "region_id": 7013,
                        "title": "Саратовская область",
                        "order": 223
                    },
                    "country": {
                        "id": 88,
                        "name": "Россия"
                    }
                },
                {
                    "id": 7002,
                    "country_id": 88,
                    "name": "Подмосковье",
                    "alias": "podmoskovye",
                    "count_objects": "32",
                    "seo": {
                        "id": 23034,
                        "region_id": 7002,
                        "title": "Подмосковье",
                        "order": 217
                    },
                    "country": {
                        "id": 88,
                        "name": "Россия"
                    }
                }
            ]
        }
    }

     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws UnsupportLocaleException
     * @throws \App\Exceptions\ApiProblemException
     */
    public function objectSearchByUrl(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'url' => 'string|nullable',
            'lat' => 'numeric|nullable',
            'lon' => 'numeric|nullable',
            'rowsPerPage' => 'integer|nullable',
            'page' => 'integer|nullable',
            'on_main_page' => 'boolean|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $url = $request->get('url');
        $lat = $request->get('lat');
        $lon = $request->get('lon');
        $onMainPage = $request->get('on_main_page');
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $page = $request->get('page') ?? 1;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $objects = $this->searchService
            ->objectSearchByUrl($url, $rowsPerPage, $locale, null, $lat, $lon, $page, $sorting, $onMainPage);

        return response()->json($objects, 200);
    }
}
