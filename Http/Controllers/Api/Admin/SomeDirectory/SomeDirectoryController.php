<?php

namespace App\Http\Controllers\Api\Admin\SomeDirectory;

use App\Rules\IsArray;
use App\Services\SomeDirectoryService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SomeDirectoryController extends Controller
{
    /**
     * @var SomeDirectoryService
     */
    protected $someDirectoryService;

    /**
     * SomeDirectoryController constructor.
     *
     * @param SomeDirectoryService $someDirectoryService
     */
    public function __construct(SomeDirectoryService $someDirectoryService)
    {
        $this->someDirectoryService = $someDirectoryService;
    }

    /**
     * @api {put} /api/admin/some-directory/{type} Редактирование справочников
     * @apiVersion 0.1.0
     * @apiName EditDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} [description] Описание списка
     * @apiParam {array} items Массив значений ["item","trutem","puten"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "type": "beach_type",
        "description": "test update",
        "created_at": null,
        "updated_at": "2019-09-23 08:11:08",
        "items": [
            {
                "id": 284,
                "type": "beach_type",
                "value": "putens"
            },
            {
                "id": 282,
                "type": "beach_type",
                "value": "items"
            },
            {
                "id": 283,
                "type": "beach_type",
                "value": "trutems"
            }
        ]
    }
     *
     * @param Request $request
     * @param string $type
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function update(Request $request, string $type)
    {
        $valid = Validator($request->all(), [
            'description' => 'nullable|string',
            'items' => ['required', new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $description = $request->get('description');
        $items = $request->get('items');
        if (!is_array($items)) $items = json_decode($items, true);

        $this->someDirectoryService->update($type, $items, $description);
        $directory = $this->someDirectoryService->get($type);

        return response()->json($directory, 200);
    }

    /**
     * @api {get} /api/admin/some-directory/{type} Получение справочников по типу
     * @apiVersion 0.1.0
     * @apiName GetDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "type": "beach_type",
        "description": "test update",
        "created_at": null,
        "updated_at": "2019-09-23 08:11:08",
        "items": [
            {
                "id": 284,
                "type": "beach_type",
                "value": "putens"
            },
            {
                "id": 282,
                "type": "beach_type",
                "value": "items"
            },
            {
                "id": 283,
                "type": "beach_type",
                "value": "trutems"
            }
        ]
    }
     *
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $type)
    {
        $directory = $this->someDirectoryService->get($type, true);

        return response()->json($directory, 200);
    }

    /**
     * @api {post} /api/admin/some-directory Добавление справочников
     * @apiVersion 0.1.0
     * @apiName AddDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} type Тип списка
     * @apiParam {string} description Описание списка
     * @apiParam {array} items Массив значений ["item","trutem","puten"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        "item",
        "trutem",
        "puten"
    ]
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function create(Request $request)
    {
        $valid = Validator($request->all(), [
            'type' => 'required|string',
            'description' => 'required|string',
            'items' => ['required', new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $type = $request->get('type');
        $description = $request->get('description');
        $items = $request->get('items');
        if (!is_array($items)) $items = json_decode($items, true);

        $directory = $this->someDirectoryService->create($type, $description, $items);

        return response()->json($directory, 201);
    }

    /**
     * @api {delete} /api/admin/some-directory/{type} Удаление справочника по типу
     * @apiVersion 0.1.0
     * @apiName DeleteDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
         "message" : "справочник удален"
     }
     *
     * @param string $type
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $type)
    {
        $this->someDirectoryService->delete($type);

        return response()->json(['message' => 'справочник удален'], 200);
    }

    /**
     * @api {get} /api/admin/some-directory Получение справочников
     * @apiVersion 0.1.0
     * @apiName ListDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
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
        "total": 22,
        "items": [
            {
                "id": 1,
                "type": "restroom_equipment",
                "description": "",
                "created_at": null,
                "updated_at": null
            },
            {
                "id": 10,
                "type": "contingent",
                "description": "",
                "created_at": null,
                "updated_at": null
            }
        ]
    }
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listTypes(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $directories = $this->someDirectoryService->listTypes($page, $rowsPerPage, $sorting, $searchKey);

        return response()->json($directories, 200);
    }

    /**
     * @api {put} /api/admin/some-directory/type/{typeId} редактирование справочника
     * @apiVersion 0.1.0
     * @apiName EditTypeDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} type Тип списка
     * @apiParam {string} description Описание списка
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "type": "wifi_places_1",
        "description": "test update",
        "created_at": null,
        "updated_at": "2019-09-23 07:31:45"
    }
     *
     * @param Request $request
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function editType(Request $request, int $typeId)
    {
        $valid = Validator($request->all(), [
           'type' => 'string|max:255',
           'description' => 'string|max:255'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('type', 'description');

        $type = $this->someDirectoryService->editType($typeId, $data);

        return response()->json($type, 200);

    }

    /**
     * @api {get} /api/admin/some-directory/type/{typeId} получение справочника
     * @apiVersion 0.1.0
     * @apiName GetTypeDirectories
     * @apiGroup SomeDirectories
     *
     * @apiHeader {string} Authorization access-token
     *     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "type": "wifi_places_1",
        "description": "test update",
        "created_at": null,
        "updated_at": "2019-09-23 07:31:45"
    }
     *
     * @param int $typeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getType(int $typeId)
    {
        $type = $this->someDirectoryService->getType($typeId);

        return response()->json($type, 200);
    }
}
