<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\SanatoriumDoctorService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SanatoriumDoctorController extends Controller
{
    /**
     * @var SanatoriumDoctorService
     */
    protected $sanatoriumDoctorService;

    /**
     * SanatoriumDoctorController constructor.
     * @param SanatoriumDoctorService $sanatoriumDoctorService
     */
    public function __construct(SanatoriumDoctorService $sanatoriumDoctorService)
    {
        $this->sanatoriumDoctorService = $sanatoriumDoctorService;
    }

    /**
     * @api {post} /api/account/object/doctor Добавление врача
     * @apiVersion 0.1.0
     * @apiName AddDoctor
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} email email
     * @apiParam {String} name Имя
     * @apiParam {String} last_name Фамилие
     * @apiParam {String} father_name Отчество
     * @apiParam {String} [other_email] Дополнительный email
     * @apiParam {array} [specializations_ru] Специализация на русском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [specializations_en] Специализация на английском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [languages] Языки на котором говорит врач (["ru","en","kz","az"])
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 45,
        "email": "email-test@mail.com",
        "name": "testustaton",
        "father_name": "father_name",
        "last_name": "last_name",
        "fullname": "last_name testustaton father_name",
        "avatar": null,
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "specializations_ru": [
                "terapevt",
                "okulist"
            ],
            "specializations_en": [
                "terapevt",
                "okulist",
                "herolog"
            ],
            "languages": [
                "ru",
                "en"
            ],
            "online": false,
            "deleted_at": null,
            "created_at": "2019-08-15 05:22:49",
            "updated_at": "2019-08-15 05:41:13"
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function addDoctor(Request $request)
    {
        $valid = Validator($request->all(), [
            'email' => 'required|email',
            'other_email' => 'nullable|email',
            'name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'specializations_ru' => [ new IsArray ],
            'specializations_en' => [ new IsArray ],
            'languages' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors());

        $object = Auth::user()->object;
        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $data = $request->only(
            'email',
            'other_email',
            'name',
            'father_name',
            'last_name',
            'specializations_ru',
            'specializations_en',
            'languages'
            );
        $sanatoriumDoctor = $this->sanatoriumDoctorService->addDoctor($object->id, $data);

        return response()->json($sanatoriumDoctor, 201);
    }

    /**
     * @api {put} /api/account/object/doctor/{user_id} Редактирование врача
     * @apiVersion 0.1.0
     * @apiName EditDoctor
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} [name] Имя
     * @apiParam {String} [last_name] Фамилие
     * @apiParam {String} [father_name] Отчество
     * @apiParam {String} [other_email] Дополнительный email
     * @apiParam {array} [specializations_ru] Специализация на русском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [specializations_en] Специализация на английском (["терапевт", "окулист", "парацитамол"])
     * @apiParam {array} [languages] Языки на котором говорит врач (["ru","en","kz","az","hz"])
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 45,
        "email": "email-test@mail.com",
        "name": "testustaton",
        "father_name": "father_name",
        "last_name": "last_name",
        "fullname": "last_name testustaton father_name",
        "avatar": null,
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "specializations_ru": [
                "terapevt",
                "okulist"
            ],
            "specializations_en": [
                "terapevt",
                "okulist",
                "herolog"
            ],
            "languages": [
                "ru",
                "en"
            ],
            "online": false,
            "deleted_at": null,
            "created_at": "2019-08-15 05:22:49",
            "updated_at": "2019-08-15 05:41:13"
        }
    }
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function editDoctor(Request $request, int $userId)
    {
        $valid = Validator($request->all(), [
            'other_email' => 'nullable|email',
            'name' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'specializations_ru' => [ new IsArray ],
            'specializations_en' => [ new IsArray ],
            'languages' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(),400);

        $object = Auth::user()->object;
        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $data = $request->only(
            'other_email',
            'name',
            'father_name',
            'last_name',
            'specializations_ru',
            'specializations_en',
            'languages'
        );
        $sanatoriumDoctor = $this->sanatoriumDoctorService->editDoctor($userId, $object->id, $data);

        return response()->json($sanatoriumDoctor, 200);
    }

    /**
     * @api {delete} /api/account/object/doctor/{user_id} Удаление врача
     * @apiVersion 0.1.0
     * @apiName DeleteDoctor
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     { "message": "Врач удален" }
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function removeDoctor(int $userId)
    {
        $object = Auth::user()->object;
        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $this->sanatoriumDoctorService->removeDoctor($userId, $object->id);

        return response()->json(['message' => 'Врач удален'], 200);
    }

    /**
     * @api {get} /api/account/object/doctor Список врачей
     * @apiVersion 0.1.0
     * @apiName ListDoctors
     * @apiGroup AccountObject
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
        "total": 1,
        "items": [
            {
                "id": 45,
                "email": "email-test@mail.com",
                "name": "testustaton",
                "father_name": "father_name",
                "last_name": "last_name",
                "fullname": "last_name testustaton father_name",
                "avatar": null,
                "sanatorium_doctor": {
                    "id": 1,
                    "user_id": 45,
                    "object_id": 33,
                    "specializations_ru": [
                        "terapevt",
                        "okulist"
                    ],
                    "specializations_en": [
                        "terapevt",
                        "okulist",
                        "herolog"
                    ],
                    "languages": [
                        "ru",
                        "en"
                    ],
                    "online": false,
                    "deleted_at": null,
                    "created_at": "2019-08-15 05:22:49",
                    "updated_at": "2019-08-15 05:41:13"
                }
            }
        ]
    }
     /*
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function listDoctors(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'nullable|integer',
            'rowsPerPage' => 'nullable|integer',
            'searchKey' => 'nullable|string',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $object = Auth::user()->object;
        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $sanatoriumDoctors = $this->sanatoriumDoctorService->listDoctors($object->id, $page, $rowsPerPage, $sorting, $searchKey, null);

        return response()->json($sanatoriumDoctors, 200);
    }

    /**
     * @api {get} /api/account/object/doctor/{user_id} Получение врача
     * @apiVersion 0.1.0
     * @apiName GetDoctor
     * @apiGroup AccountObject
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "id": 45,
        "email": "email-test@mail.com",
        "name": "testustaton",
        "father_name": "father_name",
        "last_name": "last_name",
        "fullname": "last_name testustaton father_name",
        "avatar": null,
        "sanatorium_doctor": {
            "id": 1,
            "user_id": 45,
            "object_id": 33,
            "specializations_ru": [
                "terapevt",
                "okulist"
            ],
            "specializations_en": [
                "terapevt",
                "okulist",
                "herolog"
            ],
            "languages": [
                "ru",
                "en"
            ],
            "online": false,
            "deleted_at": null,
            "created_at": "2019-08-15 05:22:49",
            "updated_at": "2019-08-15 05:41:13"
        },
        "object": {
            "id": 33,
            "title_ru": "Санаторий Буран",
            "alias": "buran"
        }
    }
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getDoctor(int $userId)
    {
        $object = Auth::user()->object;
        if (is_null($object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $sanatoriumDoctor = $this->sanatoriumDoctorService->getDoctor($userId, $object->id);

        return response()->json($sanatoriumDoctor, 200);
    }
}
