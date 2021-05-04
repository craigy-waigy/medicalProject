<?php

namespace App\Http\Controllers\Api\Admin\User;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\UserService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    /**
     * @var UserService
     */
    protected $userService;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->userService = new UserService();
    }

    /**
     * Display a listing of the resource.
     *
     * @api {get} /api/admin/user Получение и поиск пользователей
     * @apiVersion 0.1.0
     * @apiName SearchUser
     * @apiGroup AdminUsers
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
        {
            "page": 1,
            "rowsPerPage": 10,
            "total": 12,
            "items": [
                {
                    "id": 1,
                    "name": "name",
                    "email": "name@mail.com",
                    "email_verified_at": null,
                    "father_name": "fathername",
                    "last_name": "lastname",
                    "avatar_url": null,
                    "role_id": 2,
                    "email_confirmed": false,
                    "phone": "12345678",
                    "created_at": "2018-11-27 12:34:53",
                    "updated_at": "2018-11-27 12:34:53",
                    "is_deleted": false,
                    "deleted_at": null,
                    "country_id": 1,
                    "region_id": 1,
                    "city_id": 1,
                    "other_email": null,
                    "role": {
                        "id": 2,
                        "name": "Пациент",
                        "description": "Простой пользователь",
                        "slug": "patient",
                        "permissions": "[]",
                        "created_at": null,
                        "updated_at": null
                    },
                    "country": {
                        "id": 1,
                        "name_ru": "Россия",
                        "name_en": "Russia",
                        "created_at": null,
                        "updated_at": null
                    },
                    "region": {
                        "id": 1,
                        "name_ru": "Краснодарский край",
                        "name_en": "Krasnodar region",
                        "country_id": 1,
                        "created_at": null,
                        "updated_at": null
                    },
                    "city": {
                        "id": 1,
                        "name_ru": "Сочи",
                        "name_en": "Sochi",
                        "region_id": 1,
                        "country_id": 1,
                        "created_at": null,
                        "updated_at": null
                    }
                }
            ]
        }
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $valid = Validator($request->all(), [
           'page' => 'integer|nullable',
           'rowsPerPage' => 'integer|nullable',
           'role_id' => 'integer|nullable',
           'searchKey' => 'string|nullable',
           'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $searchBy = $request->get('searchBy') ?? null;
        $searchKey = $request->get('searchKey') ?? null;
        $params['role_id'] = $request->get('role_id');

        $users = $this->userService->getAll($page, $rowsPerPage, $sorting, $searchBy, $searchKey, $params);

        return response()->json($users, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @api {post} /api/admin/user Создание нового пользователя
     * @apiVersion 0.1.0
     * @apiName CreateUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name Имя пользователя
     * @apiParam {string} email почта
     * @apiParam {string} password пароль
     * @apiParam {string} [last_name] фамилие
     * @apiParam {string} [father_name] отчество
     * @apiParam {string} [phone] телефон
     * @apiParam {string} [other_email] дополнительный email
     * @apiParam {integer} [city_id] ID города
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [country_id] ID страны
     * @apiParam {integer} [role_id] ID роли. Если не передается, назначается по умолчанию роль пользователя.
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        {
          "userId": 12
        }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $valid = validator($request->only('email', 'name', 'password', 'father_name', 'last_name',
            'phone', 'other_mail', 'city_id', 'region_id', 'country_id', 'role_id'), [

            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'other_mail' => 'string|email|max:255|nullable',
            'password' => 'required|string|min:6',
        ],[
            'email.unique' => "Указанный email уже существует",
            'password.min' => "Минимальная длинна пароля 6 символов",
        ]);

        if ($valid->fails()) {

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('email', 'name', 'password', 'father_name', 'last_name',
             'phone', 'other_email', 'city_id', 'region_id', 'country_id', 'role_id');

        return $this->userService->create($data);
    }

    /**
     * Display the specified resource.
     *
     * @api {get} /api/admin/user/{userId} Получение пользователя
     * @apiVersion 0.1.0
     * @apiName GetUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
    {
        "id": 16,
        "name": "name3",
        "email": "name3@mail.com",
        "email_verified_at": null,
        "father_name": "fathername",
        "last_name": "lastname",
        "avatar_url": null,
        "role_id": 1,
        "email_confirmed": false,
        "phone": "12345678",
        "created_at": "2018-11-28 13:38:33",
        "updated_at": "2018-11-28 13:38:33",
        "is_deleted": false,
        "deleted_at": null,
        "country_id": 1,
        "region_id": 1,
        "city_id": 1,
        "other_email": "name@mail.com",
        "role": {
            "id": 1,
            "name": "Администратор",
            "description": "Администратор с высокими привилегиями",
            "slug": "admin",
            "permissions": "[]",
            "created_at": null,
            "updated_at": null
        },
        "object": {
            "id": 33,
            "user_id": 5,
            "title_ru": "Санаторий Буран",
            "city_id": 7,
            "region_id": 6977,
            "country_id": 88,
            "city": {
                "id": 7,
                "name_ru": "Барнаул"
            },
            "region": {
                "id": 6977,
                "name_ru": "Алтайский Край"
            },
            "country": {
                "id": 88,
                "name_ru": "Российская Федерация"
        }
        "country": {
            "id": 1,
            "name_ru": "Россия",
            "name_en": "Russia",
            "created_at": null,
            "updated_at": null
        },
        "region": {
            "id": 1,
            "name_ru": "Краснодарский край",
            "name_en": "Krasnodar region",
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        },
        "city": {
            "id": 1,
            "name_ru": "Сочи",
            "name_en": "Sochi",
            "region_id": 1,
            "country_id": 1,
            "created_at": null,
            "updated_at": null
        }
        "partner": {
            "id": 1,
            "user_id": 5,
            "partner_type_id": 1,
            "organisation_short_name_ru": "ShortNameRu1",
            "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
            "type": {
                "id": 1,
                "name_ru": "СМИ",
                "image": null
            }
        }
    }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function show($id)
    {
        $user = $this->userService->getUser($id);

        return response()->json($user, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @api {put} /api/admin/user/{userId} Обновление инфо пользователя
     * @apiVersion 0.1.0
     * @apiName UpdateUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [name] Имя пользователя
     * @apiParam {string} [email] почта
     * @apiParam {string} [password] пароль
     * @apiParam {string} [last_name] фамилие
     * @apiParam {string} [father_name] отчество
     * @apiParam {string} [phone] телефон
     * @apiParam {string} [other_email] дополнительный email
     * @apiParam {integer} [city_id] ID города
     * @apiParam {integer} [region_id] ID региона
     * @apiParam {integer} [country_id] ID страны
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
    {
        "user": [
            "Пользователь успешно обновлен"
        ]
    }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $valid = validator($request->all(), [
            'name' => 'string|max:191',
            'other_email' => 'string|max:191|nullable',
            'email' => 'string|email|max:191|unique:users,email,' . $id,
            'father_name' => 'string|max:255|nullable',
            'last_name' => 'string|max:255|nullable',
            'phone' => 'string|max:15|nullable',
            'country_id' => 'integer|nullable',
            'region_id' => 'integer|nullable',
            'city_id' => 'integer|nullable',
            'password' => 'string|min:6',
        ],[
            'email.unique' => "Указанный email уже существует",
            'password.min' => "Минимальная длинна пароля 6 символов",
        ]);
        if ($valid->fails()) return response(['errors' => $valid->errors()], 400);

        $data = $request->only('email', 'name', 'password', 'father_name', 'last_name',
            'phone', 'other_email', 'city_id', 'region_id', 'country_id');

        $this->userService->updateUser($data, $id);

        return response()->json(['user' => [
            'Пользователь успешно обновлен'
        ] ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     *  @api {delete} /api/admin/user/{userId} Удаление пользователя
     * @apiVersion 0.1.0
     * @apiName DeleteUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "user": [
            "Пользователь успешно удален"
        ]
    }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->userService->deleteUser($id);
        return response()->json(['user' => [
            'Пользователь успешно удален'
        ] ], 200);
    }

    /**
     * @api {put} /api/admin/user/{userId}/role Установление роли пользователю
     * @apiVersion 0.1.0
     * @apiName SetRoleUser
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} role_id ID роли. Для владельца объекта = 3
     * @apiParam {integer} object_id  ID объекта
     * @apiParam {integer} partner_id ID партнера.
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        {
            "user": [
                "Пользователю успешно назначена роль"
            ]
        }
     *
     * @param Request $request
     * @param int $userId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function setRole(Request $request, int $userId)
    {
        $valid = validator($request->all(),[
            'role_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);
        $rules = $this->userService->getRules($request->get('role_id'));

        $valid = validator($request->all(), $rules, [
            'role_id.required' => "Необходимо предоставить роль пользователя",
            'role_id.integer' => "ID роли пользователя должна быть целочисленной",
            'object_id.integer' => "ID объкета должен быть целочисленным",
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $data = $request->all();
        $this->userService->setRole($userId, $data);

        return response()->json(['user' => [
            'Пользователю успешно назначена роль'
        ] ], 200);
    }

    /**
     * @api {delete} /api/admin/user/avatar/{userId} Удаление аватара пользователя
     * @apiVersion 0.1.0
     * @apiName DeleteAvatar
     * @apiGroup AdminUsers
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Аватар удален"
    }
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteAvatar(int $userId)
    {
        $this->userService->deleteAvatar($userId);

        return response()->json(['message' => 'Аватар удален'], 200);
    }
}
