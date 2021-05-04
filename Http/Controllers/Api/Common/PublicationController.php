<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\PublicationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublicationController extends Controller
{
    /**
     * @var PublicationService
     */
    protected $publicationService;

    /**
     * PublicationController constructor.
     */
    public function __construct()
    {
        $this->publicationService = new PublicationService();
    }

    /**
     * @api {get} /api/{locale}/partner/publication Поиск публикаций (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchPublication
     * @apiGroup PublicPublication
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {integer} [partner_id] ID партнера
     * @apiParam {integer} [publication_type_id] ID типа публикации
     * @apiParam {integer} [country_id] ID Страны
     * @apiParam {integer} [region_id] ID Региона
     * @apiParam {integer} [city_id] ID Города
     * @apiParam {integer} [medical_profile_id] ID Мед. профиля
     * @apiParam {integer} [therapy_id] ID Метода лечения
     * @apiParam {integer} [disease_id] ID Заболевания
     * @apiParam {integer} [object_id] ID Объекта санатория
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    *
    ```
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 22,
                "publication_type_id": 2,
                "partner_id": 1,
                "title": "titleRu",
                "author": null,
                "description": "",
                "published_at": null,
                "alias": "titleru-22",
                "type": {
                    "id": 2,
                    "name": "аналитика",
                    "alias": "qweqweq"
                },
                "partner": {
                    "id": 1,
                    "partner_type_id": 1,
                    "organisation_short_name": "Индия Китай",
                    "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
                    "alias": "test",
                    "type": {
                        "id": 1,
                        "name": "СМИ",
                        "alias": "werw"
                    }
                }
            }
        ]
    }
    ```
     *
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function search(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'partner_id' => 'integer|nullable',
            'publication_type_id' => 'integer|nullable',
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'medical_profile_id' => 'integer|nullable',
            'therapy_id' => 'integer|nullable',
            'disease_id' => 'integer|nullable',
            'object_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        $filter['partner_id'] = $request->get('partner_id');
        $filter['publication_type_id'] = $request->get('publication_type_id');

        $filter['country_id'] = $request->get('country_id');
        $filter['region_id'] = $request->get('region_id');
        $filter['city_id'] = $request->get('city_id');
        $filter['medical_profile_id'] = $request->get('medical_profile_id');
        $filter['therapy_id'] = $request->get('therapy_id');
        $filter['disease_id'] = $request->get('disease_id');
        $filter['object_id'] = $request->get('object_id');

        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $publications = $this->publicationService
            ->search($page, $rowsPerPage, $searchKey, $sorting, $filter,false, $locale);

        return response()->json($publications, 200);
    }

    /**
     * @api {get} /api/{locale}/partner/publication/{alias} Получение публикации (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetPublication
     * @apiGroup PublicPublication
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "publication_type_id": 2,
        "partner_id": 1,
        "title": "titleEn",
        "author": "author",
        "description": "desc en",
        "published_at": "2019-04-18",
        "alias": "test",
        "type": {
            "id": 1,
            "name": "статьи",
            "alias": null
        },
        "seo": {
            "id": 34497,
            "publication_id": 2,
            "h1": "тест",
            "title": null,
            "url": "ewerwe",
            "meta_description": null,
            "meta_keywords": null
        },
        "partner": {
            "id": 1,
            "organisation_short_name": "ShortNameEn",
            "logo": null,
            "alias": "test",
            "telephones": [
                "123123",
                "324234234",
                "43534535453"
            ],
            "email": "",
            "mail_address": "",
            "address": "AddressRu"
        },
        "images": [
            {
                "id": 2,
                "publication_id": 2,
                "image": "werwerwerw",
                "description": null,
                "sorting_rule": 0,
                "is_main": false,
                "moderation": {
                    "status_id": 3,
                    "message": null
                }
            },
            {
                "id": 1,
                "publication_id": 2,
                "image": "ffdfvdfvdfvdf",
                "description": null,
                "sorting_rule": 0,
                "is_main": false,
                "moderation": {
                    "status_id": 2,
                    "message": null
                }
            }
        ],
        "publication_files": [
            {
                "id": 5,
                "publication_id": 1,
                "file": "/storage/publication_files/GuFhFZXyTYt4bHvWFGqEfOQRwTT4F7eNoSiQpKuq.pdf",
                "description": "12312"
            },
            {
                "id": 4,
                "publication_id": 1,
                "file": "/storage/publication_files/aUivRFvAlbBJYFLZxvM0Wkk7pS769OzWy1aEzzZM.pdf",
                "description": "12312"
            },
            {
                "id": 2,
                "publication_id": 1,
                "file": "/storage/publication_files/qneV5jZeeVXa0xWIooZijTn60QqdAtnYLr5WY2pZ.pdf",
                "description": "12312"
            }
        ],
        "objects": [
            {
            "id": 30,
            "title": "Test for Logus",
            "alias": "logus"
            }
        ],
        "medical_profiles": [
            {
            "id": 5,
            "name": "Diseases of the nervous system",
            "alias": "bolezni-nervnoy-sistemy"
            }
        ],
        "therapies": [
            {
            "id": 2,
            "name": "therapeutic inspection",
            "alias": "vizualnyy-osmotr-obshcheterapevticheskiy"
            }
        ],
        "diseases": [
            {
            "id": 3,
            "name": "Cholera",
            "alias": "kholera"
            }
        ],
        "geography": {
            "id": 2,
            "publication_id": 3,
            "country_id": 88,
            "region_id": 3830,
            "city_id": 7,
            "country": {
                "id": 88,
                "name": "Российская Федерация",
                "alias": "rossiyskaya-federatsiya-88",
                "is_visible": false
            },
            "region": {
                "id": 3830,
                "country_id": 41,
                "name": "Oslomej",
                "alias": "wwew",
                "is_visible": true,
                "country": {
                    "id": 41,
                    "name": "Республика Македония",
                    "alias": "respublika-makedoniya-41",
                    "is_visible": false
                }
            },
            "city": {
                "id": 7,
                "country_id": 88,
                "region_id": 6977,
                "name": "Барнаул",
                "alias": "qwerty",
                "is_visible": true,
                "region": {
                    "id": 6977,
                    "country_id": 88,
                    "name": "Алтайский Край",
                    "alias": null,
                    "is_visible": true
                },
                "country": {
                    "id": 88,
                    "name": "Российская Федерация",
                    "alias": "rossiyskaya-federatsiya-88",
                    "is_visible": false
                }
            }
        }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $alias)
    {
        $publication = $this->publicationService->get(null, $locale, $alias);

        return response()->json($publication, 200);
    }

    /**
     * @api {get} /api/{locale}/partner/publication-type Получение списка типов публикаций (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ListPublicationType
     * @apiGroup PublicPublication
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 2,
            "name": "аналитика",
            "alias": "qweqweq"
        }
    ]
     *
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getPublicationTypes(string $locale)
    {
        $types = $this->publicationService->getPublicationTypes($locale);

        return response()->json($types, 200);
    }

    /**
     * @api {get} /api/{locale}/partner/publication-type/{alias} Получение типа публикации (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetPublicationType
     * @apiGroup PublicPublication
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name": "аналитика",
        "alias": "qweqweq",
        "seo": {
            "id": 35758,
            "publication_type_id": 2,
            "h1": null,
            "title": null,
            "url": "qweqweq",
            "meta_description": null,
            "meta_keywords": null
        }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getPublicationType(string $locale, string $alias)
    {
        $type = $this->publicationService->getPublicationType(null, $locale, $alias);

        return response()->json($type, 200);
    }
}
