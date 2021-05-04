<?php

namespace App\Http\Controllers\Api\Admin\Activity;

use App\Rules\IsArray;
use App\Services\ActivitylogService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ActivityController extends Controller
{
    /**
     * @var ActivitylogService
     */
    protected $activitylogService;

    /**
     * ActivityController constructor.
     *
     * @param ActivitylogService $activitylogService
     */
    public function __construct(ActivitylogService $activitylogService)
    {
        $this->activitylogService = $activitylogService;
    }

    /**
     * @api {get} /api/admin/activity Получение списка активности
     * @apiVersion 0.1.0
     * @apiName listActivities
     * @apiGroup Activities
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска в описании активности
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "modified_at": "desc"}
     *
     * @apiParam {string} [subject_type] фильтровать по типу активности
     * @apiParam {integer} [user_id] ID пользователя, по которому нужно показать активность
     * @apiParam {integer} [role_id] ID роль по которой нужно показать активность
     * @apiParam {datetime} [date_from] фильтровать с даты
     * @apiParam {datetime} [date_to] Филтровать до даты
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 4,
        "items": [
            {
                "id": 21,
                "causer_id": 12,
                "description": "Объект: \"Медицинский центр \"Юность\"\" был обновлен",
                "subject_id": 60,
                "created_at": "2019-09-10 09:18:58",
                "user": {
                    "id": 12,
                    "fullname": "Админов Админ Админович",
                    "email": "admin@admin.com",
                    "role_id": 1,
                    "role": {
                        "id": 1,
                        "name": "Администратор"
                    }
                }
            },
            {
                "id": 19,
                "causer_id": 12,
                "description": "Объект: \"Медицинский центр \"Юность\"\" был обновлен",
                "subject_id": 60,
                "created_at": "2019-09-10 09:17:14",
                "user": {
                    "id": 12,
                    "fullname": "Админов Админ Админович",
                    "email": "admin@admin.com",
                    "role_id": 1,
                    "role": {
                        "id": 1,
                        "name": "Администратор"
                    }
                }
            },
            {
                "id": 18,
                "causer_id": 12,
                "description": "Объект: \"Медицинский центр \"Юность\"\" был обновлен",
                "subject_id": 60,
                "created_at": "2019-09-10 09:17:13",
                "user": {
                    "id": 12,
                    "fullname": "Админов Админ Админович",
                    "email": "admin@admin.com",
                    "role_id": 1,
                    "role": {
                        "id": 1,
                        "name": "Администратор"
                    }
                }
            },
            {
                "id": 17,
                "causer_id": 12,
                "description": "Изображение для объекта \"Медицинский центр \"Юность\"\" было добавлено",
                "subject_id": 710,
                "created_at": "2019-09-10 09:15:45",
                "user": {
                    "id": 12,
                    "fullname": "Админов Админ Админович",
                    "email": "admin@admin.com",
                    "role_id": 1,
                    "role": {
                        "id": 1,
                        "name": "Администратор"
                    }
                }
            }
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function listActivity(Request $request)
    {
        $valid = Validator([
            'page' => 'nullable|integer',
            'rowsPerPage' => 'nullable|integer',
            'user_id' => 'nullable|integer',
            'searchKey' => 'nullable|string',
            'sorting' => [ new IsArray ],
            'subject_type' => 'nullable|string',
            'role_id' => 'nullable|integer',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $params['user_id'] = $request->get('user_id');
        $params['subject_type'] = $request->get('subject_type');
        $params['role_id'] = $request->get('role_id');
        $params['date_from'] = $request->get('date_from');
        $params['date_to'] = $request->get('date_to');

        $activities = $this->activitylogService->listActivity($page, $rowsPerPage, $searchKey, $sorting, $params);

        return response()->json($activities, 200);
    }

    /**
     * @api {get} /api/admin/activity/types Получение типов активности
     * @apiVersion 0.1.0
     * @apiName listActivityTypes
     * @apiGroup Activities
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    [
        {
            "subject_type": "object",
            "description": "Активность по объектам"
        },
        {
            "subject_type": "user",
            "description": "Активность по пользователям"
        },
        {
            "subject_type": "moderation",
            "description": "Активность по модерации"
        },
        {
            "subject_type": "news",
            "description": "Активность по новостям"
        },
        {
            "subject_type": "offer",
            "description": "Активность по спецпредложениям"
        },
        {
            "subject_type": "partner",
            "description": "Активность по партнерам"
        },
        {
            "subject_type": "geography",
            "description": "Активность по географии"
        },
        {
            "subject_type": "seo",
            "description": "Активность по SEO"
        },
        {
            "subject_type": "medical",
            "description": "Активность по медицине"
        },
        {
            "subject_type": "other",
            "description": "Другая активность"
        }
    ]
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listActivityTypes()
    {
        $activityTypes = $this->activitylogService->listActivityTypes();

        return response()->json($activityTypes, 200);
    }

    /**
     * @api {get} /api/admin/activity/{activityId} Получение деталей активности
     * @apiVersion 0.1.0
     * @apiName getActivityDetails
     * @apiGroup Activities
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 19,
        "description": "Объект: \"Медицинский центр \"Юность\"\" был обновлен",
        "changes": [
            {
                "field": "alias",
                "from": "junost",
                "to": "junos"
            }
        ],
        "created_at": "2019-09-10 09:17:14",
        "user": {
            "id": 12,
            "fullname": "Админов Админ Админович",
            "email": "admin@admin.com",
            "role_id": 1,
            "role": {
                "id": 1,
                "name": "Администратор"
            }
        }
    }
     *
     * @param int $activityId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getActivityDetails(int $activityId)
    {
        $activity = $this->activitylogService->getActivityDetails($activityId);

        return response()->json($activity, 200);
    }
}
