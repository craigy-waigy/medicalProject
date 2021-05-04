<?php

namespace App\Http\Controllers\Api\Admin\Moderation;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\PublicationModerationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PublicationModerationController extends Controller
{
    /**
     * @var PublicationModerationService
     */
    protected $publicationModerationService;

    /**
     * PublicationModerationController constructor.
     */
    public function __construct()
    {
        $this->publicationModerationService = new PublicationModerationService();
    }

    /**
     * @api {put} /api/admin/moderation/publication/{publicationIs} модерирование Публикации
     * @apiVersion 0.1.0
     * @apiName ModeratePublication
     * @apiGroup ModerationPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     *
     * @apiParamExample {json} Request-Example:
        {
            "diseases":
                { "approve": true, "message": null},
     *
            "medical_profiles":
                { "approve": true, "message": null},
     *
            "therapies":
                { "approve": false, "message": "moderator message"},
     *
            "objects":
                { "approve": false, "message": "moderator message"},
     *
            "geography":
                { "approve": true, "message": null},
     *
            "description":
                { "approve": true, "message": null},
     *
        }
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": [
            "Данные отмодерированы"
        ]
    }
     *
     *
     * @param Request $request
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function moderate(Request $request, int $publicationId)
    {
        $valid = Validator($request->all(), [
            'diseases' => [ new IsArray ],
            'medical_profiles' => [ new IsArray ],
            'therapies' => [ new IsArray ],
            'objects' => [ new IsArray ],
            'geography' => [ new IsArray ],
            'description' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->all();
        foreach ($data as $parameter=>$datum){
            if (!is_array($datum)) $datum = json_decode($datum, true);
            switch ($parameter){
                case 'diseases' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveDiseases($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectDiseases($publicationId, $datum['message']);
                    }
                    break;

                case 'medical_profiles' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveMedicalProfiles($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectMedicalProfiles($publicationId, $datum['message']);
                    }
                    break;

                case 'therapies' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveTherapies($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectTherapies($publicationId, $datum['message']);
                    }
                    break;

                case 'objects' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveObjects($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectObjects($publicationId, $datum['message']);
                    }
                    break;

                case 'geography' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveGeography($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectGeography($publicationId, $datum['message']);
                    }
                    break;

                case 'description' :
                    if ($datum['approve']){
                        $this->publicationModerationService->approveDescription($publicationId);
                    } else {
                        if (empty($datum['message']))
                            throw new ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->publicationModerationService->rejectDescription($publicationId, $datum['message']);
                    }
                    break;

                default :
                    throw new ApiProblemException('Параметр модерации не определен на сервере', 422);
            }
        }

        return response()->json(['message' => 'Данные отмодерированы'], 200);
    }

    /**
     * @api {get} /api/admin/moderation/publication Получение списка публикаций подлежащих модерации
     * @apiVersion 0.1.0
     * @apiName ListModeratePublication
     * @apiGroup ModerationPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "created_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
        {
            "page": 1,
            "rowsPerPage": 10,
            "total": 2,
            "items": [
                    {
                        "id": 9,
                        "publication_type_id": 2,
                        "moderation_status_id": 2,
                        "moderator_message": null,
                        "partner_id": 2,
                        "alias": null,
                        "title_ru": "titleRu",
                        "title_en": "titleEn",
                        "published_at": null,
                        "author_ru": null,
                        "author_en": null,
                        "description_ru": "descriptionRu",
                        "description_en": "descriptionEn",
                        "active": false,
                        "created_at": "2019-04-25 09:11:38",
                        "updated_at": "2019-04-25 09:11:38",
                        "type": {
                            "id": 2,
                            "name_ru": "аналитика"
                        },
                        "partner": {
                            "id": 2,
                            "partner_type_id": 2,
                            "organisation_short_name_ru": null,
                            "type": {
                                "id": 2,
                                "image": null,
                                "name_ru": "НИИ"
                            }
                        }
                    }
                ]
        }
     *
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getForApproval(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'sorting' => [ new IsArray ],
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $sorting = json_decode($request->get('sorting'), true) ?? null;
        $searchKey = $request->get('searchKey') ?? null;
        $publications = $this->publicationModerationService->getForApproval($page, $rowsPerPage, $sorting, $searchKey);

        return response()->json($publications, 200);
    }

    /**
     * @api {get} /api/admin/moderation/publication/{publicationId} Получение публикации
     * @apiVersion 0.1.0
     * @apiName GetModeratePublication
     * @apiGroup ModerationPublication
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
     *
    {
        "id": 10,
        "publication_type_id": 2,
        "moderation_status_id": 2,
        "moderator_message": null,
        "partner_id": 1,
        "alias": "ewerwe",
        "title_ru": "titleRu",
        "title_en": "titleEn",
        "published_at": "2019-04-18",
        "author_ru": null,
        "author_en": null,
        "active": true,
        "created_at": "2019-04-25 09:13:57",
        "updated_at": "2019-04-25 10:34:56",
        "description_ru": "",
        "description_en": "",
        "moderation": {
            "description_ru": {
                "status_id": 1,
                "value": null,
                "message": null
            },
            "description_en": {
                "status_id": 1,
                "value": null,
                "message": null
            },
            "medical_profiles": {
                "status_id": 3,
                "value": null,
                "message": null
            },
            "therapies": {
                "status_id": 3,
                "value": [
                    {
                        "id": 5,
                        "name_ru": "Перкуссия общетерапевтическая"
                    },
                    {
                        "id": 6,
                        "name_ru": "Термометрия общая"
                    }
                ],
                "message": null
            },
            "diseases": {
                "status_id": 2,
                "value": [
                    {
                        "id": 3,
                        "name_ru": "Холера"
                    },
                    {
                        "id": 4,
                        "name_ru": "Холера, вызванная холерным вибрионом 01, биовар cholerae"
                    }
                ],
                "message": null
            },
            "objects": {
                "status_id": 3,
                "value": [
                    {
                        "id": 30,
                        "title_ru": "Тестовый санаторий для Логуса"
                    }
                ],
                "message": null
            },
            "geography": {
                "status_id": 2,
                "value": {
                    "country": {
                        "id": 88,
                        "name": "Российская Федерация"
                    },
                    "region": {
                        "id": 3830,
                        "country_id": 41,
                        "name": "Oslomej",
                        "country": {
                            "id": 41,
                            "name": "Республика Македония"
                        }
                    },
                    "city": {
                        "id": 7,
                        "country_id": 88,
                        "region_id": 6977,
                        "name": "Барнаул",
                        "country": {
                            "id": 88,
                            "name": "Российская Федерация"
                        },
                        "region": {
                            "id": 6977,
                            "country_id": 88,
                            "name": "Алтайский Край"
                        }
                    }
                },
                "message": null
            }
        },
        "type": {
            "id": 2,
            "name_ru": "аналитика",
            "alias": "qweqweq"
        },
        "partner": {
            "id": 1,
            "organisation_short_name_ru": "Индия Китай",
            "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
            "alias": "test"
        },
        "seo": null,
        "objects": [],
        "medical_profiles": [
            {
                "id": 13,
                "name": "Урология и Гинекология",
                "alias": "urologiya-i-ginekologiya"
            },
            {
                "id": 14,
                "name": "Болезни уха, горла и носа",
                "alias": "bolezni-ukha-gorla-i-nosa"
            }
        ],
        "therapies": [
            {
                "id": 5,
                "name": "Перкуссия общетерапевтическая",
                "alias": "perkussiya-obshcheterapevticheskaya"
            },
            {
                "id": 6,
                "name": "Термометрия общая",
                "alias": "termometriya-obshchaya"
            }
        ],
        "diseases": [],
        "geography": null
    }
     /*
     *
     * @param int $publicationId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getPublicationForApproval(int $publicationId)
    {
        $publication = $this->publicationModerationService->getPublications($publicationId);

        return response()->json($publication, 200);
    }
}
