<?php

namespace App\Http\Controllers\Api\Account\Partner;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\PartnerService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PartnerController extends Controller
{
    /**
     * @var PartnerService
     */
    protected $partnerService;

    /**
     * PartnerController constructor.
     */
    public function __construct()
    {
        $this->partnerService = new PartnerService();
    }

    /**
     * @api {get} /api/account/partner Получение партнера
     * @apiVersion 0.1.0
     * @apiName GetPartner
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "partner_type_id": 2,
        "manager_name_ru": "ManagerRu",
        "manager_name_en": "ManagerEn",
        "organisation_short_name_ru": "ShortNameRu-1",
        "organisation_short_name_en": "ShortNameEn-1",
        "organisation_full_name_ru": "FullnameRu1",
        "organisation_full_name_en": "FullNameEn1",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "mail_address_ru": "AddressRu",
        "mail_address_en": "AddressRu",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "email": "",
        "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
        "telephones": [
            "+7(499)335-32-65",
            "+7(499)335-32-66"
        ],
        "deleted_at": null,
        "created_at": "2019-04-03 10:34:31",
        "updated_at": "2019-04-03 10:37:04",
        "type": {
            "id": 1,
            "name": "Mass media",
            "image": null
        },
        "images": [
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
            }
        ],
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
        ],
        "publication_types": [
            {
                "publication_type_id": 1,
                "type": "статьи",
                "count": 2
            },
            {
                "publication_type_id": 2,
                "type": "аналитика",
                "count": 0
            },
            {
                "publication_type_id": 3,
                "type": "исследования",
                "count": 1
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
        }
    }
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function get()
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $partner = $this->partnerService->getPartner($partnerId, null, null, true);

        return response()->json($partner, 200);
    }

    /**
     * @api {post} /api/account/partner Редактирование партнера
     * @apiVersion 0.1.0
     * @apiName EditPartner
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [manager_name_ru] ФИО руководителя на русс.
     * @apiParam {string} [manager_name_en] ФИО руководителя на анг.
     * @apiParam {string} [organisation_short_name_ru] Краткое название на русс.
     * @apiParam {string} [organisation_short_name_en] Краткое название на анг.
     * @apiParam {string} [organisation_full_name_ru] Полное название на русс.
     * @apiParam {string} [organisation_full_name_en] Полное название на анг.
     * @apiParam {string} [description_ru] Описание на русск.
     * @apiParam {string} [description_en] Описание на анг.
     * @apiParam {string} [address_ru] Адресс на русс.
     * @apiParam {string} [address_en] Адрес на анг.
     * @apiParam {string} [mail_address_ru] Почтовый адрес на рус.
     * @apiParam {string} [mail_address_en] Почтовый адрес на анг.
     * @apiParam {string} [email] Эл. адрес.
     * @apiParam {boolean} [active] Активность
     * @apiParam {file} [logo] Логотип
     * @apiParam {array} [telephones] Массив с телефонами ["+7(499)335-32-65", "+7(499)335-32-66"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "partner_type_id": 2,
        "manager_name_ru": "ManagerRu",
        "manager_name_en": "ManagerEn",
        "organisation_short_name_ru": "ShortNameRu-1",
        "organisation_short_name_en": "ShortNameEn-1",
        "organisation_full_name_ru": "FullnameRu1",
        "organisation_full_name_en": "FullNameEn1",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "address_ru": "AddressRu",
        "address_en": "AddressEn",
        "logo": "/storage/partner_logo/JtV2FKSRuiYcIv2XJpZm79CbEqVFf7KBQMZKciAx.jpeg",
        "telephones": [
            "+7(499)335-32-65",
            "+7(499)335-32-66"
        ],
        "deleted_at": null,
        "created_at": "2019-04-03 10:34:31",
        "updated_at": "2019-04-03 10:37:04",
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
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function edit(Request $request)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $valid = Validator($request->all(), [
            'manager_name_ru' => 'string|max:255|nullable',
            'manager_name_en' => 'string|max:255|nullable',
            'organisation_short_name_ru' => "string|max:255|unique:partners,organisation_short_name_ru,{$partnerId},id|nullable",
            'organisation_short_name_en' => "string|max:255|unique:partners,organisation_short_name_en,{$partnerId},id|nullable",
            'organisation_full_name_ru' => "string|max:255|unique:partners,organisation_full_name_ru,{$partnerId},id|nullable",
            'organisation_full_name_en' => "string|max:255|unique:partners,organisation_full_name_en,{$partnerId},id|nullable",
            'description_ru' => 'string|nullable',
            'description_en' => 'string|nullable',
            'address_ru' => 'string|nullable',
            'address_en' => 'string|nullable',
            'mail_address_ru' => 'string|nullable',
            'mail_address_en' => 'string|nullable',
            'email' => 'email|nullable',
            'logo' => 'file|image|max:5124',
            'telephones' => [ new IsArray ],
        ], [
            'organisation_short_name_ru.unique' => "Короткое название на русском уже существует",
            'organisation_short_name_en.unique' => "Короткое название на английском уже существует",
            'organisation_full_name_ru.unique' => "Полное название на русском уже существует",
            'organisation_full_name_en.unique' => "Полное название на английском уже существует",
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $partner = $this->partnerService->editPartner($request, $partnerId, true);

        return response()->json($partner, 200);
    }

    /**
     * ============================ Gallery ========================================================================
     */

