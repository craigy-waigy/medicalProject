<?php

namespace App\Http\Controllers\Api\Admin\Offer;

use App\Exceptions\ApiProblemException;
use App\Services\OfferService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rules\IsArray;
use App\Rules\Scope;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    /**
     * @var OfferService
     */
    protected $offerService;

    /**
     * OfferController constructor.
     */
    public function __construct()
    {
        $this->offerService = new OfferService();
    }

    /**
     * @api {get} /api/admin/offer Получение и поиск предложений
     * @apiVersion 0.1.0
     * @apiName SearchOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [scope] Массив видимости новости ["for_objects", "for_partners", "for_hed_doctors", "for_doctors"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 8,
                "title_ru": "offer_title",
                "title_en": "offer_title_en",
                "description_ru": "",
                "description_en": "",
                "image": null,
                "scope": null,
                "is_visible": true,
                "created_at": "2019-06-07 06:28:41",
                "updated_at": "2019-06-07 06:30:47",
                "alias": null,
                "has_booking": false,
                "short_description_ru": "",
                "short_description_en": "",
                "published_at": "2019-04-12"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function search(?Request $request)
    {
        $valid = Validator($request->only('page', 'rowsPerPage', 'searchKey', 'scope'),[

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'scope' => [ new IsArray, new Scope ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $scope = json_decode($request->get('scope') ?? null);
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->offerService->search($page, $rowsPerPage, $searchKey, $scope, $sorting, false);
    }

    /**
     * @api {post} /api/admin/offer Сохранение нового предложения
     * @apiVersion 0.1.0
     * @apiName CreateOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  title_ru Название предложения
     * @apiParam {String}  [title_en] Название предложения
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [short_description_ru] краткое описание на русском.
     * @apiParam {string}  [short_description_en] краткое описание на англ.
     * @apiParam {boolean}  [has_booking] Имеется возможность бронирования
     * @apiParam {boolean}  [is_visible] Статус
     * @apiParam {file}  [image] файл изображения.
     * @apiParam {json}  [scope] Массив видимости ["for_objects", "for_partners", "for_hed_doctors", "for_doctors"]
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK

    {
        "id": 7,
        "title_ru": "title ru",
        "title_en": "title en",
        "description_ru": "description ru",
        "description_en": "description ru",
        "image": "/storage/offer/10osYGkN4iHfsZSP9aQ04CxZM42gIVAdnLtSxW7D.jpeg",
        "scope": [
            "for_patients"
        ],
        "is_visible": true,
        "created_at": "2019-02-05 10:18:28",
        "updated_at": "2019-06-07 06:31:32",
        "alias": "234",
        "has_booking": false,
        "short_description_ru": "",
        "short_description_en": "",
        "published_at": "2019-04-12"
    }

     *
     * @param Request $request
     * @return \App\Models\Offer|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws
     */
    public function create(Request $request)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'title_ru' => 'required|string|max:255',
            'title_en' => 'string|max:255',
            'has_booking' => 'boolean',
            'is_visible' => 'boolean',
            'scope' => [ new IsArray, new Scope ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        return $this->offerService->create($request);
    }

    /**
     * @api {get} /api/admin/offer/{offerId} Получение предложения
     * @apiVersion 0.1.0
     * @apiName GetOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 8,
        "title_ru": "offer_title",
        "title_en": "offer_title_en",
        "description_ru": "",
        "description_en": "",
        "image": null,
        "scope": null,
        "is_visible": true,
        "created_at": "2019-06-07 06:28:41",
        "updated_at": "2019-06-07 06:30:47",
        "alias": null,
        "has_booking": false,
        "short_description_ru": "",
        "short_description_en": "",
        "published_at": "2019-04-12",
        "seo": null
    }
     * @param int $offerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $offerId)
    {
        $offer = $this->offerService->get($offerId);

        return response()->json($offer, 200);
    }

    /**
     * @api {post} /api/admin/offer/{offerId} Редактирование предложения
     * @apiVersion 0.1.0
     * @apiName EditOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  title_ru Название предложения
     * @apiParam {String}  [title_en] Название предложения
     * @apiParam {boolean} [is_visibly] Статус
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [short_description_ru] краткое описание на русском.
     * @apiParam {string}  [short_description_en] краткое описание на англ.
     * @apiParam {boolean}  [has_booking] Имеется возможность бронирования
     * @apiParam {file}  [image] файл изображения.
     * @apiParam {json}  [scope] Массив видимости ["for_objects", "for_partners", "for_hed_doctors", "for_doctors"]
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 7,
        "title_ru": "title ru",
        "title_en": "title en",
        "description_ru": "description ru",
        "description_en": "description ru",
        "image": "/storage/offer/10osYGkN4iHfsZSP9aQ04CxZM42gIVAdnLtSxW7D.jpeg",
        "scope": [
        "for_patients"
        ],
        "is_visible": true,
        "created_at": "2019-02-05 10:18:28",
        "updated_at": "2019-06-07 06:31:32",
        "alias": "234",
        "has_booking": false,
        "short_description_ru": "",
        "short_description_en": "",
        "published_at": "2019-04-12"
    }
     *
     * @param Request $request
     * @param int $offerId
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, int $offerId)
    {
        $valid = Validator($request->all(),[
            'image' => 'file|image|max:5128|nullable',
            'title_ru' => 'string|max:255',
            'title_en' => 'string|max:255|nullable',
            'is_visible' => 'boolean',
            'has_booking' => 'boolean',
            'scope' => [ new IsArray, new Scope ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $offer = $this->offerService->update($request, $offerId);

        return response()->json($offer, 200);
    }

    /**
     * @api {delete} /api/admin/news/{newsId} Удаление предложения
     * @apiVersion 0.1.0
     * @apiName DeleteOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "offre": [
            "Предложение удалено"
        ]
    }
     *
     * @param int $offerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $offerId)
    {
        $deleted = $this->offerService->delete($offerId);
        if ($deleted) {
            return response()->json(['offer' => [
                'Предложение удалено'
            ]], 200);

        } else {
            return response()->json(['offer' => [
                'Предложение не найдено'
            ]], 404);
        }
    }

    /**
     * @api {post} /api/admin/offer/{offerId}/image Добавление изображения
     * @apiVersion 0.1.0
     * @apiName AddImageOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file}  image Файл изображения
     * @apiParam {string}  [description] Название изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "offer_id": 2,
        "image": "/storage/offer/8Cy4wsc5rPUcSQjVKEONQzUzGkZDyKWAa1d1xIU4.jpeg",
        "description": null,
        "id": 7
    }
     * @param Request $request
     * @param int $offerId
     * @return \App\Models\OfferImage|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|null
     */
    public function addImage(Request $request, int $offerId)
    {
        $valid = Validator($request->all(),[
            'image' => 'required|file|image|max:5128',
            'description'=> 'string|max:255|nullable'
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->offerService->addImage($request, $offerId);
    }

    /**
     * @api {get} /api/admin/offer/{offerId}/images Получение изображений
     * @apiVersion 0.1.0
     * @apiName GetImagesOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 10,
            "offer_id": 4,
            "description": null,
            "image": "/storage/offer/UI2b8uW1HC7brNfjwPd8MZpKVcOtMQBKZdLhgiyI.jpeg",
            "created_at": null,
            "updated_at": null
        },
        {
            "id": 9,
            "offer_id": 4,
            "description": null,
            "image": "/storage/offer/VFlEzZX6mqJ1U42Mre861llNO3ZNaRixDLB56Uhp.jpeg",
            "created_at": null,
            "updated_at": null
        },
        {
            "id": 8,
            "offer_id": 4,
            "description": null,
            "image": "/storage/offer/TxPfbU48pYeIez0wqA1JFCW3V9VHl7hr4phyI9ZG.jpeg",
            "created_at": null,
            "updated_at": null
        }
    ]
     *
     * @param int $offerId
     * @return mixed
     */
    public function getImages(int $offerId)
    {
        return $this->offerService->getImages( $offerId);
    }

    /**
     * @api {delete} /api/admin/offer/image/{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteImageOffer
     * @apiGroup AdminOffer
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "offer": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $offerImageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $offerImageId)
    {
        $deleted = $this->offerService->deleteImage($offerImageId);
        if ($deleted){

            return response()->json(['offer' =>[
                'Изображение удалено'
            ]], 200);
        } else {

            return response()->json(['offer' =>[
                'Изображение не найдено'
            ]], 404);
        }
    }
}
