<?php

namespace App\Http\Controllers\Api\Admin\Partner;

use App\Exceptions\ApiProblemException;
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
     * ====================== PARTNER TYPES ========================================================================
     */

    /**
     * @api {post} /api/admin/partner/type Создание типа партнера
     * @apiVersion 0.1.0
     * @apiName CreatePartnerType
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name_ru Название на русс.
     * @apiParam {string} name_en Название на анг.
     * @apiParam {file}   image Логотип
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "name_ru": "СМИ",
        "name_en": "Mass media",
        "image": null,
        "created_at": null,
        "updated_at": null
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addType(Request $request)
    {
        $valid = Validator($request->all(), [
           'name_ru' => 'required|string|max:255',
           'name_en' => 'required|string|max:255',
           'image' => 'file|image|max:5128',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $type = $this->partnerService->addType($request);

        return response()->json($type, 201);
    }

    /**
     * @api {post} /api/admin/partner/type/{partnerTypeId} Редактирование типа партнера
     * @apiVersion 0.1.0
     * @apiName EditPartnerType
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name_ru] Название на русс.
     * @apiParam {string} [name_en] Название на анг.
     * @apiParam {file} [image] Логотип
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "name_ru": "СМИ",
        "name_en": "Mass media",
        "image": null,
        "created_at": null,
        "updated_at": null
    }
     *
     * @param Request $request
     * @param int $partnerTypeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function editType(Request $request, int $partnerTypeId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'image' => 'file|image|size:5128',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $type = $this->partnerService->editType($request, $partnerTypeId);

        return response()->json($type, 200);
    }

    /**
     * @api {get} /api/admin/partner/type Получение всех типов партнера
     * @apiVersion 0.1.0
     * @apiName GetPartnerTypes
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name_ru": "СМИ",
            "name_en": "Mass media",
            "image": null,
            "created_at": null,
            "updated_at": null
            "alias": "wwqw"
        },
        {
            "id": 2,
            "name_ru": "НИИ",
            "name_en": "Research institute",
            "image": null,
            "created_at": null,
            "updated_at": null.
            "alias": "wwqw"
        },
        {
            "id": 3,
            "name_ru": "ВУЗ",
            "name_en": "University",
            "image": null,
            "created_at": null,
            "updated_at": null,
            "alias": "wwqw"
        }
    ]
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function allTypes()
    {
        $types = $this->partnerService->allTypes();

        return response()->json($types, 200);
    }

    /**
     * @api {get} /api/admin/partner/type/{partnerTypeId} Получение типа партнера
     * @apiVersion 0.1.0
     * @apiName GetPartnerType
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name_ru": "НИИ",
        "name_en": "Research institute",
        "image": null,
        "created_at": null,
        "updated_at": "2019-07-04 09:52:45",
        "alias": "wwqw",
        "seo": {
            "partner_type_id": 2,
            "for": "partner-type",
            "h1_ru": null,
            "h1_en": null,
            "title_ru": null,
            "title_en": null,
            "url": "wwqw",
            "meta_description_ru": null,
            "meta_description_en": null,
            "meta_keywords_ru": null,
            "meta_keywords_en": null
        }
    }
     *
     * @param int $partnerTypeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getType(int $partnerTypeId)
    {
        $type = $this->partnerService->getType($partnerTypeId);

        return response()->json($type, 200);
    }

    /**
     * @api {delete} /api/admin/partner/type/{partnerTypeId} Удаление типа партнера
     * @apiVersion 0.1.0
     * @apiName DeletePartnerType
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": "Тип партнера удален"
     }
     *
     * @param int $partnerTypeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deleteType(int $partnerTypeId)
    {
        $this->partnerService->deleteType($partnerTypeId);
        return response()->json([
            'message' => ['Тип партнера удален']
        ], 200);
    }


    /**
     * ====================== PARTNERS =============================================================================
     */

    /**
     * @api {post} /api/admin/partner Создание партнера
     * @apiVersion 0.1.0
     * @apiName CreatePartner
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} partner_type_id Тип партнера
     * @apiParam {string} [manager_name_ru] ФИО руководителя на русс.
     * @apiParam {string} [manager_name_en] ФИО руководителя на анг.
     * @apiParam {string} organisation_short_name_ru Краткое название на русс.
     * @apiParam {string} [organisation_short_name_en] Краткое название на анг.
     * @apiParam {string} [organisation_full_name_ru] Полное название на русс.
     * @apiParam {string} [organisation_full_name_en] Полное название на анг.
     * @apiParam {string} [description_ru] Описание на русск.
     * @apiParam {string} [description_en] Описание на анг.
     * @apiParam {string} [address_ru] Адресс на русс.
     * @apiParam {string} [address_en] Адрес на анг.
     * @apiParam {boolean} [active] Активность
     * @apiParam {file} [logo] Логотип
     * @apiParam {string} [email] Эл. почта
     * @apiParam {array} [telephones] Массив с телефонами ["+7(499)335-32-65", "+7(499)335-32-66"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "partner_type_id": 2,
        "manager_name_ru": "ManagerRu",
        "manager_name_en": "ManagerEn",
        "organisation_short_name_ru": "ShortNameRu-1",
        "organisation_short_name_en": "ShortNameEn-1",
        "organisation_full_name_ru": "FullnameRu1",
        "organisation_full_name_en": "FullNameEn1",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
        "telephones": [
            "+7(499)335-32-65",
            "+7(499)335-32-66"
        ],
        "deleted_at": null,
        "created_at": "2019-04-03 10:34:31",
        "updated_at": "2019-04-03 10:37:04"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPartner(Request $request)
    {
        $valid = Validator($request->all(), [
           'partner_type_id' => 'required|integer',
           'manager_name_ru' => 'string|max:255|nullable',
           'manager_name_en' => 'string|max:255|nullable',
           'organisation_short_name_ru' => 'required|string|max:255|unique:partners|nullable',
           'organisation_short_name_en' => 'string|max:255|unique:partners|nullable',
           'organisation_full_name_ru' => 'string|max:255|unique:partners|nullable',
           'organisation_full_name_en' => 'string|max:255|unique:partners|nullable',
           'description_ru' => 'string|nullable',
           'description_en' => 'string|nullable',
           'address_ru' => 'string|nullable',
           'address_en' => 'string|nullable',
           'logo' => 'file|image|max:5124',
           'active' => 'boolean',
           'telephones' => [ new IsArray ],
           'email' => 'email|nullable',
        ],[
            'organisation_short_name_ru.unique' => "Кратокое название на русском уже существует",
            'organisation_short_name_en.unique' => "Кратокое название на английском уже существует",
            'organisation_full_name_ru.unique' => "Полное название на русском уже существует",
            'organisation_full_name_en.unique' => "Полное название на английском уже существует",
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $partner = $this->partnerService->addPartner($request);

        return response()->json($partner, 200);
    }

    /**
     * @api {post} /api/admin/partner/{partnerId} Редактирование партнера
     * @apiVersion 0.1.0
     * @apiName EditPartner
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [partner_type_id] Тип партнера
     * @apiParam {string} [manager_name_ru] ФИО руководителя на русс.
     * @apiParam {string} [manager_name_en] ФИО руководителя на анг.
     * @apiParam {string} [organisation_short_name_ru] Краткое название на русс.
     * @apiParam {string} [organisation_short_name_en] Краткое название на анг.
     * @apiParam {string} [organisation_full_name_ru] Полное название на русс.
     * @apiParam {string} [organisation_full_name_en] Полное название на анг.
     * @apiParam {string} [description_ru] Описание на русск.
     * @apiParam {string} [description_en] Описание на анг.
     * @apiParam {string} [address_ru] Адресс на русс.
     * @apiParam {string} [address_en] Адрес на анг.
     * @apiParam {string} [mail_address_ru] Почтовый адрес на рус.
     * @apiParam {string} [mail_address_en] Почтовый адрес на анг.
     * @apiParam {boolean} [active] Активность
     * @apiParam {file} [logo] Логотип
     * @apiParam {string} [email] Эл. почта
     * @apiParam {array} [telephones] Массив с телефонами ["+7(499)335-32-65", "+7(499)335-32-66"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "partner_type_id": 2,
        "manager_name_ru": "ManagerRu",
        "manager_name_en": "ManagerEn",
        "organisation_short_name_ru": "ShortNameRu-1",
        "organisation_short_name_en": "ShortNameEn-1",
        "organisation_full_name_ru": "FullnameRu1",
        "organisation_full_name_en": "FullNameEn1",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "mail_address_ru": "AddressEn",
        "mail_address_en": "AddressEn",
        "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
        "telephones": [
            "+7(499)335-32-65",
            "+7(499)335-32-66"
        ],
        "deleted_at": null,
        "created_at": "2019-04-03 10:34:31",
        "updated_at": "2019-04-03 10:37:04"
    }
     *
     * @param Request $request
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function editPartner(Request $request, int $partnerId)
    {
        $valid = Validator($request->all(), [
            'partner_type_id' => 'integer',
            'manager_name_ru' => 'string|max:255|nullable',
            'manager_name_en' => 'string|max:255|nullable',
            'organisation_short_name_ru' => "string|max:255|unique:partners,organisation_short_name_ru,{$partnerId},id|nullable",
            'organisation_short_name_en' => "string|max:255|unique:partners,organisation_short_name_en,{$partnerId},id|nullable",
            'organisation_full_name_ru' => "string|max:255|unique:partners,organisation_full_name_ru,{$partnerId},id|nullable",
            'organisation_full_name_en' => "string|max:255|unique:partners,organisation_full_name_en,{$partnerId},id|nullable",
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'address_ru' => 'string|nullable',
            'address_en' => 'string|nullable',
            'mail_address_ru' => 'string|nullable',
            'mail_address_en' => 'string|nullable',
            'logo' => 'file|image|max:5124',
            'telephones' => [ new IsArray ],
            'email' => 'email|nullable',
        ], [
            'organisation_short_name_ru.unique' => "Краткое название на русском уже существует",
            'organisation_short_name_en.unique' => "Краткое название на английском уже существует",
            'organisation_full_name_ru.unique' => "Полное название на русском уже существует",
            'organisation_full_name_en.unique' => "Полное название на английском уже существует",
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $partner = $this->partnerService->editPartner($request, $partnerId);

        return response()->json($partner, 200);
    }

    /**
     * @api {get} /api/admin/partner Поиск партнеров
     * @apiVersion 0.1.0
     * @apiName SearchPartner
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 2,
        "total": 4,
        "items": [
            {
                "id": 4,
                "partner_type_id": 2,
                "manager_name_ru": "ManagerRu",
                "manager_name_en": "ManagerEn",
                "organisation_short_name_ru": "ShortNameRu-1",
                "organisation_short_name_en": "ShortNameEn-1",
                "organisation_full_name_ru": "FullnameRu1",
                "organisation_full_name_en": "FullNameEn1",
                "description_ru": "descriptionRu",
                "description_en": "descriptionEn",
                "address_ru": "AddressRu",
                "address_en": "AddressEn",
                "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
                "telephones": [],
                "publications_count": "9",
                "publication_types": [
                    {
                        "publication_type_id": 1,
                        "type": "статьи",
                        "count": 2
                    },
                    {
                        "publication_type_id": 2,
                        "type": "аналитика",
                        "count": 0
                    },
                    {
                        "publication_type_id": 3,
                        "type": "исследования",
                        "count": 1
                    }
                ],
                "type": {
                    "id": 1,
                    "name_ru": "СМИ",
                    "name_en": "Mass media",
                    "image": null,
                    "created_at": null,
                    "updated_at": null
                }
                "deleted_at": null,
                "created_at": "2019-04-03 10:34:31",
                "updated_at": "2019-04-03 10:37:04"
            },
            {
                "id": 6,
                "partner_type_id": 2,
                "manager_name_ru": "ManagerRu",
                "manager_name_en": "ManagerEn",
                "organisation_short_name_ru": "ShortNameRu-3",
                "organisation_short_name_en": "ShortNameEn-3",
                "organisation_full_name_ru": "FullnameRu3",
                "organisation_full_name_en": "FullNameEn3",
                "description_ru": "descriptionRu",
                "description_en": "descriptionEn",
                "address_ru": "AddressRu",
                "address_en": "AddressEn",
                "logo": "/storage/partner_logo/bx0YnRb9Fyk3p9yKhRzzuMrdIvYYcZfdEVNAECgG.jpeg",
                "telephones": [
                    "123123",
                    "324234234"
                ],
                "publications_count": "9",
                "type": {
                    "id": 1,
                    "name_ru": "СМИ",
                    "name_en": "Mass media",
                    "image": null,
                    "created_at": null,
                    "updated_at": null
                },
                "publication_types": [
                    {
                        "publication_type_id": 1,
                        "type": "статьи",
                        "count": 2
                    },
                    {
                        "publication_type_id": 2,
                        "type": "аналитика",
                        "count": 0
                    },
                    {
                        "publication_type_id": 3,
                        "type": "исследования",
                        "count": 1
                    }
                ],
                "deleted_at": null,
                "created_at": "2019-04-03 10:56:06",
                "updated_at": "2019-04-03 10:56:06"
            }
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function searchPartner(Request $request)
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
        $partners = $this->partnerService->searchPartner($page, $rowsPerPage, $searchKey, $sorting, null, $params);

        return response()->json($partners, 200);
    }

    /**
     * @api {get} /api/admin/partner/{partnerId} Получение партнера
     * @apiVersion 0.1.0
     * @apiName GetPartner
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "partner_type_id": 2,
        "manager_name_ru": "ManagerRu",
        "manager_name_en": "ManagerEn",
        "organisation_short_name_ru": "ShortNameRu-1",
        "organisation_short_name_en": "ShortNameEn-1",
        "organisation_full_name_ru": "FullnameRu1",
        "organisation_full_name_en": "FullNameEn1",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "mail_address_ru": "AddressEn",
        "mail_address_en": "AddressEn",
        "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
        "email": "",
        "telephones": [
            "+7(499)335-32-65",
            "+7(499)335-32-66"
        ],
        "deleted_at": null,
        "created_at": "2019-04-03 10:34:31",
        "updated_at": "2019-04-03 10:37:04",
        "type": {
            "id": 1,
            "name": "Mass media",
            "image": null
        },
        "seo": {
            "id": 34496,
            "for": "partner",
            "h1_ru": "тест",
            "title_ru": null,
            "url": "ewerwe",
            "meta_description_ru": null,
            "meta_keywords_ru": null,
            "offer_id": null,
            "h1_en": "test",
            "title_en": null,
            "meta_description_en": null,
            "meta_keywords_en": null,
            "partner_id": 2
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
                "id": 2,
                "partner_id": 1,
                "file": "/storage/partner_files/GolTKumvUx35qSxsQyB5GwVuqJrAIqpJWbiSgaQz.jpeg",
                "description": "qwwefwe"
            }
        ],
        "publication_types": [
            {
                "publication_type_id": 1,
                "type": "статьи",
                "count": 2
            },
            {
                "publication_type_id": 2,
                "type": "аналитика",
                "count": 0
            },
            {
                "publication_type_id": 3,
                "type": "исследования",
                "count": 1
            }
        ]
    }
     *
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getPartner(int $partnerId)
    {
        $partner = $this->partnerService->getPartner($partnerId);

        return response()->json($partner, 200);
    }

    /**
     * @api {delete} /api/admin/partner/{partnerId} Удаление партнера
     * @apiVersion 0.1.0
     * @apiName DeletePartner
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": "Партнер удален"
     }
     *
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deletePartner(int $partnerId)
    {
        $this->partnerService->deletePartner($partnerId);

        return response()->json(['message' => 'Партнер удален'], 200);
    }

    /**
     * ============================= Partner gallery =============================================================
     */

    /**
     * @api {post} /api/admin/partner/image Добавление изображения
     * @apiVersion 0.1.0
     * @apiName AddGalleryImage
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} partner_id ID партнера
     * @apiParam {string} [description] Описание
     * @apiParam {integer} [sorting_rule] Порядок сортировки
     * @apiParam {file} image Изображение
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "partner_id": 1,
        "moderation_status_id": 2,
        "moderation_message": null,
        "image": "/storage/partner_gallery/VZSyJKp6EkzX7zOsrTZEuy3db3V3Ys6n3fybJlYh.jpeg",
        "description": "Descr",
        "sorting_rule": 0,
        "is_main": false,
        "created_at": "2019-04-04 12:24:27",
        "updated_at": "2019-04-04 12:42:24"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'partner_id' => 'required|integer',
            'image' => 'required|file|image|max:1024',
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable'
        ],[
            'image.max' => "Максимальный размер изображения 10 МБ (10240 КБ)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $partnerId = $request->get('partner_id');

        $image = $this->partnerService->addImage($request, $partnerId, true);

        return response()->json($image, 201);
    }

    /**
     * @api {put} /api/admin/partner/image/{imageId} Редактирование изображения
     * @apiVersion 0.1.0
     * @apiName EditGalleryImage
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} [description] Описание
     * @apiParam {integer} [sorting_rule] Порядок сортировки
     * @apiParam {boolean} [is_main] Установка главного изображения
     * @apiParam {json} [moderation] {"approve": false, "message": "message about reject}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "publication_id": 10,
        "image": "erte",
        "description": null,
        "sorting_rule": 1,
        "is_main": false,
        "created_at": null,
        "updated_at": "2019-07-12 11:14:47",
        "moderation": {
            "status_id": 4,
            "message": "asdkfaslkdjfasdf"
        }
    }
     *
     * @param Request $request
     * @param int|null $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function editImage(Request $request, ?int $imageId)
    {
        $valid = Validator($request->all(), [
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable',
            'is_main' => 'boolean',
            'moderation' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('description', 'sorting_rule', 'is_main', 'moderation');

        $image = $this->partnerService->editImage($data, $imageId);

        return response()->json($image, 200);
    }

    /**
     * @api {get} /api/admin/partner/{partnerId}/images Получение изображений
     * @apiVersion 0.1.0
     * @apiName GetGalleryImages
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 5,
            "partner_id": 1,
            "moderation_status_id": 2,
            "moderation_message": null,
            "image": "/storage/partner_gallery/up4Q7YGQZOHiuToeg4yV8lOR7Q5pattNC5iRKc37.jpeg",
            "description": "ertwertwerwtertwertwer",
            "sorting_rule": 0,
            "is_main": false,
            "created_at": "2019-04-04 12:24:48",
            "updated_at": "2019-04-04 12:42:24",
            "moderation": {
                "status_id": 3,
                "message": null
            }
        }
    ]
     *
     *
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(int $partnerId)
    {
        $images = $this->partnerService->getImages($partnerId);

        return response()->json($images, 200);
    }

    /**
     * @api {put} /api/admin/partner/image Сортировка изображений
     * @apiVersion 0.1.0
     * @apiName SortingGalleryImage
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {integer} partner_id ID парнтнера
     * @apiParam {array} sorting Массив с ID изображениями в порядке сортировки [5,4,2,3,1]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message" : "Изображения отсортированы"
     }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function sortingImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'sorting' => [ 'required', new IsArray ],
            'partner_id' => 'required|integer',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $sorting = $request->get('sorting');
        $partnerId = $request->get('partner_id');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $this->partnerService->sortingImage($sorting, $partnerId);

        return response()->json(['message' => 'Изображения отсортированы'], 200);
    }

    /**
     * @api {delete} /api/admin/partner/image/{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteGalleryImage
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message" : "Изображение удалено"
    }
     *
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deleteImage(int $imageId)
    {
        $this->partnerService->deleteImage($imageId);

        return response()->json(['message' => 'Изображение удалено'], 200);
    }

    /**
     * @api {post} /api/admin/partner/file Добавление файла партнера
     * @apiVersion 0.1.0
     * @apiName AddFile
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} partner_id ID партнера
     * @apiParam {file} file файл ( Максимальный размер файла 20мб (20480 кб) )
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "file": "/storage/partner_files/eAbvagS7vBG8qkEuOQ0iyejj5APa6GfpX8o4bx8t.jpeg",
        "partner_id": "1",
        "description": "qwwefwe",
        "id": 3
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function addFile(Request $request)
    {
        $valid = Validator($request->all(), [
            'file' => 'required|file|max:20480',
            'partner_id' => 'required|integer'

        ],[
            'file.max' => "Максимальный размер файла 20мб (20480 кб)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $partnerId = $request->get('partner_id');

        $file = $this->partnerService->addFile($partnerId, $request);

        return response()->json($file, 201);
    }

    /**
     * @api {delete} /api/admin/partner/file/{fileId} Удаление файла партнера
     * @apiVersion 0.1.0
     * @apiName deleteFile
     * @apiGroup AdminPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": [
            "Файл удален"
        ]
     }
     *
     * @param int $fileId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteFile(int $fileId)
    {
        $this->partnerService->deleteFile($fileId);
        return response()->json([ 'message' => [
            "Файл удален"
        ]]);
    }
}
