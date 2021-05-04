<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\PartnerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartnerController extends Controller
{
    /**
     * @var PartnerService
     */
    protected $partnerService;

    /**
     * PartnerController constructor.
     */
    public function __construct()
    {
        $this->partnerService = new PartnerService();
    }

    /**
     * @api {get} /api/{locale}/partner Поиск партнеров (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchPartner
     * @apiGroup PublicPartner
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {integer} [partner_type_id] Тип партнера
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 2,
        "items": [
            {
                "id": 2,
                "partner_type_id": 2,
                "manager_name": null,
                "organisation_short_name": null,
                "organisation_full_name": null,
                "address": "",
                "logo": null,
                "alias": "ewerwe",
                "moderated_publication_count": "1",
                "publication_types": [
                    {
                        "publication_type_id": 1,
                        "type": "статьи",
                        "alias": "qweqweq",
                        "count": 0
                    },
                    {
                        "publication_type_id": 2,
                        "type": "аналитика",
                        "alias": "qweqweq",
                        "count": 0
                    },
                    {
                        "publication_type_id": 3,
                        "type": "исследования",
                        "alias": "qweqweq",
                        "count": 0
                    }
                ],
                "type": {
                    "id": 2,
                    "name": "НИИ",
                    "alias": "qweqweq",
                    "image": null
                }
            },
            {
                "id": 1,
                "partner_type_id": 1,
                "manager_name": "ManagerRuRu",
                "organisation_short_name": "ShortNameRu1",
                "organisation_full_name": "FullnameRu1",
                "address": "",
                "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
                "alias": "test",
                "moderated_publication_count": "4",
                "publication_types": [
                    {
                        "publication_type_id": 1,
                        "type": "статьи",
                        "alias": "qweqweq",
                        "count": 2
                    },
                    {
                        "publication_type_id": 2,
                        "type": "аналитика",
                        "alias": "qweqweq",
                        "count": 0
                    },
                    {
                        "publication_type_id": 3,
                        "type": "исследования",
                        "alias": "qweqweq",
                        "count": 1
                    }
                ],
                "type": {
                    "id": 1,
                    "name": "СМИ",
                    "alias": "qweqweq",
                    "image": null
                }
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function search(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'partner_type_id' => 'integer|nullable',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        $params['partner_type_id'] = $request->get('partner_type_id');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $partners = $this->partnerService->searchPartner($page, $rowsPerPage, $searchKey, $sorting, $locale, $params);

        return response()->json($partners, 200);
    }

    /**
     * @api {get} /api/{locale}/partner/{alias} Получение партнера (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetPartner
     * @apiGroup PublicPartner
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "partner_type_id": 1,
        "manager_name": "ManagerRu",
        "organisation_short_name": "ShortNameRu1",
        "organisation_full_name": "FullnameRu1",
        "address": "AddressRu",
        "mail_address": "AddressRu",
        "logo": null,
        "alias": "test",
        "email": "",
        "telephones": [
            "123123",
            "324234234",
            "43534535453"
        ],
        "moderated_publication_count": "0",
        "type": {
            "id": 1,
            "name": "СМИ",
            "alias": "qweqweq",
            "image": null
        },
        "images": [
            {
                "id": 9,
                "partner_id": 1,
                "image": "/storage/partner_gallery/I60c6x59mXb2MKiu3EHyZV0sPzNv8sLWWeSAGxeX.jpeg",
                "description": "description",
                "sorting_rule": 0,
                "is_main": true,
                "moderation": {
                    "status_id": 3,
                    "message": null
                }
            },
            {
                "id": 7,
                "partner_id": 1,
                "image": "/storage/partner_gallery/JoKCpSkJwVQHkPzhagMAGRrwvGxT0IsIC7kmzcsi.jpeg",
                "description": "deccription",
                "sorting_rule": 0,
                "is_main": false,
                "moderation": {
                    "status_id": 3,
                    "message": null
                }
            }
        ],
        "partner_files": [
            {
                "id": 5,
                "partner_id": 1,
                "file": "/storage/partner_files/aoixjG6A62oQ6EuqwMEggCwO0Kjp6drnUYw4gM2F.jpeg",
                "description": "qwwefwe"
            },
            {
                "id": 3,
                "partner_id": 1,
                "file": "/storage/partner_files/eAbvagS7vBG8qkEuOQ0iyejj5APa6GfpX8o4bx8t.jpeg",
                "description": "qwwefwe"
            }
        ],
        "publication_types": [
            {
                "publication_type_id": 1,
                "type": "статьи",
                "alias": "qweqweq"
                "count": 0
            },
            {
                "publication_type_id": 2,
                "type": "аналитика",
                "alias": "qweqweq",
                "count": 3
            },
            {
                "publication_type_id": 3,
                "type": "исследования",
                "alias": "qweqweq",
                "count": 0
            }
        ]
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $alias)
    {
        $partner = $this->partnerService->getPartner(null, $locale, $alias);

        return response()->json($partner, 200);
    }

    /**
     * @api {get} /api/{locale}/partner-type Получение списка типов партнеров (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ListPartnerType
     * @apiGroup PublicPartner
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 2,
            "name": "НИИ",
            "image": null,
            "alias": "wwqw"
        }
    ]
     *
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function allTypes(string $locale)
    {
        $types = $this->partnerService->allTypes($locale);

        return response()->json($types, 200);
    }

    /**
     * @api {get} /api/{locale}/partner-type/{alias} Получение типа партнера (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetPartnerType
     * @apiGroup PublicPartner
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name": "НИИ",
        "image": null,
        "alias": "wwqw",
        "seo": {
            "id": 35759,
            "partner_type_id": 2,
            "h1": null,
            "title": null,
            "url": "wwqw",
            "meta_description": null,
            "meta_keywords": null
        }
    }
     *
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getType(string $locale,string $alias)
    {
        $type = $this->partnerService->getType(null, $locale, $alias);

        return response()->json($type, 200);
    }
}
