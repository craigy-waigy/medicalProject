<?php

namespace App\Http\Controllers\Api\Admin\Moderation;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\PartnerModerationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PartnerModerationController extends Controller
{
    /**
     * @var PartnerModerationService
     */
    protected $partnerModerationService;

    /**
     * PartnerModerationController constructor.
     */
    public function __construct()
    {
        $this->partnerModerationService = new PartnerModerationService();
    }

    /**
     * @api {put} /api/admin/moderation/partner/{partnerId} модерирование Партнера
     * @apiVersion 0.1.0
     * @apiName ModeratePartner
     * @apiGroup ModerationPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     *
     * @apiParamExample {json} Request-Example:
    {
        "manager_name":
            { "approve": true, "message": null},
         *
        "organisation_short_name":
            { "approve": true, "message": null},
         *
        "organisation_full_name":
            { "approve": false, "message": "moderator message"},
         *
        "description":
            { "approve": false, "message": "moderator message"},
         *
        "address":
            { "approve": true, "message": null},
         *
        "telephones":
            { "approve": true, "message": null},
         *
        "email":
            { "approve": true, "message": null},
         *
        "mail_address":
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
     * @param Request $request
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function moderate(Request $request, int $partnerId)
    {
        $valid = Validator($request->all(), [
            'manager_name' => [ new IsArray ],
            'organisation_short_name' => [ new IsArray ],
            'organisation_full_name' => [ new IsArray ],
            'description' => [ new IsArray ],
            'address' => [ new IsArray ],
            'telephones' => [ new IsArray ],
            'email' => [ new IsArray ],
            'mail_address' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $data = $request->all();
        foreach ($data as $parameter => $value){
            switch ($parameter){
                case 'manager_name' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveManagerName($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectManagerName($partnerId, $value['message']);
                    }
                    break;

                case 'organisation_short_name' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveOrganisationShortName($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectOrganisationShortName($partnerId, $value['message']);
                    }
                    break;

                case 'organisation_full_name' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveOrganisationFullName($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectOrganisationFullName($partnerId, $value['message']);
                    }
                    break;

                case 'description' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveDescription($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectDescription($partnerId, $value['message']);
                    }
                    break;

                case 'address' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveAddress($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectAddress($partnerId, $value['message']);
                    }
                    break;

                case 'telephones' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveTelephones($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectTelephones($partnerId, $value['message']);
                    }
                    break;

                case 'email' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveEmail($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectEmail($partnerId, $value['message']);
                    }
                    break;

                case 'mail_address' :
                    if ($value['approve']){
                        $this->partnerModerationService->approveMailAddress($partnerId);
                    } else{
                        if (empty($value['message']))
                            throw new  ApiProblemException('Необходимо передать сообщение модератора message', 412);
                        $this->partnerModerationService->rejectMailAddress($partnerId, $value['message']);
                    }
                    break;

                default :
                    throw new ApiProblemException('Параметр модерации не определен на сервере', 422);
            }
        }

        return response()->json(['message' => 'Данные отмодерированы'], 200);
    }

    /**
     * @api {get} /api/admin/moderation/partner Получение списка партнеров для модерации
     * @apiVersion 0.1.0
     * @apiName ListPartner
     * @apiGroup ModerationPartner
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
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 1,
                "partner_type_id": 1,
                "user_id": 5,
                "manager_name_ru": "manager_name_ru",
                "manager_name_en": null,
                "organisation_short_name_ru": "titleRuModeration",
                "organisation_short_name_en": "titleEnModeration",
                "organisation_full_name_ru": null,
                "organisation_full_name_en": null,
                "description_ru": "descriptionRuModeration",
                "description_en": "descriptionRuModeration",
                "address_ru": "AddressRu",
                "address_en": "AddressEn",
                "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
                "telephones": [
                    "111",
                    "222",
                    "333"
                ],
                "deleted_at": null,
                "created_at": "2019-04-04 12:21:24",
                "updated_at": "2019-07-11 13:16:39",
                "alias": "test",
                "active": true,
                "email": "moderatedEmail@mail.ru",
                "mail_address_ru": null,
                "mail_address_en": null,
                "publications_count": "15",
                "publication_types": [
                    {
                        "publication_type_id": 1,
                        "type": "статьи",
                        "alias": null,
                        "count": 1
                    },
                    {
                        "publication_type_id": 3,
                        "type": "исследования",
                        "alias": null,
                        "count": 1
                    },
                    {
                        "publication_type_id": 2,
                        "type": "аналитика",
                        "alias": "qweqweq",
                        "count": 13
                    }
                ],
                "type": {
                    "id": 1,
                    "name_ru": "СМИ",
                    "name_en": "Mass media",
                    "image": null,
                    "created_at": null,
                    "updated_at": null,
                    "alias": null
                }
            }
        ]
    }
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

        $partners = $this->partnerModerationService->getForApproval($page, $rowsPerPage, $sorting, $searchKey);

        return response()->json($partners, 200);
    }

    /**
     * @api {get} /api/admin/moderation/partner/{partnerId} Получение партнера для модерации
     * @apiVersion 0.1.0
     * @apiName GetPartner
     * @apiGroup ModerationPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
        {
        "id": 1,
        "partner_type_id": 1,
        "user_id": 5,
        "manager_name_ru": "manager_name_ru",
        "manager_name_en": null,
        "organisation_short_name_ru": "titleRuModeration",
        "organisation_short_name_en": "titleEnModeration",
        "organisation_full_name_ru": null,
        "organisation_full_name_en": null,
        "description_ru": "descriptionRuModeration",
        "description_en": "descriptionRuModeration",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "logo": "/storage/partner_logo/o9g6LNAf831QYn97FNFnQJzr66wIeGCK6tPgN7d1.jpeg",
        "telephones": [
        "111",
        "222",
        "333"
        ],
        "deleted_at": null,
        "created_at": "2019-04-04 12:21:24",
        "updated_at": "2019-07-11 13:16:39",
        "alias": "test",
        "active": true,
        "email": "moderatedEmail@mail.ru",
        "mail_address_ru": null,
        "mail_address_en": null,
        "images": [
        {
        "id": 1,
        "partner_id": 1,
        "image": "/storage/partner_gallery/VZSyJKp6EkzX7zOsrTZEuy3db3V3Ys6n3fybJlYh.jpeg",
        "description": "Descr",
        "sorting_rule": 0,
        "is_main": false,
        "moderation": {
        "status_id": 2,
        "message": null
        }
        },
        {
            "id": 12,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/xbRTiStv5Yoc31ZGAOIjt8LCeXKkW5Sit0CP3QTe.jpeg",
                    "description": "12312",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 11,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/orMG2s73UcOi0r5jmRGOD9ZTj0Tb3gn0ZQJ5E0JE.jpeg",
                    "description": "12312",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 10,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/WpBDKupqAKcoRZM6kVQ33EvH82gs1aEoak9Dscs0.jpeg",
                    "description": "qwwefwe",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 9,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/I60c6x59mXb2MKiu3EHyZV0sPzNv8sLWWeSAGxeX.jpeg",
                    "description": "description",
                    "sorting_rule": 0,
                    "is_main": true,
                    "moderation": {
            "status_id": 3,
                        "message": null
                    }
                },
        {
            "id": 6,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/KRhE9KJUCyu8TzCWpmvJeCFhb9Ou04qbWDUksQv7.jpeg",
                    "description": "deccription",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 7,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/JoKCpSkJwVQHkPzhagMAGRrwvGxT0IsIC7kmzcsi.jpeg",
                    "description": "deccription",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 3,
                        "message": null
                    }
                },
        {
            "id": 5,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/up4Q7YGQZOHiuToeg4yV8lOR7Q5pattNC5iRKc37.jpeg",
                    "description": "ertwertwerwtertwertwer",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 13,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/SD6xVj9BwZu0AEFMRh1GbhiPXRXDYbfwvqOY8BHi.jpeg",
                    "description": "12312",
                    "sorting_rule": 0,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 3,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/YiMHYcwpJ1WlSqRI23KM8NoGKnpgQ3GNyMj9XfVv.jpeg",
                    "description": "Descr",
                    "sorting_rule": 1,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 4,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/hPhO7GhQ1IxZqXm2lCvXaivGnAcChm9WDKuWVlB6.jpeg",
                    "description": "Descr",
                    "sorting_rule": 1,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                },
        {
            "id": 2,
                    "partner_id": 1,
                    "image": "/storage/partner_gallery/fXMnA5JHLfiY5c6hglg8LdV2YyCRwudE2KnKOB3I.jpeg",
                    "description": "Descr",
                    "sorting_rule": 2,
                    "is_main": false,
                    "moderation": {
            "status_id": 2,
                        "message": null
                    }
                }
        ],
        "publication_types": [
                {
                    "publication_type_id": 1,
                    "type": "статьи",
                    "alias": null,
                    "count": 1
                },
                {
                    "publication_type_id": 3,
                    "type": "исследования",
                    "alias": null,
                    "count": 1
                },
                {
                    "publication_type_id": 2,
                    "type": "аналитика",
                    "alias": "qweqweq",
                    "count": 13
                }
            ],
            "moderation": {
            "manager_name_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "manager_name_en": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "organisation_short_name_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "organisation_short_name_en": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "organisation_full_name_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "organisation_full_name_en": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "description_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "description_en": {
                "status_id": 3,
                    "value": "descriptionEnModeration",
                    "message": null
                },
                "address_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "address_en": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "telephones": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "email": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "mail_address_ru": {
                "status_id": 3,
                    "value": null,
                    "message": null
                },
                "mail_address_en": {
                "status_id": 3,
                    "value": null,
                    "message": null
                }
            },
            "type": {
            "id": 1,
                "name": "СМИ",
                "image": null
            },
            "seo": null,
            "partner_files": [
                {
                    "id": 5,
                    "partner_id": 1,
                    "file": "/storage/partner_files/aoixjG6A62oQ6EuqwMEggCwO0Kjp6drnUYw4gM2F.jpeg",
                    "description": "qwwefwe"
                },
                {
                    "id": 3,
                    "partner_id": 1,
                    "file": "/storage/partner_files/eAbvagS7vBG8qkEuOQ0iyejj5APa6GfpX8o4bx8t.jpeg",
                    "description": "qwwefwe"
                }
            ]
        }
     *
     * @param int $partnerId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getPartnerForApproval(int $partnerId)
    {
        $partner = $this->partnerModerationService->getPartnerForApproval($partnerId);

        return response()->json($partner, 200);
    }

}
