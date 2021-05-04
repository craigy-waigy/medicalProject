<?php

namespace App\Http\Controllers\Api\Account;

use App\Services\SanatoriumDoctorChatService;
use App\Traits\CaptchaTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class SanatoriumDoctorChatController extends Controller
{
    use CaptchaTrait;
    /**
     * @var SanatoriumDoctorChatService
     */
    protected $sanatoriumDoctorChatService;

    /**
     * SanatoriumDoctorChatController constructor.
     * @param SanatoriumDoctorChatService $sanatoriumDoctorChatService
     */
    public function __construct(SanatoriumDoctorChatService $sanatoriumDoctorChatService)
    {
        $this->sanatoriumDoctorChatService = $sanatoriumDoctorChatService;
    }

    /**
     * @api {get} /api/{locale}/account/sanatorium-doctor/chat Получение списка чатов (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName ListChat
     * @apiGroup SanatoriumDoctorChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 51,
                "name": "my-name",
                "avatar": null,
                "fullname": " my-name ",
                "last_name": null,
                "father_name": null,
                "email": "patient@mail.com",
                "sanatorium_doctor_chat_id": 9,
                "messages_count": "1",
                "new_messages_count": "1",
                "last_message": "2019-08-19 05:18:42",
                "sanatorium_doctor": null
            }
        ]
    }
     * @apiSuccessExample {json} Ответ сервера пациенту:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 5,
                "name": "name",
                "avatar": null,
                "fullname": "lastname name fathername",
                "last_name": "lastname",
                "father_name": "fathername",
                "email": "admin@admin.com",
                "sanatorium_doctor_chat_id": 9,
                "messages_count": "1",
                "new_messages_count": "0",
                "last_message": "2019-08-19 05:18:42",
                "sanatorium_doctor": {
                    "id": 2,
                    "user_id": 5,
                    "object_id": 33,
                    "specializations": "[\"terapevt\", \"okulist\"]",
                    "languages": [
                        "ru",
                        "en"
                    ],
                    "online": false,
                    "object": {
                        "id": 33,
                        "title": "Санаторий Буран",
                        "country_id": 88,
                        "region_id": 6977,
                        "city_id": 7,
                        "country": {
                            "id": 88,
                            "name": "Российская Федерация"
                        },
                        "region": {
                            "id": 6977,
                            "name": "Алтайский Край"
                        },
                        "city": {
                            "id": 7,
                            "name": "Барнаул"
                        }
                    }
                }
            }
        ]
    }

    *

     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function listChats(Request $request, string $locale)
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

        $chats = $this->sanatoriumDoctorChatService->listChats($page, $rowsPerPage, $userId,  $searchKey, $locale);

        return response()->json($chats, 200);
    }

    /**
     * @api {get} /api/{locale}/account/sanatorium-doctor/chat/{sanatorium_doctor_chat_id} Получение сообщений чата (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName GetChat
     * @apiGroup SanatoriumDoctorChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера пациенту:
    HTTP/1.1 200 OK
     *

    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 25,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 5,
                "message": "message from doctor",
                "is_read": false,
                "created_at": "2019-08-19 06:16:31",
                "deleted_at": null,
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 24,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 5,
                "message": "message from doctor",
                "is_read": false,
                "created_at": "2019-08-19 06:14:44",
                "deleted_at": null,
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 23,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 51,
                "message": "message to doctor",
                "is_read": false,
                "created_at": "2019-08-19 05:18:42",
                "deleted_at": null,
                "user": {
                    "id": 51,
                    "fullname": " my-name ",
                    "avatar": null
                }
            }
        ],
        "chat_with_user": {
            "id": 5,
            "name": "name",
            "avatar": null,
            "fullname": "lastname name fathername",
            "last_name": "lastname",
            "father_name": "fathername",
            "email": "admin@admin.com",
            "sanatorium_doctor": {
                "id": 2,
                "user_id": 5,
                "object_id": 33,
                "specializations": "[\"terapevt\", \"okulist\"]",
                "languages": [
                    "ru",
                    "en"
                ],
                "online": false,
                "object": {
                    "id": 33,
                    "title": "Санаторий Буран",
                    "country_id": 88,
                    "region_id": 6977,
                    "city_id": 7,
                    "country": {
                        "id": 88,
                        "name": "Российская Федерация"
                    },
                    "region": {
                        "id": 6977,
                        "name": "Алтайский Край"
                    },
                    "city": {
                        "id": 7,
                        "name": "Барнаул"
                    }
                }
            }
        }
    }

     *
     *
     * @apiSuccessExample {json} Ответ сервера врачу:
    HTTP/1.1 200 OK
     *

    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 25,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 5,
                "message": "message from doctor",
                "is_read": false,
                "created_at": "2019-08-19 06:16:31",
                "deleted_at": null,
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 24,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 5,
                "message": "message from doctor",
                "is_read": false,
                "created_at": "2019-08-19 06:14:44",
                "deleted_at": null,
                "user": {
                    "id": 5,
                    "fullname": "lastname name fathername",
                    "avatar": null
                }
            },
            {
                "id": 23,
                "sanatorium_doctor_chat_id": 9,
                "user_id": 51,
                "message": "message to doctor",
                "is_read": false,
                "created_at": "2019-08-19 05:18:42",
                "deleted_at": null,
                "user": {
                    "id": 51,
                    "fullname": " my-name ",
                    "avatar": null
                }
            }
        ],
        "chat_with_user": {
            "id": 51,
            "name": "my-name",
            "avatar": null,
            "fullname": " my-name ",
            "last_name": null,
            "father_name": null,
            "email": "patient@mail.com",
            "sanatorium_doctor": null
        }
    }

     *
     *
     * @param Request $request
     * @param string $locale
     * @param int $sanatoriumDoctorChatId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getChat(Request $request, string $locale, int $sanatoriumDoctorChatId)
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

        $chat = $this->sanatoriumDoctorChatService->getChat($page, $rowsPerPage, $sanatoriumDoctorChatId, $userId, $searchKey, $locale);

        return response()->json($chat, 200);
    }

    /**
     * @api {post} /api/{locale}/account/sanatorium-doctor/chat Отправка сообщения (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName SendMessage
     * @apiGroup SanatoriumDoctorChat
     *
     * @apiDescription
     * При успешном запросе эмитится событие в websocket: <b>SanatoriumDoctorNewMessage</b>  <a href="#api-Websockets-SanatoriumDoctorChat">doc>></a>
     *
     * @apiHeader {string} Authorization access-token
     * @apiHeader {string} X-Socket-Id <code>Echo.socketId()</code>
     *
     * @apiParam {integer} sanatorium_doctor_chat_id ID чата
     * @apiParam {String} message Текст сообщения
     *
     * @apiSuccessExample {json} Ответ сервера:
    HTTP/1.1 200 OK
    {
        "id": 25,
        "sanatorium_doctor_chat_id": 9,
        "user_id": 5,
        "message": "message from doctor",
        "is_read": false,
        "created_at": "2019-08-19 06:16:31",
        "deleted_at": null,
        "user": {
            "id": 5,
            "fullname": "lastname name fathername",
            "avater": null
        }
    }
     *
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function sendMessage(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'sanatorium_doctor_chat_id' => 'required|integer',
            'message' => 'required|string'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $sanatoriumDoctorChatId = $request->get('sanatorium_doctor_chat_id');
        $message = $request->get('message');
        $userId = Auth::user()->id;

        $message = $this->sanatoriumDoctorChatService->sendMessage($message, $sanatoriumDoctorChatId, $userId, $locale, null, false);

        return response()->json($message, 201);
    }

    /**
     * @api {put} /api/{locale}/account/sanatorium-doctor/chat Пометка сообщения прочитанным(locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName ReadMessage
     * @apiGroup SanatoriumDoctorChat
     *
     * @apiDescription
     * При успешном запросе эмитится событие в websocket: <b>SanatoriumDoctorReadMessage</b>  <a href="#api-Websockets-SanatoriumDoctorChat">doc>></a>
     *
     * @apiHeader {string} Authorization access-token
     * @apiHeader {string} X-Socket-Id <code>Echo.socketId()</code>
     *
     * @apiParam {integer} sanatorium_doctor_chat_id ID чата
     * @apiParam {integer} message_id ID сообщения
     *
     * @apiSuccessExample {json} Ответ сервера:
    HTTP/1.1 200 OK
    {
        "id": 25,
        "sanatorium_doctor_chat_id": 9,
        "user_id": 5,
        "message": "message from doctor",
        "is_read": false,
        "created_at": "2019-08-19 06:16:31",
        "deleted_at": null,
        "user": {
            "id": 5,
            "fullname": "lastname name fathername",
            "avater": null
        }
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function readMessage(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'sanatorium_doctor_chat_id' => 'required|integer',
            'message_id' => 'required|integer'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $sanatoriumDoctorChatId = $request->get('sanatorium_doctor_chat_id');
        $messageId = $request->get('message_id');
        $userId = Auth::user()->id;

        $message = $this->sanatoriumDoctorChatService->readMessage($messageId, $sanatoriumDoctorChatId, $userId, $locale);

        return response()->json($message, 200);
    }

    /**
     * @api {post} /api/{locale}/account/sanatorium-doctor/chat/message/from-object-page Отправка сообщения со старницы санатория(locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName SendMessageFromPage
     * @apiGroup SanatoriumDoctorChat
     *
     *
     * @apiParam {string} email Email задающего вопрос
     * @apiParam {string} name Имя задающего вопрос
     * @apiParam {string} [last_name] Фамилие задающего вопрос
     * @apiParam {string} [father_name] Отчество задающего вопрос
     * @apiParam {String} message Текст сообщения
     * @apiParam {integer} doctor_id ID пользователя-врача в санатории
     *
     * @apiParam {String} response ответ ReCaptcha
     *
     * @apiSuccessExample {json} Ответ сервера:
    HTTP/1.1 200 OK
    {
        "message": "Вопрос отправлен"
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendPatientMessageFromObjectPage(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
           'email' => 'required|email',
           'name' => 'required|string|max:255',
           'father_name' => 'nullable|string|max:255',
           'last_name' => 'nullable|string|max:255',
           'doctor_id' => 'required|integer',
           'message' => 'required|string',
           'response' => 'required',
        ],[
            'response.required' => 'Не отправлен response для reCAPTCHA',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $this->captchaValidate( $request->get('response') );

        $data = $request->only('email', 'name', 'father_name', 'last_name');
        $message = $request->get('message');
        $doctorId = $request->get('doctor_id');

        $this->sanatoriumDoctorChatService->sendPatientMessageFromObjectPage($message, $doctorId, $data, $locale);

        return response()->json(['message' => 'Вопрос отправлен'], 201);
    }

    /**
     * @api {delete} /api/{locale}/account/sanatorium-doctor/chat/{sanatorium_doctor_chat_id} Удаление переписки (locale = ru, en)
     * @apiVersion 0.1.0
     * @apiName DeleteChat
     * @apiGroup SanatoriumDoctorChat
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера:
    HTTP/1.1 200 OK
    {
        "message": "Переписка удалена"
    }
     *
     * @param Request $request
     * @param int $sanatoriumDoctorChatId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deleteChat( string $locale, int $sanatoriumDoctorChatId)
    {
        $userId = Auth::user()->id;
        $this->sanatoriumDoctorChatService->deleteChat($sanatoriumDoctorChatId, $userId);

        return response()->json(['message' => 'Переписка удалена'], 200);
    }
}
