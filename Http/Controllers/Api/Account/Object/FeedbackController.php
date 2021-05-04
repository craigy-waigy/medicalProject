<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FeedbackController extends Controller
{
    /**
     * @var FeedbackService
     */
    protected $feedbackService;

    /**
     * FeedbackController constructor.
     *
     * @param FeedbackService $feedbackService
     */
    public function __construct( FeedbackService $feedbackService )
    {
        $this->feedbackService = $feedbackService;
    }

    /**
     * @api {get} /api/account/object/feedback Список отзывов
     * @apiVersion 0.1.0
     * @apiName ListFeedback
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     * @apiParam {integer}  [reservation_id] Номер брони
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 2,
        "items": [
            {
                "id": 2,
                "reservation_id": 3,
                "object_id": 14,
                "quality_impressions": 5,
                "quality_healing": 5,
                "quality_rooms": 5,
                "quality_cleaning_rooms": 5,
                "quality_nutrition": 5,
                "quality_entertainment": 5,
                "liked": null,
                "not_liked": null,
                "comment": null,
                "result_rating": "10",
                "deleted_at": null,
                "created_at": null,
                "updated_at": null,
                "has_answer": false,
                "object": {
                    "id": 14,
                    "title_ru": "Санаторий им. М.В. Фрунзе",
                    "image": {
                        "id": 274,
                        "object_id": 14,
                        "image": "/storage/object_gallery/thumbs-450x450_c8tvjI6aqPGuiZqeyWBR4JX1C9KtZxB26chEiPet.jpeg.jpeg",
                        "description": null
                    }
                },
                "reservation": {
                    "id": 3,
                    "email": "admin@admin.com",
                    "showcase_room_id": 287,
                    "from_date": "2019-10-26 00:00:00",
                    "to_date": "2019-10-03 07:54:03",
                    "room": {
                        "id": 287,
                        "title": "\"Стандарт\" 1-местный, 1-комнатный"
                    },
                    "user": {
                        "id": 12,
                        "fullname": "Админов Админ Админович",
                        "email": "admin@admin.com",
                        "avatar": null
                    }
                },
                images": [],
                "sanatorium_answer": null
            },
            {
                "id": 5,
                "reservation_id": 5,
                "object_id": 14,
                "quality_impressions": 5,
                "quality_healing": 5,
                "quality_rooms": 5,
                "quality_cleaning_rooms": 5,
                "quality_nutrition": 5,
                "quality_entertainment": 5,
                "liked": null,
                "not_liked": null,
                "comment": null,
                "result_rating": "10",
                "deleted_at": null,
                "created_at": "2019-10-09 10:41:38",
                "updated_at": "2019-10-09 10:41:38",
                "has_answer": false,
                "object": {
                    "id": 14,
                    "title_ru": "Санаторий им. М.В. Фрунзе",
                    "image": {
                        "id": 274,
                        "object_id": 14,
                        "image": "/storage/object_gallery/thumbs-450x450_c8tvjI6aqPGuiZqeyWBR4JX1C9KtZxB26chEiPet.jpeg.jpeg",
                        "description": null
                    }
                },
                "reservation": {
                    "id": 5,
                    "email": "admin@admin.com",
                    "showcase_room_id": 149,
                    "from_date": "2019-10-10 00:00:00",
                    "to_date": "2019-10-15 00:00:00",
                    "room": {
                        "id": 149,
                        "title": "2-местный люкс угловой 2-4 этаж (корп.2)"
                    },
                    "user": {
                        "id": 12,
                        "fullname": "Админов Админ Админович",
                        "email": "admin@admin.com",
                        "avatar": null
                    }
                },
                "images": [
                    {
                        "id": 3,
                        "feedback_id": 5,
                        "image": "/path-to-image"
                    },
                    {
                        "id": 5,
                        "feedback_id": 5,
                        "image": "/path-to-image"
                    }
                ],
                "sanatorium_answer": {
                    "id": 2,
                    "feedback_id": 5,
                    "comment": "Спасибо!)",
                    "created_at": "2019-10-09 21:29:44",
                    "updated_at": "2019-10-09 21:29:44",
                    "feedback": {
                        "id": 5,
                        "object_id": 33,
                        "object": {
                            "id": 33,
                            "user_id": 15,
                            "user": {
                                "id": 15,
                                "fullname": "Санаторий Санаториевич Ивушка",
                                "avatar": null
                            }
                        }
                    }
                }
            }
        ]
    }     /*
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
            'reservation_id' => 'integer|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        $filter['reservation_id'] = $request->get('reservation_id');

        if (is_null(Auth::user()->object))
            return response()->json(['message' => 'У пользователя нет объекта'], 422);

        $filter['object_id'] = Auth::user()->object->id;

        $feedback = $this->feedbackService->list($page, $rowsPerPage, $searchKey, $sorting, $filter);

        return response()->json($feedback, 200);
    }

    /**
     * @api {get} /api/account/object/feedback/{feedbackId} Получение отзыва
     * @apiVersion 0.1.0
     * @apiName GetFeedback
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
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
        "result_rating": "10",
        "deleted_at": null,
        "created_at": "2019-10-02 11:21:30",
        "updated_at": "2019-10-02 12:23:36",
        "has_answer": false,
        "images": [
            {
                "id": 6,
                "feedback_id": 20,
                "image": "/storage/feedback_images/B0D9qUjOKf9RlJVWwz2YPGhRTysNgdVvh7Y356pj.jpeg"
            },
            {
                "id": 5,
                "feedback_id": 20,
                "image": "/storage/feedback_images/tpTtZMT94K5qN04WzItvahr9UtK2sUYjhSWssBPU.jpeg"
            }
        ],
        "object": {
            "id": 14,
            "title_ru": "Санаторий им. М.В. Фрунзе"
        },
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
     *
     *
     * @param int $feedbackId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(int $feedbackId)
    {
        if ( empty(Auth::user()->object) )
            return response()->json([ 'message' => 'У пользователя нет объекта' ], 422);

        $objectId = Auth::user()->object->id;

        try {
            $feedback = $this->feedbackService->get($feedbackId, $objectId);
        } catch (ApiProblemException $e){

            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        return response()->json($feedback, 200);
    }

    /**
     * @api {get} /api/account/object/feedback/answer Ответ санатория на отзыв
     * @apiVersion 0.1.0
     * @apiName AnswerFeedback
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} feedback_id ID отзыва
     * @apiParam {string} comment Коментарий
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "feedback_id": 20,
        "comment": "ответ санатория",
        "updated_at": "2019-10-02 17:16:49",
        "created_at": "2019-10-02 17:16:49",
        "id": 1
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sanatoriumAnswer(Request $request)
    {
        $valid = Validator($request->all(), [
            'feedback_id' => 'required|integer',
            'comment' => 'required|string',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $feedbackId = $request->get('feedback_id');
        $comment = $request->get('comment');
        $objectId = Auth::user()->object->id;

        try {
            $answer = $this->feedbackService->sanatoriumAnswer($feedbackId, $comment, $objectId);

        } catch (ApiProblemException $e){

            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        return response()->json($answer, 200);
    }
}
