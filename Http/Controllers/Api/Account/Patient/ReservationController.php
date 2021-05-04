<?php

namespace App\Http\Controllers\Api\Account\Patient;

use App\Exceptions\ApiProblemException;
use App\Models\ReservationStatus;
use App\Rules\IsArray;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReservationController extends Controller
{
    /**
     * @var ReservationService
     */
    public $reservationService;

    /**
     * ReservationController constructor.
     *
     * @param ReservationService $reservationService
     */
    public function __construct(ReservationService $reservationService)
    {
        $this->reservationService = $reservationService;
    }

    /**
     * @api {get} /api/{locale}/account/reservation Получение списка бронирований ( local = ru, en )
     * @apiVersion 0.1.0
     * @apiName ListReservation
     * @apiGroup AccountPatient
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     * @apiParam {integer}  [reservation_id] Номер бронирования
     * @apiParam {integer}  [reservation_status_id] ID статуса брони
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 5,
        "items": [
            {
                "id": 3,
                "showcase_room_id": 287,
                "name": "Никитос",
                "email": "admin@admin.com",
                "tel": "72345678908",
                "text": "Запросик бронирования",
                "from_date": "2019-10-26 00:00:00",
                "to_date": "2019-10-03 07:54:03",
                "date_count": 1,
                "reservation_status_id": 2,
                "created_at": "2019-10-03 07:54:03",
                "updated_at": "2019-10-03 07:54:03",
                "deleted_at": null,
                "feedback_requested": false,
                "has_feedback": false,
                "token": token,
                "status": {
                    "id": 2,
                    "name": "подтвержденная бронь",
                    "description": "Подтверденная бронь, состоявшаяся поездка"
                },
                "room": {
                    "id": 287,
                    "object_id": 55,
                    "title": "\"Стандарт\" 1-местный, 1-комнатный",
                    "object": {
                        "id": 55,
                        "title": "Санаторий \"Эдем\"",
                        "country_id": 88,
                        "region_id": 6977,
                        "city_id": 141933,
                        "alias": "jedemsanatorium",
                        "stars": 4,
                        "country": {
                            "id": 88,
                            "name": "Россия"
                        },
                        "region": {
                            "id": 6977,
                            "name": "Алтайский край"
                        },
                        "city": {
                            "id": 141933,
                            "name": "Белокуриха"
                        },
                        "image": {
                            "id": 226,
                            "object_id": 55,
                            "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                            "description": null
                        }
                    }
                }
            },
            {
                "id": 5,
                "showcase_room_id": 149,
                "name": "Алексей",
                "email": "admin@admin.com",
                "tel": "79225331507",
                "text": "Мы будем с собакой!)",
                "from_date": "2019-10-10 00:00:00",
                "to_date": "2019-10-15 00:00:00",
                "date_count": 5,
                "reservation_status_id": 2,
                "created_at": "2019-10-06 12:48:57",
                "updated_at": "2019-10-06 12:48:57",
                "deleted_at": null,
                "feedback_requested": false,
                "has_feedback": false,
                "token": token,
                "status": {
                    "id": 2,
                    "name": "подтвержденная бронь",
                    "description": "Подтверденная бронь, состоявшаяся поездка"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title": "Санаторий им. М.В. Фрунзе",
                        "country_id": 88,
                        "region_id": 6996,
                        "city_id": 142922,
                        "alias": "frunze",
                        "stars": 4,
                        "country": {
                            "id": 88,
                            "name": "Россия"
                        },
                        "region": {
                            "id": 6996,
                            "name": "Краснодарский край"
                        },
                        "city": {
                            "id": 142922,
                            "name": "Сочи"
                        },
                        "image": {
                            "id": 226,
                            "object_id": 55,
                            "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                            "description": null
                        }
                    }
                }
            },
            {
                "id": 6,
                "showcase_room_id": 152,
                "name": "tet",
                "email": "admin@admin.com",
                "tel": "+1 (111) 111-11-11",
                "text": "test",
                "from_date": "2019-10-14 00:00:00",
                "to_date": "2019-10-19 00:00:00",
                "date_count": 5,
                "reservation_status_id": 2,
                "created_at": "2019-10-06 14:09:05",
                "updated_at": "2019-10-06 14:10:15",
                "deleted_at": null,
                "feedback_requested": false,
                "has_feedback": false,
                "token": token,
                "status": {
                    "id": 2,
                    "name": "подтвержденная бронь",
                    "description": "Подтверденная бронь, состоявшаяся поездка"
                },
                "room": {
                    "id": 152,
                    "object_id": 14,
                    "title": "2-местный люкс центр 5-9 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title": "Санаторий им. М.В. Фрунзе",
                        "country_id": 88,
                        "region_id": 6996,
                        "city_id": 142922,
                        "alias": "frunze",
                        "stars": 4,
                        "country": {
                            "id": 88,
                            "name": "Россия"
                        },
                        "region": {
                            "id": 6996,
                            "name": "Краснодарский край"
                        },
                        "city": {
                            "id": 142922,
                            "name": "Сочи"
                        },
                        "image": {
                            "id": 226,
                            "object_id": 55,
                            "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                            "description": null
                        }
                    }
                }
            },
            {
                "id": 2,
                "showcase_room_id": 149,
                "name": "Антон",
                "email": "admin@admin.com",
                "tel": "79628841999",
                "text": "test",
                "from_date": "2019-10-01 00:00:00",
                "to_date": "2019-10-06 00:00:00",
                "date_count": 5,
                "reservation_status_id": 2,
                "created_at": "2019-09-30 17:15:33",
                "updated_at": "2019-09-30 17:15:33",
                "deleted_at": null,
                "feedback_requested": false,
                "has_feedback": false,
                "token": token,
                "status": {
                    "id": 2,
                    "name": "подтвержденная бронь",
                    "description": "Подтверденная бронь, состоявшаяся поездка"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title": "Санаторий им. М.В. Фрунзе",
                        "country_id": 88,
                        "region_id": 6996,
                        "city_id": 142922,
                        "alias": "frunze",
                        "stars": 4,
                        "country": {
                            "id": 88,
                            "name": "Россия"
                        },
                        "region": {
                            "id": 6996,
                            "name": "Краснодарский край"
                        },
                        "city": {
                            "id": 142922,
                            "name": "Сочи"
                        },
                        "image": {
                            "id": 226,
                            "object_id": 55,
                            "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                            "description": null
                        }
                    }
                }
            },
            {
                "id": 1,
                "showcase_room_id": 149,
                "name": "Антон",
                "email": "admin@admin.com",
                "tel": "79628841999",
                "text": "test",
                "from_date": "2019-10-01 00:00:00",
                "to_date": "2019-10-06 00:00:00",
                "date_count": 5,
                "reservation_status_id": 2,
                "created_at": "2019-09-30 16:31:07",
                "updated_at": "2019-09-30 16:31:07",
                "deleted_at": null,
                "feedback_requested": false,
                "has_feedback": false,
                "token": token,
                "status": {
                    "id": 2,
                    "name": "подтвержденная бронь",
                    "description": "Подтверденная бронь, состоявшаяся поездка"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title": "Санаторий им. М.В. Фрунзе",
                        "country_id": 88,
                        "region_id": 6996,
                        "city_id": 142922,
                        "alias": "frunze",
                        "stars": 4,
                        "country": {
                            "id": 88,
                            "name": "Россия"
                        },
                        "region": {
                            "id": 6996,
                            "name": "Краснодарский край"
                        },
                        "city": {
                            "id": 142922,
                            "name": "Сочи"
                        },
                        "image": {
                            "id": 226,
                            "object_id": 55,
                            "image": "/storage/object_gallery/thumbs-450x450_WwoVUvlAYneurQH1.jpeg.jpeg",
                            "description": null
                        }
                    }
                }
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'publication_type_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
            'reservation_id' => 'integer|nullable',
            'reservation_status_id' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);
        $filter['reservation_status_id'] = $request->get('reservation_status_id');
        $filter['reservation_id'] = $request->get('reservation_id');
        $filter['email'] = Auth::user()->email;

        try {
            $reservations = $this->reservationService->list($page, $rowsPerPage, $searchKey, $sorting, $filter, $locale);
        } catch (ApiProblemException $e){
            return response()->json( ['message' => $e->getMessage()], $e->getCode() );
        }

        return response()->json($reservations, 200);

    }

    /**
     * @api {get} /api/{locale}/account/reservation/{reservationId} Получение брони ( local = ru, en )
     * @apiVersion 0.1.0
     * @apiName GetReservation
     * @apiGroup AccountPatient
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 3,
        "showcase_room_id": 287,
        "name": "Никитос",
        "email": "admin@admin.com",
        "tel": "72345678908",
        "text": "Запросик бронирования",
        "from_date": "2019-10-26 00:00:00",
        "to_date": "2019-10-03 07:54:03",
        "date_count": 1,
        "reservation_status_id": 2,
        "token": token,
        "created_at": "2019-10-03 07:54:03",
        "updated_at": "2019-10-03 07:54:03",
        "deleted_at": null,
        "feedback_requested": false,
        "has_feedback": false,
        "status": {
            "id": 2,
            "name": "подтвержденная бронь"
        },
        "room": {
            "id": 287,
            "object_id": 55,
            "title": "\"Стандарт\" 1-местный, 1-комнатный",
            "object": {
                "id": 55,
                "title": "Санаторий \"Эдем\"",
                "country_id": 88,
                "region_id": 6977,
                "city_id": 141933,
                "country": {
                    "id": 88,
                    "name": "Россия"
                },
                "region": {
                    "id": 6977,
                    "name": "Алтайский край"
                },
                "city": {
                    "id": 141933,
                    "name": "Белокуриха"
                }
            }
        }
    }
     /*
     * @param string $locale
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(string $locale, int $reservationId)
    {
        try {
            $reservation = $this->reservationService->get($reservationId, $locale);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        if ( Auth::user()->email != $reservation->email )
            return response()->json( ['message' => 'А это чужая бронь'], 403 );

        return response()->json($reservation, 200);
    }
}
