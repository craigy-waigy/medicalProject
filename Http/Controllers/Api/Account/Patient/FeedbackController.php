<?php

namespace App\Http\Controllers\Api\Account\Patient;

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
     * @api {get} /api/{locale}/account/reservation/feedback Получение отзыва ( local = ru, en )
     * @apiVersion 0.1.0
     * @apiName GetFeedback
     * @apiGroup AccountPatient
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  reservation_id Номер бронирования
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 5,
        "reservation_id": 5,
        "object_id": 33,
        "quality_impressions": 6,
        "quality_healing": 6,
        "quality_rooms": 6,
        "quality_cleaning_rooms": 6,
        "quality_nutrition": 6,
        "quality_entertainment": 6,
        "liked": null,
        "not_liked": null,
        "comment": null,
        "result_rating": "8",
        "deleted_at": null,
        "created_at": "2019-10-10 10:41:38",
        "updated_at": "2019-10-09 10:41:38",
        "has_answer": false,
        "quality_service": 1,
        "reservation": {
            "id": 5,
            "email": "admin@admin.com",
            "showcase_room_id": 149,
            "from_date": "2019-10-10 00:00:00",
            "to_date": "2019-10-15 00:00:00",
            "room": {
                "id": 149,
                "title": ""
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
     /*
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function list(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'publication_type_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'reservation_id' => 'required|integer',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        $filter['reservation_id'] = $request->get('reservation_id');
        $filter['user_id'] = Auth::user()->id;

        $feedback = $this->feedbackService->list($page, $rowsPerPage, $searchKey, $sorting, $filter, $locale);

        if ($feedback['total'] == 0)
            return response()->json(['message' => 'отзывы не найдены'], 404);

        $feedback =  $feedback['items'][0];

        return response()->json($feedback, 200);
    }
}
