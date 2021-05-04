<?php

namespace App\Http\Controllers\Api\Admin\Feedback;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @api {put} /api/admin/feedback/{feedbackId} Редактирование отзыва
     * @apiVersion 0.1.0
     * @apiName EditFeedback
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [quality_impressions] Оценка впечаления (от 1 до 10)
     * @apiParam {integer} [quality_healing] Оценка лечения (от 1 до 10)
     * @apiParam {integer} [quality_rooms] Оценка номеров (от 1 до 10)
     * @apiParam {integer} [quality_cleaning_rooms] Оценка чистоты и уборки номеров (от 1 до 10)
     * @apiParam {integer} [quality_nutrition] Оценка питания (от 1 до 5)
     * @apiParam {integer} [quality_entertainment] Оценка развлечений (от 1 до 10)
     * @apiParam {integer} [quality_service] уровеь сервиса (от 1 до 10)
     * @apiParam {string} [liked] что понравилось (255 символов)
     * @apiParam {string} [not_liked] что не понравилось (255 символов)
     * @apiParam {string} [comment] комментарий (500 символов)

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
        "quality_service": 5,
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
            "updated_at": "2019-10-02 17:16:49"
        }
    }
     *
     * @param Request $request
     * @param int $feedbackId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $feedbackId)
    {
        $valid = Validator($request->all(),[
            'quality_impressions' =>    'required|integer|min:1|max:10',
            'quality_healing' =>        'required|integer|min:1|max:10',
            'quality_rooms' =>          'required|integer|min:1|max:10',
            'quality_cleaning_rooms' => 'required|integer|min:1|max:10',
            'quality_nutrition' =>      'required|integer|min:1|max:10',
            'quality_entertainment' =>  'required|integer|min:1|max:10',
            'quality_service' =>        'required|integer|min:1|max:10',
            'liked' =>                  'nullable|string|max:255',
            'not_liked' =>              'nullable|string|max:255',
            'comment' =>                'nullable|string|max:500',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only(
            'quality_impressions',
            'quality_healing',
            'quality_rooms',
            'quality_cleaning_rooms',
            'quality_nutrition',
            'quality_entertainment',
            'quality_service',
            'liked',
            'not_liked',
            'comment'
        );

        try{
            $feedback = $this->feedbackService->update($feedbackId, $data);
        } catch (ApiProblemException $e){

            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        return response()->json($feedback, 200);
    }

    /**
     * @api {get} /api/admin/feedback/{feedbackId} Получение отзыва
     * @apiVersion 0.1.0
     * @apiName GetFeedback
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token

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
        "quality_service": 5,
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
     /*
     *
     * @param int $feedbackId
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(int $feedbackId)
    {
        try {
            $feedback = $this->feedbackService->get($feedbackId);
        } catch (ApiProblemException $e){

            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        return response()->json($feedback, 200);
    }


    /**
     * @api {get} /api/admin/feedback Список отзывов
     * @apiVersion 0.1.0
     * @apiName ListFeedback
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     * @apiParam {integer}  [reservation_id] Номер брони
     * @apiParam {integer}  [object_id] ID объекта
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
                "quality_service": 5,
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
                "quality_service": 5,
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
                    "updated_at": "2019-10-09 21:29:44"
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
            'searchKey' => 'string|nullable',
            'reservation_id' => 'integer|nullable',
            'object_id' => 'integer|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        $filter['reservation_id'] = $request->get('reservation_id');
        $filter['object_id'] = $request->get('object_id');

        $feedback = $this->feedbackService->list($page, $rowsPerPage, $searchKey, $sorting, $filter);

        return response()->json($feedback, 200);
    }

    /**
     * @api  {post} /api/admin/feedback/request Ручная отправка приглашения оставить отзыв
     * @apiVersion 0.1.0
     * @apiName RequestFeedback
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  [reservation_id] Номер брони
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message" : "приглашение отправлено"
     }
    * @apiErrorExample {json} Ответ сервера в случае ошибки:
    HTTP/1.1 422 Unprocessable Entity
    {
        "message" : "Невозможно отправить приглашение пройти отзыв у не подтвержденной брони"
    }
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manualFeedbackRequest( Request $request )
    {
        $valid = Validator($request->all(), [
            'reservation_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $reservationId = $request->get('reservation_id');

        try {
            $this->feedbackService->manualFeedbackRequest($reservationId);
        } catch (ApiProblemException $e){
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }

        return response()->json(['message' => 'приглашение отправлено'], 200);
    }

    /**
     * @api  {get} /api/admin/feedback/object список объектов с рейтингами
     * @apiVersion 0.1.0
     * @apiName ObjectsFeedbackRatings
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  [object_id] ID объекта
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
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
                "id": 14,
                "title_ru": "Санаторий им. М.В. Фрунзе",
                "heating_rating": "9.8765432098765",
                "full_rating": "4.9",
                "feedback_count": 2,
                "quality_impressions": "4.0",
                "quality_healing": "4.0",
                "quality_rooms": "4.0",
                "quality_cleaning_rooms": "4.0",
                "quality_nutrition": "4.0",
                "quality_entertainment": "4.0",
                "quality_service": "4.0",
                "feedback_at": "2019-10-10 10:41:38"
            }
        ]
    }
     /*
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listObjectFeedback(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'reservation_id' => 'integer|nullable',
            'object_id' => 'integer|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        $filter['reservation_id'] = $request->get('reservation_id');
        $filter['object_id'] = $request->get('object_id');

        $feedback = $this->feedbackService->listObjectFeedback($page, $rowsPerPage, $searchKey, $sorting, $filter);

        return response()->json($feedback, 200);
    }

    /**
     * @api  {delete} /api/admin/feedback/image/{imageId} удаление изображения отзыва
     * @apiVersion 0.1.0
     * @apiName DeleteImageFeedback
     * @apiGroup AdminFeedback
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     { "message": "Изображение удалено" }
     *
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $imageId)
    {
        try {
            $this->feedbackService->deleteImage($imageId);
        } catch (ApiProblemException $exception){
            return response()->json([ 'message' => $exception->getMessage() ], $exception->getCode());
        }

        return response()->json([ 'message' => 'Изображение удалено' ], 200);
    }
}
