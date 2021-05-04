<?php

namespace App\Http\Controllers\Api\Account\Patient;

use App\Rules\IsArray;
use App\Services\FriendshipService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class FriendshipController extends Controller
{
    /**
     * @var FriendshipService
     */
    protected $friendshipService;

    /**
     * FriendshipController constructor.
     */
    public function __construct()
    {
        $this->friendshipService = new FriendshipService();
    }

    /**
     * @api {get} /api/account/friendship Получение списка друзей
     * @apiVersion 0.1.0
     * @apiName ListFriends
     * @apiGroup Friendship
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"last_message":"desc"}
     * @apiParam {boolean}  [accepted] Если отправлено TRUE, то в списке только подтвержденные контакты и наоборот, FALSE - неподтвержденные
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 39,
                "name": "Друган",
                "avatar": null,
                "fullname": " Друган ",
                "last_name": null,
                "father_name": null,
                "email": "frend-dude@mail.ru",
                "accepted": true,
                "friendship_id": 7,
                "messages_count": "3",
                "new_messages_count": "0",
                "last_message": "2019-07-26 09:42:42",
                "incoming": false
            },
            {
                "id": 38,
                "name": "friend",
                "avatar": null,
                "fullname": " friend ",
                "last_name": null,
                "father_name": null,
                "email": "frien@mail.ru",
                "accepted": false,
                "friendship_id": 6,
                "messages_count": "1",
                "new_messages_count": "0",
                "last_message": "2019-07-26 07:45:01",
                "incoming": false
            },
            {
                "id": 37,
                "name": "myFriend",
                "avatar": null,
                "fullname": " myFriend ",
                "last_name": null,
                "father_name": null,
                "email": "moderatedemail@mail.ru",
                "accepted": true,
                "friendship_id": 5,
                "messages_count": "1",
                "new_messages_count": "0",
                "last_message": "2019-07-26 07:41:53",
                "incoming": false
            }
        ]
    }
     /*
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getFriendship(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
            'accepted' => 'nullable|boolean'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $userId = Auth::user()->id;
        $params['accepted'] = $request->get('accepted');

        $friends = $this->friendshipService->getFriendship($userId, $page, $rowsPerPage, $sorting, $searchKey, $params);

        return response()->json($friends, 200);
    }

    /**
     * @api {post} /api/account/friendship Отправка запроса дружбы
     * @apiVersion 0.1.0
     * @apiName AddFriends
     * @apiGroup Friendship
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} name Имя
     * @apiParam {string} email Электронная почта
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Запрос на дружбу отправлен"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function addFriendship(Request $request)
    {
        $valid = Validator($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $name = $request->get('name');
        $email = $request->get('email');
        $initiatorUserId = Auth::user()->id;

        $this->friendshipService->addFriendship($initiatorUserId, $name, $email);

        return response()->json(['message' => 'Запрос на дружбу отправлен'], 201);
    }

    /**
     * @api {post} /api/account/friendship/accept Подтверждение дружбы через почту
     * @apiVersion 0.1.0
     * @apiName AcceptFriendsFromEmail
     * @apiGroup Friendship
     *
     * @apiDescription <p style="color: red;">На почту отправляется ссылка: HOST/friendship/accept/[token]</p>
     *
     * @apiParam {string} token Токен отправленый ссылкой на почту
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "access_token": "access-token,
        "token_type": "Bearer",
        "expires_at": 1595600714
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function acceptFriendship(Request $request)
    {
        $valid = Validator($request->all(), [
            'token' => 'required|string',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $token = $request->get('token');

        $access = $this->friendshipService->acceptFriendship($token);

        return response()->json($access, 200);
    }

    /**
     * @api {delete} /api/account/friendship Отмена дружбы
     * @apiVersion 0.1.0
     * @apiName DeleteFriends
     * @apiGroup Friendship
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} friendship_id ID дружбы
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Дружба отменена"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function removeFriendship(Request $request)
    {
        $valid = Validator($request->all(), [
            'friendship_id' => 'required|integer',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $friendshipId = $request->get('friendship_id');
        $initiatorUserId = Auth::user()->id;

        $this->friendshipService->removeFriendship($initiatorUserId, $friendshipId);

        return response()->json(['message' => 'Дружба отменена'], 200);
    }

    /**
     * @api {put} /api/account/friendship/accept/{friendship_id} Подтверждение дружды с ЛК
     * @apiVersion 0.1.0
     * @apiName AcceptFriends
     * @apiGroup Friendship
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Дружба принята"
    }
     * @param int $friendshipId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function acceptFriendshipFromAccount(int $friendshipId)
    {
        $recipientUser = Auth::user()->id;
        $this->friendshipService->acceptFriendship(null, $friendshipId, $recipientUser);

        return response()->json(['message' => 'Дружба принята'], 200);
    }
}