    /**
     * @api {post} /api/account/partner/image Добавление изображения в галерею партнера
     * @apiVersion 0.1.0
     * @apiName AddGalleryImage
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} [description] Описание
     * @apiParam {integer} [sorting_rule] Порядок сортировки
     * @apiParam {file} image Изображение
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 13,
        "partner_id": 1,
        "image": "/storage/partner_gallery/SD6xVj9BwZu0AEFMRh1GbhiPXRXDYbfwvqOY8BHi.jpeg",
        "description": "12312",
        "sorting_rule": 0,
        "is_main": false,
        "created_at": "2019-07-04 03:57:18",
        "updated_at": "2019-07-04 03:57:18",
        "moderation": {
            "status_id": 2,
            "message": null
        }
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function addImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'image' => 'required|file|image|max:10240',
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable'
        ],[
            'image.max' => "Максимальный размер изображения 10 МБ (10240 КБ)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $image = $this->partnerService->addImage($request, $partnerId, false);

        return response()->json($image, 201);
    }

    /**
     *  @api {put} /api/account/partner/image/{imageId} Редактирование изображения гелереи партнера
     * @apiVersion 0.1.0
     * @apiName EditGalleryImage
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} [description] Описание
     * @apiParam {integer} [sorting_rule] Порядок сортировки
     * @apiParam {boolean} [is_main] Установка главного изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 13,
        "partner_id": 1,
        "image": "/storage/partner_gallery/SD6xVj9BwZu0AEFMRh1GbhiPXRXDYbfwvqOY8BHi.jpeg",
        "description": "12312",
        "sorting_rule": 0,
        "is_main": false,
        "created_at": "2019-07-04 03:57:18",
        "updated_at": "2019-07-04 03:57:18",
        "moderation": {
            "status_id": 2,
            "message": null
        }
    }
     *
     * @param Request $request
     * @param int|null $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function editImage(Request $request, ?int $imageId)
    {
        $valid = Validator($request->all(), [
            'description' => 'string|nullable',
            'sorting_rule' => 'integer|nullable',
            'is_main' => 'boolean',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;
        $data = $request->only('description', 'sorting_rule', 'is_main');

        $image = $this->partnerService->editImage($data, $imageId, $partnerId);

        return response()->json($image, 200);
    }

    /**
     * @api {put} /api/account/partner/image Сортировка изображений гвлереи партнера
     * @apiVersion 0.1.0
     * @apiName SortingGalleryImage
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} sorting Массив с ID изображениями в порядке сортировки [5,4,2,3,1]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message" : "Изображения отсортированы"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function sortingImage(Request $request)
    {
        $valid = Validator($request->all(), [
            'sorting' => [ 'required', new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $this->partnerService->sortingImage($sorting, $partnerId);

        return response()->json(['message' => 'Изображения отсортированы'], 200);
    }

    /**
     * @api {get} /api/account/partner/images Получение изображений гелереи партнера
     * @apiVersion 0.1.0
     * @apiName GetGalleryImages
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    *
    [
         {
            "id": 2,
            "partner_id": 1,
            "image": "/storage/partner_gallery/fXMnA5JHLfiY5c6hglg8LdV2YyCRwudE2KnKOB3I.jpeg",
            "description": "Descr",
            "sorting_rule": 2,
            "is_main": false,
            "created_at": "2019-04-04 12:24:40",
            "updated_at": "2019-07-02 09:12:11",
            "moderation": {
                "status_id": 2,
                "message": null
            }
        }
    ]
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getImages()
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;
        $images = $this->partnerService->getImages($partnerId);

        return response()->json($images, 200);
    }

    /**
     * @api {delete} /api/account/partner/image/{imageId} Удаление изображения из галереи партнера
     * @apiVersion 0.1.0
     * @apiName DeleteGalleryImage
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message" : "Изображение удалено"
    }
     *
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteImage(int $imageId)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $this->partnerService->deleteImage($imageId, $partnerId);

        return response()->json(['message' => 'Изображение удалено'], 200);
    }

    /**
     * @api {post} /api/account/partner/file Добавление файла партнера
     * @apiVersion 0.1.0
     * @apiName AddFile
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} partner_id ID партнера
     * @apiParam {file} file файл ( Максимальный размер файла 20мб (20480 кб) )
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "file": "/storage/partner_files/eAbvagS7vBG8qkEuOQ0iyejj5APa6GfpX8o4bx8t.jpeg",
        "partner_id": "1",
        "description": "qwwefwe",
        "id": 3
    }
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function addFile(Request $request)
    {
        $valid = Validator($request->all(), [
            'file' => 'required|file|max:20480',

        ],[
            'file.max' => "Максимальный размер файла 20мб (20480 кб)"
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $file = $this->partnerService->addFile($partnerId, $request);

        return response()->json($file, 201);
    }

    /**
     * @api {delete} /api/account/partner/file/{fileId} Удаление файла партнера
     * @apiVersion 0.1.0
     * @apiName deleteFile
     * @apiGroup AccountPartner
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": [
            "Файл удален"
        ]
    }
     *
     * @param int $fileId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteFile(int $fileId)
    {
        if (is_null(Auth::user()->partner))
            throw new ApiProblemException('Партнер пользователю не назначен', 422);
        $partnerId = Auth::user()->partner->id;

        $this->partnerService->deleteFile($fileId, $partnerId);
        return response()->json([ 'message' => [
            "Файл удален"
        ]]);
    }
}
