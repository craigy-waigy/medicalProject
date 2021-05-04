<?php

namespace App\Http\Controllers\Api\Account\Patient;

use App\Exceptions\ApiProblemException;
use App\Services\PatientChatService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PatientChatController extends Controller
{
    /**
     * @var PatientChatService
     */
    protected $patientChatService;

    /**
     * PatientChatController constructor.
     */
    public function __construct()
    {
        $this->patientChatService = new PatientChatService();
    }

    /**
     * @api {get} /api/account/friendship/chat/{friendship_id} Получение  сообщений чата
     * @apiVersion 0.1.0
     * @apiName GetChat
     * @apiGroup PatientChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
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
                "id": 1,
                "friendship_id": 7,
                "user_id": 5,
                "message": "test message",
                "is_read": false,
                "created_at": "2019-07-26 07:36:49",
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 2,
                "friendship_id": 7,
                "user_id": 5,
                "message": "test message 2",
                "is_read": false,
                "created_at": "2019-07-26 07:41:53",
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 3,
                "friendship_id": 7,
                "user_id": 5,
                "message": "test message 2",
                "is_read": false,
                "created_at": "2019-07-26 07:45:01",
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 4,
                "friendship_id": 7,
                "user_id": 5,
                "message": "test message 2",
                "is_read": true,
                "created_at": "2019-07-26 07:45:45",
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            }
        ],
        "friend": {
            "id": 38,
            "name": "friend",
            "avatar": null,
            "fullname": " friend ",
            "last_name": null,
            "father_name": null,
            "email": "frien@mail.ru"
        }
    }

     *
     * @param Request $request
     * @param int $friendshipId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getChat(Request $request, int $friendshipId)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'publication_type_id' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $userId = Auth::user()->id;

        $messages = $this->patientChatService->getChat($page, $rowsPerPage, $friendshipId, $userId, $searchKey);

        return response()->json($messages, 200);
    }

    /**
     * @api {post} /api/account/friendship/chat отправка сообщения
     * @apiVersion 0.1.0
     * @apiName SendMessage
     * @apiGroup PatientChat
     *
     * @apiDescription
     * При успешном запросе эмитится событие в websocket: <b>FriendshipNewMessage</b>  <a href="#api-Websockets-FriendshipChat">doc>></a>
     *
     * @apiHeader {string} Authorization access-token
     * @apiHeader {string} X-Socket-Id <code>Echo.socketId()</code>
     *
     * @apiParam {integer} friendship_id ID "Дружбы"
     * @apiParam {string} message Сообщение
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "friendship_id": 7,
        "user_id": 5,
        "message": "test message 2",
        "is_read": false,
        "created_at": "2019-07-26 09:42:42",
        "user": {
            "id": 5,
            "fullname": "lastname name fathername",
            "avatar": null
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function sendMessage(Request $request)
    {
        $valid = Validator($request->all(), [
            'friendship_id' => 'required|integer',
            'message' => 'required|string'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $friendshipId = $request->get('friendship_id');
        $message = $request->get('message');
        $userId = Auth::user()->id;

        $message = $this->patientChatService->sendMessage($message, $friendshipId, $userId);

        return response()->json($message, 201);
    }

    /**
     * @api {put} /api/account/friendship/chat Пометка сообщения как прочитанное
     * @apiVersion 0.1.0
     * @apiName ReadMessage
     * @apiGroup PatientChat
     *
     * @apiDescription
     * При успешном запросе эмитится событие в websocket: <b>FriendshipReadMessage</b>  <a href="#api-Websockets-FriendshipChat">doc>></a>
     *
     * @apiHeader {string} Authorization access-token
     * @apiHeader {string} X-Socket-Id <code>Echo.socketId()</code>
     *
     * @apiParam {integer} friendship_id ID "Дружбы"
     * @apiParam {integer} message_id ID Сообщения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 5,
        "friendship_id": 7,
        "user_id": 5,
        "message": "test message 2",
        "is_read": true,
        "created_at": "2019-07-26 09:42:42",
        "user": {
            "id": 5,
            "fullname": "lastname name fathername",
            "avatar": null
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function readMessage(Request $request)
    {
        $valid = Validator($request->all(), [
            'friendship_id' => 'required|integer',
            'message_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $friendshipId = $request->get('friendship_id');
        $messageId = $request->get('message_id');
        $userId = Auth::user()->id;

        $message = $this->patientChatService->readMessage($messageId, $friendshipId, $userId);

        return response()->json($message, 200);
    }
}
