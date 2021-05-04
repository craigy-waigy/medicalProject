<?php

namespace App\Http\Controllers\Api\Account;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Rules\Scope;
use App\Services\OfferService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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
     * @api {get} /api/{locale}/account/offer Получение и поиск предложений (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchOffer
     * @apiGroup Account
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 5,
                "title": "title ru",
                "description": "description ru",
                "image": "/storage/offer/38frokoCmGFvBUtelppeAZbTVmzy8asoGveoiz3R.jpeg",
                "alias": "testurldsdcsdc"
            }
        ]
    }
     * @throws ApiProblemException
     * @param Request|null $request
     * @param string|null $locale
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
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
        is_null(Auth::user()->role->scope['slug']) ?
            $scope = null :
            $scope = [Auth::user()->role->scope['slug']];
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->offerService->search($page, $rowsPerPage, $searchKey, $scope, $sorting, true, $locale);
    }

    /**
     * @api {get} /api/{locale}/account/offer/{alias} Получение предложения (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetOffer
     * @apiGroup Account
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 4,
        "title": "title ru",
        "description": "description ru",
        "image": null,
        "alias": "rter",
        "seo": {
            "id": 34657,
            "offer_id": 4,
            "h1": null,
            "title": null,
            "url": "rter",
            "meta_description": null,
            "meta_keywords": null
        },
        "images": [
            {
                "id": 10,
                "offer_id": 4,
                "image": "/storage/offer/UI2b8uW1HC7brNfjwPd8MZpKVcOtMQBKZdLhgiyI.jpeg",
                "description": null
            },
            {
                "id": 9,
                "offer_id": 4,
                "image": "/storage/offer/VFlEzZX6mqJ1U42Mre861llNO3ZNaRixDLB56Uhp.jpeg",
                "description": null
            },
            {
                "id": 8,
                "offer_id": 4,
                "image": "/storage/offer/TxPfbU48pYeIez0wqA1JFCW3V9VHl7hr4phyI9ZG.jpeg",
                "description": null
            }
        ]
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function get(string $locale, string $alias)
    {
        $offer = $this->offerService->get(null, $locale, $alias);

        return response()->json($offer, 200);
    }
}
