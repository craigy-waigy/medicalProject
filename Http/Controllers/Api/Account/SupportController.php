<?php

namespace App\Http\Controllers\Api\Account;

use App\Exceptions\ApiProblemException;
use App\Services\SupportCRMService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SupportController extends Controller
{
    /**
     * @var SupportCRMService
     */
    protected $supportCrmService;

    /**
     * SupportController constructor.
     *
     * @param SupportCRMService $supportCRMService
     */
    public function __construct(SupportCRMService $supportCRMService)
    {
        $this->supportCrmService = $supportCRMService;
    }

    /**
     * @api {get} /api/{locale}/account/support/chat Получение списка чатов (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName ListChat
     * @apiGroup SupportChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 2,
        "items": [
            {
                "chat_id": 4,
                "name": "Админ",
                "last_name": "Админов",
                "father_name": "Админович",
                "fullname": "Админов Админ Админович",
                "email": "admin@admin.com",
                "avatar": null,
                "messages_count": 21,
                "new_messages_count": 2,
                "question": "question-4"
                "last_message": "2019-11-21 12:01:31"
            },
            {
                "chat_id": 6,
                "name": "Санатория",
                "last_name": "Врачеватель",
                "father_name": "Ивушка",
                "fullname": "Врачеватель Санатория Ивушка",
                "email": "nikertos@mail.ru",
                "avatar": "/storage/avatars/MxoSP9CRkBYeCwytTiFzRNSkqdwdYmWdGVxZB78o.png",
                "messages_count": 1,
                "new_messages_count": 0,
                "question": "question-6"
                "last_message": "2019-11-21 10:22:34"
            }
        ]
    }
     /*
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function listChats(Request $request)
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

        try {
            $chats = $this->supportCrmService->listChats($page, $rowsPerPage, $userId, $searchKey);
        } catch (ApiProblemException $exception){
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }


        return response()->json($chats, 200);
    }

    /**
     * @api {get} /api/{locale}/account/support/chat/{chatId} Получение сообщений чата (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName GetChat
     * @apiGroup SupportChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 27,
        "items": [
            {
                "id": 46,
                "chat_id": 4,
                "user_id": 35,
                "message": "ответ от здравпродукта",
                "is_read": true,
                "created_at": "2019-11-21 12:01:31",
                "user": {
                    "id": 35,
                    "fullname": "Врачеватель Санатория Ивушка",
                    "avatar": "/storage/avatars/MxoSP9CRkBYeCwytTiFzRNSkqdwdYmWdGVxZB78o.png"
                }
            },
            {
                "id": 45,
                "chat_id": 4,
                "user_id": 12,
                "message": "А, привет)",
                "is_read": false,
                "created_at": "2019-11-21 12:00:56",
                "user": {
                    "id": 12,
                    "fullname": "Админов Админ Админович",
                    "avatar": null
                }
            }
        ],
        "chat_info": {
            "name": "Санатория",
            "last_name": "Врачеватель",
            "father_name": "Ивушка",
            "fullname": "Врачеватель Санатория Ивушка",
            "email": "nikertos@mail.ru",
            "avatar": "/storage/avatars/MxoSP9CRkBYeCwytTiFzRNSkqdwdYmWdGVxZB78o.png",
            "question": "question-6"
        }
    }
     /*
     * @param Request $request
     * @param string $locale
     * @param int $chatId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChat(Request $request, string $locale, int $chatId)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $userId = Auth::user()->id;

        try {
            $chat = $this->supportCrmService->getChat($page, $rowsPerPage, $chatId, $userId, $searchKey);
        } catch (ApiProblemException $exception){
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        return response()->json($chat, 200);
    }

    /**
     * @api {post} /api/{locale}/account/support/chat Отпрввка сообщения (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName SendMessage
     * @apiGroup SupportChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} chat_id ID чата
     * @apiParam {string} message Сообщение
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
     *
    {
        "id": 46,
        "user_id": 35,
        "message": "ответ вам",
        "is_read": true,
        "created_at": "2019-11-21 12:01:31",
        "user": {
            "id": 35,
            "fullname": "Врачеватель Санатория Ивушка",
            "avatar": "/storage/avatars/MxoSP9CRkBYeCwytTiFzRNSkqdwdYmWdGVxZB78o.png"
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        $valid = Validator($request->all(), [
            'chat_id' => 'required|integer',
            'message' => 'required|string'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $chatId = $request->get('chat_id');
        $message = $request->get('message');
        $userId = Auth::user()->id;

        try {
            $message = $this->supportCrmService->sendMessage($message, $chatId, $userId);
        } catch (ApiProblemException $exception){
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        return response()->json($message, 200);
    }

    /**
     * @api {put} /api/{locale}/account/support/chat Пометка сообщения прочитанным (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName ReadMessage
     * @apiGroup SupportChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} chat_id ID чата
     * @apiParam {integer} message_id ID сообщения
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
     *
    {
        "id": 46,
        "user_id": 35,
        "message": "ответ вам",
        "is_read": false,
        "created_at": "2019-11-21 12:01:31",
        "user": {
            "id": 35,
            "fullname": "Врачеватель Санатория Ивушка",
            "avatar": "/storage/avatars/MxoSP9CRkBYeCwytTiFzRNSkqdwdYmWdGVxZB78o.png"
        }
    }
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     */
    public function readMessage(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'chat_id' => 'required|integer',
            'message_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $userId = Auth::user()->id;
        $chatId = $request->get('chat_id');
        $messageId = $request->get('message_id');

        try {
            $message = $this->supportCrmService->readMessage($messageId, $chatId, $userId);
        } catch (ApiProblemException $exception){
            return response()->json(['message' => $exception->getMessage()], $exception->getCode());
        }

        return response()->json($message, 200);
    }
}
