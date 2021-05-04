<?php

namespace App\Http\Controllers\Api\Common;

use App\Exceptions\ApiProblemException;
use App\Services\PublicObjectService;
use App\Services\SearchService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjectController extends Controller
{
    /**
     * @var SearchService
     */
    protected $searchService;

    /**
     * @var PublicObjectService
     */
    protected $publicObjectService;

    /**
     * ObjectController constructor.
     */
    public function __construct()
    {
        $this->searchService = new SearchService();
        $this->publicObjectService = new PublicObjectService();
    }

    /**
     * @api {get} /api/{locale}/objects-show Краткий список объектов для разных страниц (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ObjectsShow
     * @apiGroup Searches
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {boolean} [on_main_page] Санатории на главной странице (true если запрос с главной страницы)
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 4,
        "total": 4,
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
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function showObject(Request $request, string $locale)
    {
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 4;
        $objects = $this->searchService->showObject($page, $rowsPerPage, $locale);

        return response()->json($objects, 200);
    }

    /**
     * @api {get} /api/{locale}/object/{alias} Получение информации по объекту (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName ObjectShow
     * @apiGroup PublicObject
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "country_id": 88,
        "region_id": 6977,
        "city_id": 7,
        "title": "название объекта-5",
        "description": "описание объекта",
        "documents": "документы для объекта",
        "visa_information": "информация по визе объекта",
        "contraindications": "противопоказания на объекта",
        "payment_description": null,
        "in_action": false,
        "address": "адрес объекта",
        "stars": 1,
        "lat": null,
        "lon": null,
        "street_view_link": null,
        "service_categories": [
            {
                "id": 1,
                "name": "Детям",
                "image": "/storage/service_icon/xdG9T47CIofe4Jz1d6nCELxuS87y8DWZCHP1OcA2.jpeg",
                "services": [
                    {
                        "id": 8,
                        "service_category_id": 1,
                        "name": "Детская комната"
                    },
                    {
                        "id": 9,
                        "service_category_id": 1,
                        "name": "Игровая площадка"
                    }
                ]
            },
                {
                    "id": 4,
                    "name": "Питание",
                    "image": "/storage/service_icon/xdG9T47CIofe4Jz1d6nCELxuS87y8DWZCHP1OcA2.jpeg",
                    "services": [
                        {
                            "id": 29,
                            "service_category_id": 4,
                            "name": "Ресторан"
                        }
                    ]
            }
        ],
        "showcase_rooms_public": [
            {
                "id": 11,
                "capacity": 1,
                "capacity_min": 2,
                "capacity_max": 10,
                "square": null,
                "title": "Title",
                "description": null,
                "interior": null,
                "price": null,
                "images": []
            },
            {
                "id": 9,
                "capacity": 1,
                "capacity_min": 2,
                "capacity_max": 10,
                "square": null,
                "title": "TitleRu",
                "description": "descriptionRu",
                "interior": "interiorRu",
                "price": 3000,
                "image": "/storage/showcase_room/D7Tx1QT8mEasjfjxI7k4Q6AGwS7fMLBjNmbipxy9.jpeg",
                "images": [
                    {
                        "id": 21,
                        "showcase_room_id": 9,
                        "image": "image",
                        "description": "desscription"
                    },
                    {
                        "id": 22,
                        "showcase_room_id": 9,
                        "image": "image",
                        "description": "desscription"
                    },
                    {
                        "id": 23,
                        "showcase_room_id": 9,
                        "image": "image",
                        "description": "desscription"
                    },
                    {
                        "id": 24,
                        "showcase_room_id": 9,
                        "image": "image",
                        "description": "desscription"
                    }
                ]
            }
        ],
        "moderated_images": [
            {
                "id": 1,
                "object_id": 5,
                "image": "/storage/object_gallery/xdG9T47CIofe4Jz1d6nCELxuS87y8DWZCHP1OcA2.jpeg",
                "description": "qwerty"
            },
            {
                "id": 2,
                "object_id": 5,
                "image": "/storage/object_gallery/lUfUkTeUFISXaNG0C3dmp3g5NzIexzOiaNp7HnUY.jpeg",
                "description": "qwerty"
            }
        ],
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "online": true,
            "languages": [
                "ru",
                "en"
            ],
            "specializations": "[\"terapevt\", \"okulist\"]",
            "user": {
                "id": 45,
                "fullname": "last_name SanatoriumDoctor2 father_name",
                "avatar": null
            }
        },
        "feedback": [
            {
                "id": 20,
                "reservation_id": 1,
                "object_id": 14,
                "quality_impressions": 5,
                "quality_healing": 3,
                "quality_rooms": 5,
                "quality_cleaning_rooms": 5,
                "quality_nutrition": 5,
                "quality_entertainment": 5,
                "liked": "liked",
                "not_liked": "not liked true",
                "comment": "my comment",
                "has_answer": true,
                "reservation": {
                    "id": 1,
                    "email": "nikertos@mail.ru",
                    "user": {
                        "id": 26,
                        "fullname": "lastname name fathername",
                        "email": "nikertos@mail.ru",
                        "avatar": null
                    }
                },
                "sanatorium_answer": {
                    "id": 1,
                    "feedback_id": 20,
                    "comment": "ответ санатория",
                    "commented_at": "2019-10-02 17:16:49"
                }
            }
        ],
        "feedback_count": 1,
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
        "therapies_public": [
            {
                "id": 78,
                "name": "Миоэлектростимуляция",
                "alias": "mioelektrostimulyatsiya"
            },
            {
                "id": 176,
                "name": "Прием (осмотр, консультация) врача-кардиолога",
                "alias": "priem-osmotr-konsultatsiya-vracha-kardiologa"
            }
        ],
        "award_icons_public": [
            {
                "id": 1,
                "name": "NmaeRu",
                "description": null,
                "image": "/storage/award_icons/UuRP6TXK030powUKPD0Rx2fWMdAHtGHBPzgmDgeU.jpeg"
            },
            {
                "id": 3,
                "name": "TitleRurr",
                "description": "DescriptionRuerter"
                "image": "/storage/award_icons/UuRP6TXK030powUKPD0Rx2fWMdAHtGHBPzgmDgeU.jpeg"
            },
            {
                "id": 4,
                "name": "TitleRurr",
                "description": "DescriptionRuerter"
                "image": "/storage/award_icons/UuRP6TXK030powUKPD0Rx2fWMdAHtGHBPzgmDgeU.jpeg"
            }
        ],
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
            "name": "23",
            "alias": "mother-and-child",
            "image": "/storage/moods/BGETtAscXIDG0tZO5v3X0kDte7FV7RRgjhLS1Frf.jpeg",
            "crop_image": "/storage/moods_crop/BGETtAscXIDG0tZO5v3X0kDte7FV7RRgjhLS1Frf.jpeg"
        }
    ],
        "country": {
            "id": 88,
            "name": "Российская Федерация",
            "alias": null
        },
        "region": {
            "id": 6977,
            "name": "Алтайский Край",
            "alias": null
        },
        "city": {
            "id": 7,
            "name": "Барнаул",
            "alias": null
        }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getObject(string $locale, string $alias)
    {
        $object = $this->publicObjectService->getObject($locale, $alias);

        return response()->json($object, 200);

    }
}
