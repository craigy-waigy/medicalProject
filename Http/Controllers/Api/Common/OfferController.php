<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Rules\Scope;
use App\Services\OfferService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
     * @api {get} /api/{locale}/offer Получение и поиск предложений (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchOffer
     * @apiGroup PublicOffer
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [scope] Массив видимости предложения ["for_objects", "for_partners", "for_hed_doctors", "for_doctors"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 2,
        "items": [
            {
                "id": 8,
                "title": "offer_title",
                "description": "",
                "image": null,
                "alias": null,
                "published_at": "2019-04-12",
                "has_booking": false
            },
            {
                "id": 7,
                "title": "title ru",
                "description": "",
                "image": "/storage/offer/10osYGkN4iHfsZSP9aQ04CxZM42gIVAdnLtSxW7D.jpeg",
                "alias": "234",
                "published_at": "2019-04-12",
                "has_booking": false
            }
        ]
    }
     *
     * @param Request|null $request
     * @param string $locale
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function search(?Request $request, string $locale)
    {
        $valid = Validator($request->all(),[

            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'scope' => [ new IsArray, new Scope  ],
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        $scope = $request->get('scope');
        if (!is_array($scope)) $scope = json_decode($request->get('scope'), true) ?? null;

        return $this->offerService->search($page, $rowsPerPage, $searchKey, $scope, $sorting, true, $locale);
    }

    /**
     * @api {get} /api/{locale}/offer/{alias} Получение предложения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetOffer
     * @apiGroup PublicOffer
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 7,
        "title": "title ru",
        "description": "description ru",
        "published_at": "2019-04-12",
        "image": "/storage/offer/10osYGkN4iHfsZSP9aQ04CxZM42gIVAdnLtSxW7D.jpeg",
        "alias": "234",
        "has_booking": false,
        "seo": null,
        "images": []
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $alias)
    {
        $offer = $this->offerService->get(null, $locale, $alias);

        return response()->json($offer, 200);
    }
}
