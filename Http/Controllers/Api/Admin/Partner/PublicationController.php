<?php

namespace App\Http\Controllers\Api\Admin\Partner;

use App\Exceptions\ApiProblemException;
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
     * @api {get} /api/admin/partner/publication/type Получение типов публикации
     * @apiVersion 0.1.0
     * @apiName ListPublicationTypes
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name_ru": "статьи",
            "name_en": "articles",
            "alias": null
        },
        {
            "id": 3,
            "name_ru": "исследования",
            "name_en": "researches",
            "alias": null
        },
        {
            "id": 2,
            "name_ru": "аналитика",
            "name_en": "analytics",
            "alias": "qweqweq"
        }
    ]
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getPublicationTypes()
    {
        $publicationTypes = $this->publicationService->getPublicationTypes();

        return response()->json($publicationTypes, 200);
    }

    /**
     * @api {post} /api/admin/partner/publication/type Добавление типа публикации
     * @apiVersion 0.1.0
     * @apiName AddPublicationTypes
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name_ru Название на русском
     * @apiParam {string} name_en Название на английком
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 201 OK
    {
        "name_ru": "lkdflkdgj",
        "name_en": "lksdflkd",
        "id": 4
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addPublicationType(Request $request)
    {
        $valid = Validator($request->all(), [
           'name_ru' => 'required|string',
           'name_en' => 'required|string',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $type = $this->publicationService->addPublicationType($request);

        return response()->json($type, 201);
    }

    /**
     * @api {post} /api/admin/partner/publication/type/{typeId} Редактирование типа публикации
     * @apiVersion 0.1.0
     * @apiName EditPublicationTypes
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name_ru] Название на русском
     * @apiParam {string} [name_en] Название на английком
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "name_ru": "ru",
        "name_en": "en",
        "alias": null
    }
     *
     * @param Request $request
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function editPublicationType(Request $request, int $typeId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string',
            'name_en' => 'string',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $type = $this->publicationService->editPublicationType($request, $typeId);

        return response()->json($type, 200);
    }

    /**
     * @api {get} /api/admin/partner/publication/type/{typeId} Получение типа публикации
     * @apiVersion 0.1.0
     * @apiName DeletePublicationTypes
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "name_ru": "аналитика",
        "name_en": "analytics",
        "alias": "qweqweq",
        "seo": {
            "publication_type_id": 2,
            "for": "publication-type",
            "h1_ru": null,
            "h1_en": null,
            "title_ru": null,
            "title_en": null,
            "url": "qweqweq",
            "meta_description_ru": null,
            "meta_description_en": null,
            "meta_keywords_ru": null,
            "meta_keywords_en": null
        }
    }
     *
     *
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getPublicationType(int $typeId)
    {
       $type = $this->publicationService->getPublicationType($typeId);

        return response()->json($type, 200);
    }

    /**
     * @api {delete} /api/admin/partner/publication/type/{typeId} Удаление типа публикации
     * @apiVersion 0.1.0
     * @apiName DeletePublicationTypes
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Удалено"
    }
     *
     *
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deletePublicationType(int $typeId)
    {
        $this->publicationService->deletePublicationType( $typeId);

        return response()->json(['message' => 'Удалено'], 200);
    }

    /**
     * ====================== Publications =========================================================================
     */

    /**
     * @api {post} /api/admin/partner/publication Создание публикации
     * @apiVersion 0.1.0
     * @apiName AddPublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} publication_type_id ID Типа публикации
     * @apiParam {integer} partner_id ID Партнера
     * @apiParam {string} title_ru Название на русс.
     * @apiParam {string} title_en Название на анг.
     * @apiParam {date} [published_at] Дата публикации формат YYYY-MM-DD
     * @apiParam {string} [author_ru] Автор (русс.)
     * @apiParam {string} [author_en] Автор (ан.)
     * @apiParam {string} [description_ru] Описание (русс.)
     * @apiParam {string} [description_en] Описание (ан.)
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "publication_type_id": 2,
        "moderation_status_id": 1,
        "moderator_message": null,
        "partner_id": 1,
        "alias": null,
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": null,
        "author_ru": null,
        "author_en": null,
        "description_ru": "",
        "description_en": "",
        "active": false,
        "created_at": "2019-04-09 06:09:46",
        "updated_at": "2019-04-09 06:09:46",

    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add(Request $request)
    {
        $valid = Validator($request->all(), [
            'publication_type_id' => 'required|integer',
            'partner_id' => 'required|integer',
            'title_ru' => 'required|string|max:255',
            'title_en' => 'string|max:255|nullable',
            'author_ru' => 'string|max:255|nullable',
            'author_en' => 'string|max:255|nullable',
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'active' => 'boolean',
            'published_at' => 'nullable|date',
        ],[
            'publication_type_id.required' => 'Не указан тип публикации',
            'partner_id.required' => 'Не указан тип партнера',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $partnerId = $request->get('partner_id');
        $data = $request->only('publication_type_id', 'title_ru', 'title_en', 'author_ru',
            'author_en', 'description_ru', 'description_en', 'active', 'published_at');
        $publication = $this->publicationService->add($data, $partnerId, true);

        return response()->json($publication, 201);
    }

    /**
     * @api {put} /api/admin/partner/publication/{publicationId} Редактирование публикации
     * @apiVersion 0.1.0
     * @apiName EditPublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [publication_type_id] ID Типа публикации
     * @apiParam {integer} [partner_id] ID Партнера
     * @apiParam {string} [title_ru] Название на русс.
     * @apiParam {string} [title_en] Название на анг.
     * @apiParam {date} [published_at] Дата публикации формат YYYY-MM-DD
     * @apiParam {string} [author_ru] Автор (русс.)
     * @apiParam {string} [author_en] Автор (ан.)
     * @apiParam {string} [description_ru] Описание (русс.)
     * @apiParam {string} [description_en] Описание (ан.)
     * @apiParam {boolean} [active] активность
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "publication_type_id": 2,
        "moderation_status_id": 1,
        "moderator_message": null,
        "partner_id": 1,
        "alias": null,
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": null,
        "author_ru": null,
        "author_en": null,
        "description_ru": "",
        "description_en": "",
        "active": false,
        "created_at": "2019-04-09 06:09:46",
        "updated_at": "2019-04-09 06:09:46"
    }
     *
     * @param Request $request
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function edit(Request $request, int $publicationId)
    {
        $valid = Validator($request->all(), [
            'publication_type_id' => 'integer',
            'partner_id' => 'integer',
            'title_ru' => 'string|max:255',
            'title_en' => 'string|max:255|nullable',
            'author_ru' => 'string|max:255|nullable',
            'author_en' => 'string|max:255|nullable',
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'active' => 'boolean',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $data = $request->only('publication_type_id', 'partner_id', 'title_ru', 'title_en', 'author_ru',
            'author_en', 'description_ru', 'description_en', 'active', 'published_at');
        $publication = $this->publicationService->edit($data, $publicationId);

        return response()->json($publication, 200);
    }

    /**
     * @api {get} /api/admin/partner/publication Поиск публикаций
     * @apiVersion 0.1.0
     * @apiName SearchPublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     * @apiParam {integer} [publication_type_id] ID Типа публикации
     * @apiParam {integer} [partner_id] ID Партнера
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
                "publication_type_id": 2,
                "moderation_status_id": 1,
                "moderator_message": null,
                "partner_id": 1,
                "alias": null,
                "title_ru": "titleRu",
                "title_en": "titleEn",
                "published_at": null,
                "author_ru": null,
                "author_en": null,
                "description_ru": "",
                "description_en": "",
                "active": false,
                "created_at": "2019-04-09 06:09:46",
                "updated_at": "2019-04-09 06:09:46",
                "type": {
                    "id": 2,
                    "name": "analytics"
                },
                "partner": {
                    "id": 1,
                    "organisation_short_name": "ShortNameEn",
                    "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
                    "alias": "test"
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
            'publication_type_id' => 'integer|nullable',
            'partner_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $filter['partner_id'] = $request->get('partner_id');
        $filter['publication_type_id'] = $request->get('publication_type_id');
        $publications = $this->publicationService->search($page, $rowsPerPage, $searchKey, $sorting, $filter,false);

        return response()->json($publications, 200);
    }

    /**
     * @api {get} /api/admin/partner/publication/{publicationId} Получение публикации
     * @apiVersion 0.1.0
     * @apiName GetPublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "publication_type_id": 2,
        "moderation_status_id": 3,
        "moderator_message": null,
        "partner_id": 1,
        "alias": "test",
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": "2019-04-18",
        "author_ru": "автор",
        "author_en": "author",
        "description_ru": "desc ru",
        "description_en": "desc en",
        "active": true,
        "created_at": "2019-04-15 11:32:02",
        "updated_at": "2019-04-15 11:32:02",
        "type": {
            "id": 2,
            "name": "аналитика",
            "alias": null
        },
        "partner": {
            "id": 1,
            "organisation_short_name": "ShortNameEn",
            "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
            "alias": "test"
        },
        "seo": {
            "id": 34497,
            "for": "publication",
            "h1_ru": "тест",
            "title_ru": null,
            "url": "ewerwe",
            "meta_description_ru": null,
            "meta_keywords_ru": null,
            "h1_en": "test",
            "title_en": null,
            "meta_description_en": null,
            "meta_keywords_en": null,
            "publication_id": 2
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
        "moderation": {
            "description_ru": {
                "status_id": 1,
                "value": null,
                "message": null
            },
            "description_en": {
                "status_id": 1,
                "value": null,
                "message": null
            },
            "medical_profiles": {
                "status_id": 2,
                "value": null,
                "message": null
            },
            "therapies": {
                "status_id": 2,
                "value": null,
                "message": null
            },
            "diseases": {
                "status_id": 2,
                "value": null,
                "message": null
            },
            "objects": {
                "status_id": 2,
                "value": null,
                "message": null
            },
            "geography": {
                "status_id": 2,
                "value": {
                    "country": {
                        "id": 88,
                        "name": "Российская Федерация"
                    },
                    "region": {
                        "id": 3830,
                        "country_id": 41,
                        "name": "Oslomej",
                        "country": {
                            "id": 41,
                            "name": "Республика Македония"
                        }
                    },
                    "city": {
                        "id": 7,
                        "country_id": 88,
                        "region_id": 6977,
                        "name": "Барнаул",
                        "country": {
                            "id": 88,
                            "name": "Российская Федерация"
                        },
                        "region": {
                            "id": 6977,
                            "country_id": 88,
                            "name": "Алтайский Край"
                        }
                    }
                },
                "message": null
            }
        },
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
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws  ApiProblemException
     */
    public function get(int $publicationId)
    {
        $publication = $this->publicationService->get($publicationId);

        return response()->json($publication, 200);
    }

    /**
     * @api {delete} /api/admin/partner/publication/{publicationId} Удаление публикации
     * @apiVersion 0.1.0
     * @apiName DeletePublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Публикация удалена"
    }
     *
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function delete(int $publicationId)
    {
        $this->publicationService->delete($publicationId);

        return response()->json(['message' => 'Публикация удалена'], 200);
    }

    /**
     * ====================== Publication Gallery ===================================================================
     */

    /**
     * @api {post} /api/admin/partner/publication/image Добавление изображения
     * @apiVersion 0.1.0
     * @apiName AddGalleryImage
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} publication_id ID Публикации
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
     * @throws ApiProblemException
     */
    public function addImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'publication_id' => 'required|integer',
            'image' => 'required|file|image|max:10240',
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable'
        ],[
            'image.max' => "Максимальный размер изображения 10 МБ (10240 КБ)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $image = $this->publicationService->addImage($request, true);

        return response()->json($image, 201);
    }

    /**
     * @api {put} /api/admin/partner/publication/image/{imageId} Редактирование изображения
     * @apiVersion 0.1.0
     * @apiName EditGalleryImage
     * @apiGroup AdminPartnerPublication
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
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function editImage(Request $request, int $imageId)
    {
        $valid = Validator($request->all(), [
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable',
            'is_main' => 'boolean',
            'moderation' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('description', 'sorting_rule', 'is_main', 'moderation');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $image = $this->publicationService->editImage($data, $imageId, $sorting);

        return response()->json($image, 200);
    }

    /**
     * @api {put} /api/admin/partner/publication/image Сортировка изображений
     * @apiVersion 0.1.0
     * @apiName SortingGalleryImage
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {integer} publication_id ID Публикации
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
     * @throws \App\Exceptions\ApiProblemException
     */
    public function sortingImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'sorting' => [ 'required', new IsArray() ],
            'publication_id' => 'required|integer',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $sorting = $request->get('sorting');
        $publicationId = $request->get('publication_id');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $this->publicationService->sortingImage($sorting, $publicationId);

        return response()->json(['message' => 'Изображения отсортированы'], 200);
    }

    /**
     * @api {get} /api/admin/partner/publication/{publicationId}/images Получение изображений
     * @apiVersion 0.1.0
     * @apiName GetGalleryImages
     * @apiGroup AdminPartnerPublication
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
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getImages(int $publicationId)
    {
        $images = $this->publicationService->getImages($publicationId);

        return response()->json($images, 200);
    }

    /**
     * @api {delete} /api/admin/partner/publication/image/{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteGalleryImage
     * @apiGroup AdminPartnerPublication
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
        $this->publicationService->deleteImage($imageId);

        return response()->json(['message' => 'Изображение удалено'], 200);
    }

    /**
     * ====================== Relations ============================================================================
     */

    /**
     * @api {post} /api/admin/partner/publication/relations Редактирование / Создание связей публикации
     * @apiVersion 0.1.0
     * @apiName RelationsPublication
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} publication_id ID публикации
     * @apiParam {integer} [country_id] ID Страны
     * @apiParam {integer} [region_id] ID Региона
     * @apiParam {integer} [city_id] ID Города
     * @apiParam {array} [medical_profiles] Массив с ID мед. профилей [1,23,43,76]
     * @apiParam {array} [therapies] Массив с ID мет. лечений [1,23,43,76]
     * @apiParam {array} [diseases] Массив с ID заболеваний [1,23,43,76]
     * @apiParam {array} [objects] Массив с ID объектов (санаториев) [1,23,43,76]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message" : "Связь сохранена"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function relations(Request $request)
    {
        $valid = Validator($request->all(), [
            'publication_id' => 'required|integer',
            'medical_profiles' => [ new IsArray, 'nullable' ],
            'therapies' => [ new IsArray, 'nullable' ],
            'diseases' => [ new IsArray, 'nullable' ],
            'objects' => [ new IsArray, 'nullable' ],
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $publicationId = $request->get('publication_id');
        $medicalProfileIds = $request->get('medical_profiles');
        $therapyIds = $request->get('therapies');
        $diseaseIds = $request->get('diseases');
        $objectIds = $request->get('objects');
        $countryId = $request->get('country_id');
        $regionId = $request->get('region_id');
        $cityId = $request->get('city_id');

        $this->publicationService->relations($publicationId, $medicalProfileIds, $therapyIds,
            $diseaseIds, $objectIds, $countryId, $regionId, $cityId);

        return response()->json(['message' => 'Связь сохранена'], 200);
    }

    /**
     * @api {post} /api/admin/partner/publication/file Добавление файла
     * @apiVersion 0.1.0
     * @apiName AddFile
     * @apiGroup AdminPartnerPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} publication_id ID публикации
     * @apiParam {file} file файл ( Максимальный размер файла 20мб (20480 кб) )
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "file": "/storage/partner_files/eAbvagS7vBG8qkEuOQ0iyejj5APa6GfpX8o4bx8t.jpeg",
        "publication_id": "1",
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
            'publication_id' => 'required|integer'

        ],[
            'file.max' => "Максимальный размер файла 20мб (20480 кб)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $partnerId = $request->get('publication_id');

        $file = $this->publicationService->addFile($partnerId, $request);

        return response()->json($file, 201);
    }

    /**
     * @api {delete} /api/admin/partner/publication/file/{fileId} Удаление файла
     * @apiVersion 0.1.0
     * @apiName deleteFile
     * @apiGroup AdminPartnerPublication
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
        $this->publicationService->deleteFile($fileId);
        return response()->json([ 'message' => [
            "Файл удален"
        ]]);
    }
}
