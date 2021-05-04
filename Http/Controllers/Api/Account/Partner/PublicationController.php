<?php

namespace App\Http\Controllers\Api\Account\Partner;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\PublicationModerationService;
use App\Services\PublicationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublicationController extends Controller
{
    /**
     * @var PublicationService
     */
    protected $publicationService;

    /**
     * @var PublicationModerationService
     */
    protected $publicationModerationService;

    /**
     * PublicationController constructor.
     */
    public function __construct()
    {
        $this->publicationService = new PublicationService();
        $this->publicationModerationService = new PublicationModerationService();
    }

    /**
     * @api {get} /api/account/partner/publication Поиск публикаций
     * @apiVersion 0.1.0
     * @apiName SearchPublication
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     * @apiParam {integer} [publication_type_id] ID Типа публикации
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
                    "name_ru": "аналитика"
                },
                "partner": {
                    "id": 1,
                    "partner_type_id": 1,
                    "organisation_short_name_ru": "ShortNameRu1",
                    "type": {
                        "id": 1,
                        "image": null,
                        "name_ru": "СМИ"
                    }
                }
            },
            {
                "id": 3,
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
                "created_at": "2019-04-09 06:09:50",
                "updated_at": "2019-04-09 06:09:50",
                "type": {
                    "id": 2,
                    "name_ru": "аналитика"
                },
                "partner": {
                    "id": 1,
                    "partner_type_id": 1,
                    "organisation_short_name_ru": "ShortNameRu1",
                    "type": {
                        "id": 1,
                        "image": null,
                        "name_ru": "СМИ"
                    }
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
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;
        $filter['partner_id'] = $partnerId;
        $filter['publication_type_id'] = $request->get('publication_type_id');

        $publications = $this->publicationService->search($page, $rowsPerPage, $searchKey, $sorting, $filter,false);

        return response()->json($publications, 200);
    }

    /**
     * @api {get} /api/account/partner/publication/{publicationId} Получение публикации
     * @apiVersion 0.1.0
     * @apiName GetPublication
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
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
                "value": [
                    {
                    "id": 5,
                    "name_ru": "Болезни нервной системы"
                    },
                    {
                    "id": 7,
                    "name_ru": "Болезни органов зрения"
                    },
                    {
                    "id": 6,
                    "name_ru": "Болезни эндокринной системы и обмена веществ"
                    }
                ],
                "message": null
            },
            "therapies": {
                "status_id": 2,
                "value": [
                    {
                    "id": 1,
                    "name_ru": "Сбор анамнеза и жалоб общетерапевтический"
                    },
                    {
                    "id": 2,
                    "name_ru": "Визуальный осмотр общетерапевтический"
                    },
                    {
                    "id": 3,
                    "name_ru": "Пальпация общетерапевтическая"
                    },
                    {
                    "id": 4,
                    "name_ru": "Аускультация общетерапевтическая"
                    }
                ],
                "message": null
            },
            "diseases": {
                "status_id": 2,
                "value": [
                    {
                    "id": 3,
                    "name_ru": "Холера"
                    },
                    {
                    "id": 4,
                    "name_ru": "Холера, вызванная холерным вибрионом 01, биовар cholerae"
                    }
                ],
                "message": null
            },
            "objects": {
                "status_id": 2,
                "value": [
                    {
                    "id": 36,
                    "title_ru": "Санаторий Долина Нарсанов Ес"
                    },
                    {
                    "id": 70,
                    "title_ru": "Санаторий Сунгуль"
                    }
                ],
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
        "type": {
            "id": 2,
            "name": "analytics"
        },
        "partner": {
            "id": 1,
            "organisation_short_name": "ShortNameEn",
            "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
            "alias": "test"
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
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function get(int $publicationId)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $publication = $this->publicationService->get($publicationId, null, null, $partnerId);

        return response()->json($publication, 200);
    }

    /**
     * @api {post} /api/account/partner/publication Создание публикации
     * @apiVersion 0.1.0
     * @apiName AddPublication
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} publication_type_id ID Типа публикации
     * @apiParam {string} title_ru Название на русс.
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
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function add(Request $request)
    {
        $valid = Validator($request->all(), [
            'publication_type_id' => 'required|integer',
            'title_ru' => 'required|string|max:255',
            'title_en' => 'string|max:255',
            'author_ru' => 'string|max:255',
            'author_en' => 'string|max:255',
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'active' => 'boolean',
            'published_at' => 'nullable|date',
        ],[
            'publication_type_id.required' => 'Не указан тип публикации',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $dataNoModeration = $request->only('publication_type_id', 'title_ru', 'title_en', 'author_ru',
            'author_en', 'active', 'published_at', 'description_ru', 'description_en');
        $publication = $this->publicationService->add($dataNoModeration, $partnerId);

        $dataToModeration = $request->only('description_ru', 'description_en');
        $this->publicationModerationService->saveData($dataToModeration, $publication->id, $partnerId);

        return response()->json($publication, 201);
    }

    /**
     * @api {put} /api/account/partner/publication/{publicationId} Редактирование публикации
     * @apiVersion 0.1.0
     * @apiName EditPublication
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [publication_type_id] ID Типа публикации
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
    *

    {
        "id": 10,
        "publication_type_id": 2,
        "moderation_status_id": 2,
        "moderator_message": null,
        "partner_id": 1,
        "alias": "ewerwe",
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": "2019-04-18",
        "author_ru": null,
        "author_en": null,
        "active": false,
        "created_at": "2019-04-25 09:13:57",
        "updated_at": "2019-04-25 10:34:56",
        "description_ru": "",
        "description_en": "",
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
                "status_id": 3,
                "value": null,
                "message": null
            },
            "therapies": {
                "status_id": 3,
                "value": [
                    {
                        "id": 5,
                        "name_ru": "Перкуссия общетерапевтическая"
                    },
                    {
                        "id": 6,
                        "name_ru": "Термометрия общая"
                    }
                ],
                "message": null
            },
            "diseases": {
                "status_id": 2,
                "value": [
                    {
                        "id": 3,
                        "name_ru": "Холера"
                    },
                    {
                        "id": 4,
                        "name_ru": "Холера, вызванная холерным вибрионом 01, биовар cholerae"
                    }
                ],
                "message": null
            },
            "objects": {
                "status_id": 3,
                "value": [
                    {
                        "id": 30,
                        "title_ru": "Тестовый санаторий для Логуса"
                    }
                ],
                "message": null
            },
            "geography": {
                "status_id": 2,
                "value": {
                    "country": {
                        "id": 88,
                        "name_ru": "Российская Федерация"
                    },
                    "region": {
                        "id": 3830,
                        "country_id": 41,
                        "name_ru": "Oslomej",
                        "country": {
                            "id": 41,
                            "name_ru": "Республика Македония"
                        }
                    },
                    "city": {
                        "id": 7,
                        "country_id": 88,
                        "region_id": 6977,
                        "name_ru": "Барнаул",
                        "country": {
                            "id": 88,
                            "name_ru": "Российская Федерация"
                        },
                        "region": {
                            "id": 6977,
                            "country_id": 88,
                            "name_ru": "Алтайский Край"
                        }
                    }
                },
                "message": null
            }
        },
        "images": [
            {
                "id": 5,
                "publication_id": 10,
                "image": "erte",
                "description": null,
                "sorting_rule": 1,
                "is_main": false,
                "moderation": {
                    "status_id": 4,
                    "message": "asdkfaslkdjfasdf"
                }
            }
        ]
    }

     /*
     * @param Request $request
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function edit(Request $request, int $publicationId)
    {
        $valid = Validator($request->all(), [
            'publication_type_id' => 'integer',
            'title_ru' => 'string|max:255|nullable',
            'title_en' => 'string|max:255|nullable',
            'author_ru' => 'string|max:255|nullable',
            'author_en' => 'string|max:255|nullable',
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'active' => 'boolean',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $dataToModeration = $request->only('description_ru', 'description_en');
        $this->publicationModerationService->saveData($dataToModeration, $publicationId, $partnerId);

        $dataNoModeration = $request->only('publication_type_id', 'title_ru', 'title_en', 'author_ru',
            'author_en', 'active', 'published_at');
        $publication = $this->publicationService->edit($dataNoModeration, $publicationId, $partnerId);

        return response()->json($publication, 200);
    }

    /**
     * @api {delete} /api/account/partner/publication/{publicationId} Удаление публикации
     * @apiVersion 0.1.0
     * @apiName DeletePublication
     * @apiGroup AccountPartner
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
     * @throws ApiProblemException
     */
    public function delete(int $publicationId)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $this->publicationService->delete($publicationId, $partnerId);

        return response()->json(['message' => 'Публикация удалена'], 200);
    }

    /**
     * ============================ Gallery ========================================================================
     */

    /**
     * @api {post} /api/account/partner/publication/image Добавление изображения в галерею публикации
     * @apiVersion 0.1.0
     * @apiName AddGalleryImage
     * @apiGroup AccountPartner
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
        "id": 13,
        "partner_id": 1,
        "image": "/storage/partner_gallery/SD6xVj9BwZu0AEFMRh1GbhiPXRXDYbfwvqOY8BHi.jpeg",
        "description": "12312",
        "sorting_rule": 0,
        "is_main": false,
        "created_at": "2019-07-04 03:57:18",
        "updated_at": "2019-07-04 03:57:18",
        "moderation": {
            "status_id": 2,
            "message": null
        }
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
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $image = $this->publicationService->addImage($request, false, $partnerId);

        return response()->json($image, 201);
    }

    /**
     * @api {put} /api/account/partner/publication/image/{imageId} Редактирование изображения галереи публикации
     * @apiVersion 0.1.0
     * @apiName EditGalleryImage
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} [description] Описание
     * @apiParam {integer} [sorting_rule] Порядок сортировки
     * @apiParam {boolean} [is_main] Установка главного изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 13,
        "partner_id": 1,
        "image": "/storage/partner_gallery/SD6xVj9BwZu0AEFMRh1GbhiPXRXDYbfwvqOY8BHi.jpeg",
        "description": "12312",
        "sorting_rule": 0,
        "is_main": false,
        "created_at": "2019-07-04 03:57:18",
        "updated_at": "2019-07-04 03:57:18",
        "moderation": {
            "status_id": 2,
            "message": null
        }
    }
     *
     * @param Request $request
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function editImage(Request $request, int $imageId)
    {
        $valid = Validator($request->all(), [
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable',
            'is_main' => 'boolean'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;
        $data = $request->only('description', 'sorting_rule', 'is_main');

        $image = $this->publicationService->editImage($data, $imageId, $partnerId);

        return response()->json($image, 200);
    }

    /**
     * @api {put} /api/account/partner/publication/image Сортировка изображений в галереи публикации
     * @apiVersion 0.1.0
     * @apiName SortingGalleryImage
     * @apiGroup AccountPartner
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
     * @throws ApiProblemException
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

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $this->publicationService->sortingImage($sorting, $publicationId, $partnerId);

        return response()->json(['message' => 'Изображения отсортированы'], 200);
    }

    /**
     * @api {get} /api/account/partner/publication/{publicationId}/images Получение изображений галереи публикации
     * @apiVersion 0.1.0
     * @apiName GetGalleryImages
     * @apiGroup AccountPartner
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
                "status_id": 2,
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
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $images = $this->publicationService->getImages($publicationId, $partnerId);

        return response()->json($images, 200);
    }

    /**
     * @api {delete} /api/account/partner/publication/image/{imageId} Удаление изображения из галереи публикации
     * @apiVersion 0.1.0
     * @apiName DeleteGalleryImage
     * @apiGroup AccountPartner
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
     * @throws ApiProblemException
     */
    public function deleteImage(int $imageId)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $this->publicationService->deleteImage($imageId, $partnerId);

        return response()->json(['message' => 'Изображение удалено'], 200);
    }

    /**
     * ============================= Relation ==================================================================
     */

    /**
     * @api {post} /api/account/partner/publication/relations Редактирование / Создание связей публикации
     * @apiVersion 0.1.0
     * @apiName RelationsPublication
     * @apiGroup AccountPartner
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
    *
        {
        "id": 10,
        "publication_type_id": 2,
        "moderation_status_id": 2,
        "moderator_message": null,
        "partner_id": 1,
        "alias": "ewerwe",
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": "2019-04-18",
        "author_ru": null,
        "author_en": null,
        "active": true,
        "created_at": "2019-04-25 09:13:57",
        "updated_at": "2019-04-25 10:34:56",
        "description_ru": "",
        "description_en": "",
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
                    "value": [
                        {
                            "id": 5,
                            "name_ru": "Болезни нервной системы"
                        },
                        {
                            "id": 7,
                            "name_ru": "Болезни органов зрения"
                        },
                        {
                            "id": 6,
                            "name_ru": "Болезни эндокринной системы и обмена веществ"
                        }
                    ],
                    "message": null
                },
                "therapies": {
            "status_id": 2,
                    "value": [
                        {
                            "id": 1,
                            "name_ru": "Сбор анамнеза и жалоб общетерапевтический"
                        },
                        {
                            "id": 2,
                            "name_ru": "Визуальный осмотр общетерапевтический"
                        },
                        {
                            "id": 3,
                            "name_ru": "Пальпация общетерапевтическая"
                        },
                        {
                            "id": 4,
                            "name_ru": "Аускультация общетерапевтическая"
                        }
                    ],
                    "message": null
                },
                "diseases": {
            "status_id": 2,
                    "value": [
                        {
                            "id": 3,
                            "name_ru": "Холера"
                        },
                        {
                            "id": 4,
                            "name_ru": "Холера, вызванная холерным вибрионом 01, биовар cholerae"
                        }
                    ],
                    "message": null
                },
                "objects": {
            "status_id": 2,
                    "value": [
                        {
                            "id": 35,
                            "title_ru": "Санаторий Долина Нарзанов Жв"
                        },
                        {
                            "id": 36,
                            "title_ru": "Санаторий Долина Нарсанов Ес"
                        }
                    ],
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
            "images": [
                {
                    "id": 5,
                    "publication_id": 10,
                    "image": "erte",
                    "description": null,
                    "sorting_rule": 1,
                    "is_main": false,
                    "moderation": {
                    "status_id": 3,
                        "message": null
                    }
                }
            ],
            "type": {
            "id": 2,
                "name": "analytics"
            },
            "partner": {
            "id": 1,
                "organisation_short_name": "ShortNameEn",
                "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
                "alias": "test"
            },
            "seo": null,
            "objects": [],
            "medical_profiles": [],
            "therapies": [],
            "diseases": [],
            "geography": null,
            "publication_files": []
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
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $dataToModeration = $request->only('medical_profiles', 'therapies', 'diseases', 'objects',
            'country_id', 'region_id', 'city_id');
        $this->publicationModerationService->saveData($dataToModeration, $publicationId, $partnerId);
        $publication = $this->publicationService->get($publicationId);

        return response()->json($publication, 200);
    }

    /**
     * @api {post} /api/account/partner/publication/file Добавление файла публикации
     * @apiVersion 0.1.0
     * @apiName AddPublicationFile
     * @apiGroup AccountPartner
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
     * @api {delete} /api/account/partner/publication/file/{fileId} Удаление файла публикации
     * @apiVersion 0.1.0
     * @apiName deletePublicationFile
     * @apiGroup AccountPartner
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
