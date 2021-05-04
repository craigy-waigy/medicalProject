<?php

namespace App\Http\Controllers\Api\Admin\Reservation;

use App\Exceptions\ApiProblemException;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @api {get} /api/admin/reservation/status Получение списка статусов брони
     * @apiVersion 0.1.0
     * @apiName ListStatuses
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name": "входящая бронь",
            "description": "Входящий запрос от пользователя"
        },
        {
            "id": 2,
            "name": "подтвержденная бронь",
            "description": "Подтверденная бронь, состоявшаяся поездка"
        }
    ]
     *
     * @return \App\Models\ReservationStatus[]|\Illuminate\Database\Eloquent\Collection
     */
    public function listReservationStatus()
    {
        return $this->reservationService->listReservationStatus();
    }

    /**
     * @api {post} /api/admin/reservation Добавление брони
     * @apiVersion 0.1.0
     * @apiName AddReservation
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} showcase_room_id ID номера
     * @apiParam {string} name Имя
     * @apiParam {string} email Адрес почты
     * @apiParam {string} [tel] Телефон
     * @apiParam {string} [text] Текст доп. информации
     * @apiParam {date} from_date Дата заезда ("2019-03-02")
     * @apiParam {integer} date_count Количество дней
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "showcase_room_id": 149,
        "name": "Всеволо",
        "email": "nikertos@mail.ru",
        "tel": "+7(390)49493094959",
        "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
        "from_date": "2019-03-02 00:00:00",
        "to_date": "2019-03-25 00:00:00",
        "date_count": 23,
        "reservation_status_id": 1,
        "created_at": "2019-09-30 08:50:14",
        "updated_at": "2019-09-30 08:50:14",
        "deleted_at": null,
        "status": {
            "id": 1,
            "name": "входящая бронь",
            "description": "Входящий запрос от пользователя"
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $valid = Validator($request->all(),[
            'showcase_room_id' => 'required|integer',
            'name' => 'required|string',
            'tel' => 'string',
            'email' => 'required|email',
            'text' => 'string|nullable',
            'from_date' => 'required|date',
            'date_count' => 'required|integer',
        ],[
            'response.required' => 'Не отправлен response для reCAPTCHA',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        try {

            $data = $request->only('name', 'tel', 'email', 'text', 'from_date', 'date_count',  'reservation_status_id');
            $roomId = $request->get('showcase_room_id');

            $reservation = $this->reservationService->create($roomId, $data, true);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        return response()->json($reservation, 201);
    }

    /**
     * @api {put} /api/admin/reservation/{reservationId} Редактирование брони
     * @apiVersion 0.1.0
     * @apiName EditReservation
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [showcase_room_id] ID номера
     * @apiParam {string} [name] Имя
     * @apiParam {string} [email] Адрес почты
     * @apiParam {string} [tel] Телефон
     * @apiParam {string} [text] Текст доп. информации
     * @apiParam {date} [from_date] Дата заезда ("2019-03-02")
     * @apiParam {integer} [date_count] Количество дней
     * @apiParam {integer} [reservation_status_id] Статус брони
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "showcase_room_id": 149,
        "name": "Всеволо",
        "email": "nikertos@mail.ru",
        "tel": "+7(390)49493094959",
        "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
        "from_date": "2019-03-02 00:00:00",
        "to_date": "2019-03-25 00:00:00",
        "date_count": 23,
        "reservation_status_id": 1,
        "created_at": "2019-09-30 08:50:14",
        "updated_at": "2019-09-30 08:50:14",
        "deleted_at": null,
        "status": {
            "id": 1,
            "name": "входящая бронь",
            "description": "Входящий запрос от пользователя"
        }
    }
     *
     * @param Request $request
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $reservationId)
    {
        $valid = Validator($request->all(),[
            'showcase_room_id' => 'integer',
            'name' => 'string',
            'tel' => 'string',
            'email' => 'email',
            'text' => 'string|nullable',
            'from_date' => 'date',
            'date_count' => 'integer',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        try {

            $data = $request->only('name', 'tel', 'email', 'text', 'from_date', 'date_count',
                'reservation_status_id', 'showcase_room_id');
            $reservation = $this->reservationService->update($reservationId, $data);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        return response()->json($reservation, 200);
    }

    /**
     * @api {get} /api/admin/reservation Получение списка бронирований
     * @apiVersion 0.1.0
     * @apiName ListReservation
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     * @apiParam {integer}  [reservation_status_id] Статус бронирования
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
                "id": 1,
                "showcase_room_id": 149,
                "name": "Всеволо",
                "email": "nikertos@mail.ru",
                "tel": "+7(390)49493094959",
                "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
                "from_date": "2019-03-02 00:00:00",
                "to_date": "2019-03-25 00:00:00",
                "date_count": 23,
                "reservation_status_id": 1,
                "created_at": "2019-09-30 08:11:52",
                "updated_at": "2019-09-30 08:11:52",
                "deleted_at": null,
                "status": {
                    "id": 1,
                    "name": "входящая бронь",
                    "description": "Входящий запрос от пользователя"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title_ru": "Санаторий им. М.В. Фрунзе"
                    }
                }
            },
            {
                "id": 2,
                "showcase_room_id": 149,
                "name": "Всеволо",
                "email": "nikertos@mail.ru",
                "tel": "+7(390)49493094959",
                "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
                "from_date": "2019-03-02 00:00:00",
                "to_date": "2019-03-25 00:00:00",
                "date_count": 23,
                "reservation_status_id": 1,
                "created_at": "2019-09-30 08:15:24",
                "updated_at": "2019-09-30 08:15:24",
                "deleted_at": null,
                "status": {
                    "id": 1,
                    "name": "входящая бронь",
                    "description": "Входящий запрос от пользователя"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title_ru": "Санаторий им. М.В. Фрунзе"
                    }
                }
            },
            {
                "id": 3,
                "showcase_room_id": 149,
                "name": "Всеволо",
                "email": "nikertos@mail.ru",
                "tel": "+7(390)49493094959",
                "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
                "from_date": "2019-03-02 00:00:00",
                "to_date": "2019-03-25 00:00:00",
                "date_count": 23,
                "reservation_status_id": 1,
                "created_at": "2019-09-30 08:47:52",
                "updated_at": "2019-09-30 08:47:52",
                "deleted_at": null,
                "status": {
                    "id": 1,
                    "name": "входящая бронь",
                    "description": "Входящий запрос от пользователя"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title_ru": "Санаторий им. М.В. Фрунзе"
                    }
                }
            },
            {
                "id": 4,
                "showcase_room_id": 149,
                "name": "Всеволо",
                "email": "nikertos@mail.ru",
                "tel": "+7(390)49493094959",
                "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
                "from_date": "2019-03-02 00:00:00",
                "to_date": "2019-03-25 00:00:00",
                "date_count": 23,
                "reservation_status_id": 1,
                "created_at": "2019-09-30 08:49:20",
                "updated_at": "2019-09-30 08:49:20",
                "deleted_at": null,
                "status": {
                    "id": 1,
                    "name": "входящая бронь",
                    "description": "Входящий запрос от пользователя"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title_ru": "Санаторий им. М.В. Фрунзе"
                    }
                }
            },
            {
                "id": 5,
                "showcase_room_id": 149,
                "name": "Всеволо",
                "email": "nikertos@mail.ru",
                "tel": "+7(390)49493094959",
                "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
                "from_date": "2019-03-02 00:00:00",
                "to_date": "2019-03-25 00:00:00",
                "date_count": 23,
                "reservation_status_id": 1,
                "created_at": "2019-09-30 08:50:14",
                "updated_at": "2019-09-30 08:50:14",
                "deleted_at": null,
                "status": {
                    "id": 1,
                    "name": "входящая бронь",
                    "description": "Входящий запрос от пользователя"
                },
                "room": {
                    "id": 149,
                    "object_id": 14,
                    "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
                    "object": {
                        "id": 14,
                        "title_ru": "Санаторий им. М.В. Фрунзе"
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
    public function list(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'publication_type_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'reservation_status_id' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);
        $filter['reservation_status_id'] = $request->get('reservation_status_id');

        $reservations = $this->reservationService->list($page, $rowsPerPage, $searchKey, $sorting, $filter);

        return response()->json($reservations, 200);

    }

    /**
     * @api {get} /api/admin/reservation/{reservationId} Получение брони
     * @apiVersion 0.1.0
     * @apiName GetReservation
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "showcase_room_id": 149,
        "name": "Всеволо",
        "email": "nikertos@mail.ru",
        "tel": "+7(390)49493094959",
        "text": "Вот тут что-то пишется обычно чтобы что-то уточнить",
        "from_date": "2019-03-02 00:00:00",
        "to_date": "2019-03-25 00:00:00",
        "date_count": 23,
        "reservation_status_id": 1,
        "created_at": "2019-09-30 08:50:14",
        "updated_at": "2019-09-30 08:50:14",
        "deleted_at": null,
        "status": {
            "id": 1,
            "name": "входящая бронь",
            "description": "Входящий запрос от пользователя"
        },
        "room": {
            "id": 149,
            "object_id": 14,
            "title_ru": "2-местный люкс угловой 2-4 этаж (корп.2)",
            "object": {
                "id": 14,
                "title_ru": "Санаторий им. М.В. Фрунзе"
            }
        }
    }
     *
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(int $reservationId)
    {
        try {
            $reservation = $this->reservationService->get($reservationId);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        return response()->json($reservation, 200);
    }

    /**
     * @api {delete} /api/admin/reservation/{reservationId} Удаление брони
     * @apiVersion 0.1.0
     * @apiName DeleteReservation
     * @apiGroup AdminReservations
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     { "message" : "бронь удалена" }
     *
     * @param int $reservationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $reservationId)
    {
        try {
            $this->reservationService->delete($reservationId);

        } catch (ApiProblemException $exception){

            return response()->json( ['message' => $exception->getMessage()], $exception->getCode() );
        }

        return response()->json(['message' => 'бронь удалена'], 200);
    }
}
