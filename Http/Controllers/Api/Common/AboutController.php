<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\IsArray;
use App\Services\AboutService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    /**
     * @var AboutService
     */
    protected $aboutService;

    /**
     * AboutController constructor.
     */
    public function __construct()
    {
        $this->aboutService = new AboutService();
    }

    /**
     * @api {get} /api/{locale}/about  Получение дефолтной страницы "О проекте" (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName SearchAboutPage
     * @apiGroup PublicAboutPage
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "parent": 0,
        "title": "TitleRu",
        "about": "AboutRu",
        "alias": "alias-2",
        "publish_date": null,
        "seo": {
            "id": 34493,
            "about_id": 2,
            "h1": null,
            "title": null,
            "url": "qweqw",
            "meta_description": null,
            "meta_keywords": null
        }
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function search(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $abouts = $this->aboutService->getDefault( $locale );

        return response()->json($abouts, 200);
    }

    /**
     * @api {get} /api/{locale}/about/{alias}  Получение раздела "О проекте" (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetAboutPage
     * @apiGroup PublicAboutPage
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "parent": 0,
        "title": "TitleRu",
        "about": "AboutRu",
        "alias": "alias-2",
        "publish_date": null,
        "seo": {
            "id": 34493,
            "about_id": 2,
            "h1": null,
            "title": null,
            "url": "qweqw",
            "meta_description": null,
            "meta_keywords": null
        }
    }
     *
     * @param string $locale
     * @param string $alias
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function get(string $locale, string $alias)
    {
        $about = $this->aboutService->get(null, $locale, $alias);

        return response()->json($about, 200);
    }

    /**
     * @api {get} /api/{locale}/about/tree  Получение дерева разделов "О проекте" (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName TreeAboutPage
     * @apiGroup PublicAboutPage
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 2,
            "parent": 0,
            "title": "TitleRu",
            "alias": "alias-2"
        },
        {
            "id": 3,
            "parent": 0,
            "title": "TitleRu",
            "alias": "alias-3"
        },
        {
            "id": 4,
            "parent": 0,
            "title": "TitleRu",
            "alias": "alias-4"
        }
    ]
     *
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getTree(string $locale)
    {
        $tree = $this->aboutService->getTree($locale);

        return response()->json($tree, 200);
    }
}
