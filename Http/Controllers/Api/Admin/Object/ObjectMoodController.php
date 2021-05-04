<?php

namespace App\Http\Controllers\Api\Admin\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\ObjectMoodService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjectMoodController extends Controller
{
    protected $objectMoodService;

    public function __construct()
    {
        $this->objectMoodService = new ObjectMoodService();
    }

    /**
     * @api {post} /api/admin/object-mood Добавление mood-тега к объекту
     * @apiVersion 0.1.0
     * @apiName AddObjectMood
     * @apiGroup AdminObjectMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id ID объекта
     * @apiParam {integer} mood_id ID объекта

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objectMood": [
            "Mood-тег добавлен"
        ]
    }

     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function add(Request $request)
    {
        $valid = validator($request->only('object_id', 'mood_id'), [
            'object_id' => 'required|integer',
            'mood_id' => 'required|integer',
        ], [
            'object_id.required' => 'ID объекта отсуствует',
            'object_id.integer' =>  'ID объекта должно быть целочисленным',
            'mood_id.required' => 'ID объекта отсуствует',
            'mood_id.integer' =>  'ID mood-тега должно быть целочисленным',
        ]);

        if ($valid->fails()) return response($valid->errors(),400);

        $success = $this->objectMoodService->add($request);

        if ($success){

            return response()->json(['objectMood' => [
                'Mood-тег добавлен'
            ] ], 200);
        } else {

            return response()->json(['objectMood' => [
                'Mood-тег не добавлен'
            ] ], 422);
        }
    }

    /**
     * @api {delete} /api/admin/object-mood Удаление mood-тега у объекта
     * @apiVersion 0.1.0
     * @apiName DeleteMood
     * @apiGroup AdminObjectMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id ID объекта
     * @apiParam {integer} mood_id ID объекта
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objectMood": [
            "Mood-тег удален"
        ]
    }
     *
     *
     * @param Request $request
     * @throws ApiProblemException
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request)
    {
        $success = $this->objectMoodService->delete($request);

        if ($success){

            return response()->json(['objectMood' => [
                'Mood-тег удален'
            ] ], 200);
        } else {

            return response()->json(['objectMood' => [
                'Mood-тег не найден'
            ] ], 404);
        }

    }

    /**
     * @api {get} /api/admin/object-mood/moods/{$objectId} Получение mood-тегов по id объекта
     * @apiVersion 0.1.0
     * @apiName GetMoodsByObjectId
     * @apiGroup AdminObjectMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {integer} objectId id объекта
     * @apiParam {json}  [sorting] Сортировка {"id": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 50,
        "total": 2,
        "items": [
            {
                "id": 1,
                "name_ru": "Ок",
                "name_en": "ok",
                "alias": "ok",
                "image": "/storage/moods/DoX12ULbleuF8xR3X9OEDytstWwk4BzKUoNKHUwN.jpeg",
                "crop_image": "/storage/moods_crop/DoX12ULbleuF8xR3X9OEDytstWwk4BzKUoNKHUwN.jpeg",
                "created_at": "2020-04-30 16:21:47",
                "updated_at": "2020-04-30 16:21:47"
            },
            {
                "id": 2,
                "name_ru": "Мать и дитя",
                "name_en": "mother and child",
                "alias": "mother-and-child",
                "image": "/storage/moods/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg",
                "crop_image": "/storage/moods_crop/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg",
                "created_at": "2020-04-30 16:22:16",
                "updated_at": "2020-04-30 16:22:16"
            }
        ]
    }
     *
     * @param Request|null $request
     * @param int $objectId
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @param Request $request
     * @throws ApiProblemException
     */
    public function getMoods(Request $request, int $objectId)
    {
        $valid = Validator($request->only('page', 'rowsPerPage'),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 50;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->objectMoodService->getMoods($page, $rowsPerPage, $objectId, $sorting);
    }

}
