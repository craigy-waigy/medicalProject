<?php

namespace App\Http\Controllers\Api\RU;

use App\Services\MainPageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MainPageController extends Controller
{
    /**
     * @var MainPageService
     */
    protected $mainPageService;

    /**
     * MainPageController constructor.
     */
    public function __construct()
    {
        $this->mainPageService = new MainPageService();
    }

    /**
     * @api {get} /api/{locale}/main-page Получение данных главной страницы (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetMaimPagePublic
     * @apiGroup MaimPage
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "main_banners": [
            {
                "id": 6,
                "description": "описаниеrer",
                "sorting": 2,
                "banner_resolutions": {
                    "mobile": "/storage/main_page/pNBr8FQZ5FElvmgR5N4uRu0MNZN6wdQn4jJSWjcN.jpeg",
                    "tablet": "/storage/main_page/Kqn9sxVoJXH4InYYLD0otxSx3EftAsLkTMNhcRXS.jpeg",
                    "desktop": "/storage/main_page/ikq4vsuA2FHonv1KuyyJ4M6wktcDMFZLbi0Nccde.jpeg"
                }
            },
            {
                "id": 7,
                "description": "описаниеrer",
                "sorting": 2,
                "banner_resolutions": {
                    "mobile": null,
                    "tablet": "/storage/main_page/Dbh0Ny2UkXV9JVxoU4E7ECZGFfLi8aQ7LzV2mvV3.jpeg",
                    "desktop": null
                }
            },
            {
                "id": 8,
                "description": "описаниеrer",
                "sorting": 2,
                "banner_resolutions": {
                    "mobile": "/storage/main_page/GotyyILZzpgZhigEQEScZb2iYzyjP6g5KPMBmBHg.jpeg",
                    "tablet": null,
                    "desktop": null
                }
            }
        ],
        "right_banners": {
            "banners": [
                {
                    "id": 30,
                    "meta": "right_banner",
                    "content": "/storage/main_page/RsNIRBemHaMMaFj1vQLCYgeYkRickrhLCwCje8ns.jpeg",
                    "description": "desc",
                    "sorting": 0
                },
                {
                    "id": 29,
                    "meta": "right_banner",
                    "content": "/storage/main_page/lATVlUTJifGFQpTz5R1BEAOHgSk3iCEgI4vaHY1W.jpeg",
                    "description": "desc",
                    "sorting": 1
                },
                {
                    "id": 28,
                    "meta": "right_banner",
                    "content": "/storage/main_page/RFTgbTV7btAb2eLMhtUOZkZB0Xrw7wV1QgCtiPmK.jpeg",
                    "description": "desc",
                    "sorting": 2
                },
                {
                    "id": 27,
                    "meta": "right_banner",
                    "content": "/storage/main_page/6ml0r3SSdPuyKOnUMcJ6hZlFwdr4Z3G0logC6CQ3.jpeg",
                    "description": "desc",
                    "sorting": null
                }
            ],
            "links": [
                {
                "meta": "right_banner_link",
                "content": "right_banner_link_en"
                }
            ]
        },
        "content": {
            "how_it_work": "how it works",
            "video_link": "link_en",
            "it_is_useful": "it_is_useful_en",
            "it_is_reliable": "it_is_reliable_en",
            "it_is_profitable": "it_is_profitable_en"
        },
        "statistic": {
            "objects": 5,
            "regions": 2,
            "therapies": 0
        },
        "seo": {
            "h1": "это h1",
            "title": "title",
            "url": "urlцукецукцуццsdfsdfsdfsdscdcsdcsdcsdcsdcsdcsdcwerwerqwerqwerwerwerqwerqwewscssddfserqwertqwetqwetqwewedwewecewewedwefwefwefweqwwerqwerwqerewrwerqwerwer",
            "meta_description": "это для главной страницы",
            "meta_keywords": "keys"
        }
    }
     *
     * @return array
     */
    public function getMainPage()
    {
        return $this->mainPageService->getMainPageRU();
    }
}
