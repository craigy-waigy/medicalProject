<?php

namespace App\Http\Controllers\Api\Admin\MainPage;

use App\Models\MainPage;
use App\Services\MainPageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\MainPageMetaException;

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
     * @api {get} /api/admin/main-page Получение данных главной страницы
     * @apiVersion 0.1.0
     * @apiName GetMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "main_banners": [
            {
                "id": 6,
                "description_ru": "описаниеrer",
                "description_en": "descriptionser",
                "sorting": 2,
                "active": true,
                "banner_resolutions": {
                    "mobile": {
                        "content_en": "/storage/main_page/6k26BHMGre4hK30W89iZgyMsuacS2cbnC40RCI5Z.jpeg",
                        "content_ru": "/storage/main_page/pNBr8FQZ5FElvmgR5N4uRu0MNZN6wdQn4jJSWjcN.jpeg"
                    },
                    "tablet": {
                        "content_en": "/storage/main_page/B5AEQ3GC9OhiigjvOgqR3yyAQEuTLeTUiDaiTeNP.jpeg",
                        "content_ru": "/storage/main_page/Kqn9sxVoJXH4InYYLD0otxSx3EftAsLkTMNhcRXS.jpeg"
                    },
                    "desktop": {
                        "content_en": "/storage/main_page/SYXDz4iYjl1SgsYZsRc6zmbN4CoHx48yqHc13CzL.jpeg",
                        "content_ru": "/storage/main_page/ikq4vsuA2FHonv1KuyyJ4M6wktcDMFZLbi0Nccde.jpeg"
                    }
                },
                "created_at": "2019-02-28 12:49:13",
                "updated_at": "2019-02-28 12:55:29"
            },
            {
                "id": 7,
                "description_ru": "описаниеrer",
                "description_en": "descriptionser",
                "sorting": 2,
                "active": false,
                "banner_resolutions": {
                    "mobile": {
                        "content_en": null,
                        "content_ru": null
                    },
                    "tablet": {
                        "content_en": "/storage/main_page/wvmNGcbhkQxfkYZbkYclc0lwjac4ogNqNipE37Bh.jpeg",
                        "content_ru": "/storage/main_page/Dbh0Ny2UkXV9JVxoU4E7ECZGFfLi8aQ7LzV2mvV3.jpeg"
                    },
                    "desktop": {
                        "content_en": null,
                        "content_ru": null
                    }
                },
                "created_at": "2019-02-28 12:58:46",
                "updated_at": "2019-02-28 12:58:46"
            },
            {
                "id": 8,
                "description_ru": "описаниеrer",
                "description_en": "descriptionser",
                "sorting": 2,
                "active": false,
                "banner_resolutions": {
                    "mobile": {
                        "content_en": "/storage/main_page/62bH1gRoyHVS7eYNmupqCKRnS7DPqcuUVTbbAhbI.jpeg",
                        "content_ru": "/storage/main_page/GotyyILZzpgZhigEQEScZb2iYzyjP6g5KPMBmBHg.jpeg"
                    },
                    "tablet": {
                        "content_en": null,
                        "content_ru": null
                    },
                    "desktop": {
                        "content_en": null,
                        "content_ru": null
                    }
                },
                "created_at": "2019-02-28 12:58:53",
                "updated_at": "2019-02-28 12:58:53"
            }
        ],
        "right_banners": {
            "banners": [
                {
                    "id": 30,
                    "meta": "right_banner",
                    "active": true,
                    "content_ru": "/storage/main_page/RsNIRBemHaMMaFj1vQLCYgeYkRickrhLCwCje8ns.jpeg",
                    "content_en": "/storage/main_page/4YmPa67o49Oe7K6km6IsZpaNxZYUW9PAJ6QVNaoP.jpeg",
                    "description_ru": "desc",
                    "description_en": "csed",
                    "sorting": 0
                },
                {
                    "id": 29,
                    "meta": "right_banner",
                    "active": true,
                    "content_ru": "/storage/main_page/lATVlUTJifGFQpTz5R1BEAOHgSk3iCEgI4vaHY1W.jpeg",
                    "content_en": "/storage/main_page/oHPn7T0HFuNh3FHW78wO8a1nDofgUAa8xfuDTTst.jpeg",
                    "description_ru": "desc",
                    "description_en": "csed",
                    "sorting": 1
                },
                {
                    "id": 28,
                    "meta": "right_banner",
                    "active": true,
                    "content_ru": "/storage/main_page/RFTgbTV7btAb2eLMhtUOZkZB0Xrw7wV1QgCtiPmK.jpeg",
                    "content_en": "/storage/main_page/iH7vPdWUGzrG6obrVC5SSpO8jIDI4uRDJEaYp6im.jpeg",
                    "description_ru": "desc",
                    "description_en": "csed",
                    "sorting": 2
                },
                {
                    "id": 27,
                    "meta": "right_banner",
                    "active": true,
                    "content_ru": "/storage/main_page/6ml0r3SSdPuyKOnUMcJ6hZlFwdr4Z3G0logC6CQ3.jpeg",
                    "content_en": "/storage/main_page/v771MIJmmw8GOAqcMvPfhUFdTU0og3YIRixDVEfo.jpeg",
                    "description_ru": "desc",
                    "description_en": "csed",
                    "sorting": null
                }
                ],
                "links": [
                {
                    "meta": "right_banner_link",
                    "content_ru": "right_banner_link_ru_2",
                    "content_en": "right_banner_link_en"
                }
            ]
        },
        "content": {
            "how_it_work": {
                "content_ru": "Kak eto rabotaet",
                "content_en": "how it works"
            },
            "video_link": {
                "content_ru": "link_ru",
                "content_en": "link_en"
            },
            "it_is_useful": {
                "content_ru": "it_is_useful_ru",
                "content_en": "it_is_useful_en"
            },
            "it_is_reliable": {
                "content_ru": "it_is_reliable_ru",
                "content_en": "it_is_reliable_en"
            },
            "it_is_profitable": {
                "content_ru": "it_is_profitable_ru",
                "content_en": "it_is_profitable_en"
            }
        },
        "statistic": {
            "objects": 5,
            "regions": 2,
            "therapies": 0
        }
    }
     *
     * @return array
     */
    public function getMainPage()
    {
        return $this->mainPageService->getMainPage();
    }

    /**
     * @api {put} /api/admin/main-page Обновление данных главной страницы
     * @apiVersion 0.1.0
     * @apiName UpdateMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} meta Назначение (how_it_work - как это работает)
     * @apiParam {string} meta. Назначение (video_link - Ссылка на видео)
     * @apiParam {string} meta.. Назначение (it_is_useful - Это доступно)
     * @apiParam {string} meta... Назначение (it_is_reliable - Это полезно)
     * @apiParam {string} meta.... Назначение (it_is_profitable - Это выгодно)
     * @apiParam {string} meta..... Назначение (right_banner_link - Ссылка ведущая с правого баннера)
     * @apiParam {string} content_ru Содержание на русском
     * @apiParam {string} [content_en] Содержание на английском
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK

     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws MainPageMetaException
     */
    public function updateMainPage(Request $request)
    {
        $valid = Validator($request->all(),[
            'meta' => 'required|string',
            'content_ru' => 'required|string',
            'content_en' => 'string|nullable'
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $meta = $request->get('meta');
        if (!in_array($meta, [
                'how_it_work',
                'video_link',
                'it_is_useful',
                'it_is_reliable',
                'it_is_profitable',
                'right_banner_link']
        )) throw  new MainPageMetaException();

        return $this->mainPageService->updateMainPage($meta, $request->get('content_ru'), $request->get('content_en'));
    }

    /**
     *
     * @api {post} /api/admin/main-page/banner Добавление банера
     * @apiVersion 0.1.0
     * @apiName AddBannerMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} meta Назначение (main_banner_desktop - главный баннер для десктоп)
     * @apiParam {string} meta. Назначение (main_banner_tablet - главный баннер для планшетов)
     * @apiParam {string} meta.. Назначение (main_banner_mobile - главный баннер для мобилных)
     * @apiParam {string} [description_ru] Название на русском
     * @apiParam {string} [description_en] Название на английском
     * @apiParam {file} content_ru Баннер на русском
     * @apiParam {file} [content_en] Баннер на английском
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "description_ru": "описаниеrer",
        "description_en": "descriptionser",
        "sorting": "34",
        "active": false,
        "banner_resolutions": {
            "mobile": {
                "content_en": "/storage/main_page/zASrZj1XTiU2IipQvxlQnOxFmMrKSEnMV3oluivF.jpeg",
                "content_ru": "/storage/main_page/MbVjGN3zvO9DqRd77umQQ4kNUKOYowjLBhK0gnuh.jpeg"
            },
            "tablet": {
                "content_en": "/storage/main_page/TOBt0RrXE2gEKyErMF2CRZurw8pSAY6FmVC1f9vo.jpeg",
                "content_ru": "/storage/main_page/AUG23FvLKFMkDfpqF3Xuz5lZsdIM1ovBWYiX13q1.jpeg"
            },
            "desktop": {
                "content_en": "/storage/main_page/E3Q9dTyVK8GjzyzURROi6XdpDxsRXCWqh2aORPIf.jpeg",
                "content_ru": "/storage/main_page/d97yZYgsgqtFqjTJzNwdGKdQOXAwhPMHUOMufDnG.jpeg"
            }
        },
        "created_at": "2019-02-28 10:45:14",
        "updated_at": "2019-02-28 10:57:02"
    }
     *
     * @param Request $request
     * @return MainPage|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws MainPageMetaException
     */
    public function addBanner(Request $request)
    {
        $valid = Validator($request->all(),[
            'content_ru' => 'file|image',
            'content_en' => 'file|image|nullable',
            'description_ru' => 'required|string|max:255|nullable',
            'description_en' => 'required|string|max:255|nullable',
            'sorting' => 'integer',
            'meta' => 'string|nullable',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $meta = $request->get('meta') ?? null;
        if (!in_array($meta, [
            'main_banner_desktop',
            'main_banner_tablet',
            'main_banner_mobile',
            'right_banner',
            ]) && !is_null($meta))
            throw  new MainPageMetaException();

        $banner = $this->mainPageService->addBanner($request, $meta);

        return response()->json($banner, 200);
    }

    /**
     * @api {delete} /api/admin/main-page/banner/{bannerId} Удаление банера
     * @apiVersion 0.1.0
     * @apiName DeleteBannerMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "main-page": [
            "Банер удален"
        ]
    }
     *
     * @param int $bannerId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteBanner(int $bannerId)
    {
        $this->mainPageService->deleteBanner($bannerId);

        return response()->json(['main-page' =>[
            'Банер удален'
        ]], 200);
    }

    /**
     * @api {post} /api/admin/main-page/banner/{bannerId} Обновление банера
     * @apiVersion 0.1.0
     * @apiName EditBannerMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} meta Назначение (main_banner_desktop - главный баннер для десктоп)
     * @apiParam {string} meta. Назначение (main_banner_tablet - главный баннер для планшетов)
     * @apiParam {string} meta.. Назначение (main_banner_mobile - главный баннер для мобилных)
     *
     * @apiParam {string} [description_ru] Название на русском
     * @apiParam {string} [description_en] Название на английском
     * @apiParam {boolean} [active] Видимость
     * @apiParam {integer} [sorting] порядковый номер сортировки
     * @apiParam {file} [content_ru] Баннер на русском
     * @apiParam {file} [content_en] Баннер на английском
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "description_ru": "описаниеrer",
        "description_en": "descriptionser",
        "sorting": "34",
        "active": false,
        "banner_resolutions": {
            "mobile": {
                "content_en": "/storage/main_page/zASrZj1XTiU2IipQvxlQnOxFmMrKSEnMV3oluivF.jpeg",
                "content_ru": "/storage/main_page/MbVjGN3zvO9DqRd77umQQ4kNUKOYowjLBhK0gnuh.jpeg"
            },
            "tablet": {
                "content_en": "/storage/main_page/TOBt0RrXE2gEKyErMF2CRZurw8pSAY6FmVC1f9vo.jpeg",
                "content_ru": "/storage/main_page/AUG23FvLKFMkDfpqF3Xuz5lZsdIM1ovBWYiX13q1.jpeg"
            },
            "desktop": {
                "content_en": "/storage/main_page/E3Q9dTyVK8GjzyzURROi6XdpDxsRXCWqh2aORPIf.jpeg",
                "content_ru": "/storage/main_page/d97yZYgsgqtFqjTJzNwdGKdQOXAwhPMHUOMufDnG.jpeg"
            }
        },
        "created_at": "2019-02-28 10:45:14",
        "updated_at": "2019-02-28 10:57:02"
    }
     *
     * @param Request $request
     * @param $bannerId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \App\Exceptions\NotFoundException
     */
    public function updateBanner(Request $request, $bannerId)
    {
        $valid = Validator($request->all(),[
            'description_ru' => 'string|max:255|nullable',
            'description_en' => 'string|max:255|nullable',
            'active' => 'boolean',
            'sorting' => 'integer|nullable',
            'meta' => 'string',
            'content_ru' => 'file|image',
            'content_en' => 'file|image',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $banner = $this->mainPageService->updateBanner($request, $bannerId);

        return response()->json($banner, 200);
    }

    /**
     * @api {put} /api/admin/main-page/banners/sorting Сортировка баннеров
     * @apiVersion 0.1.0
     * @apiName SortingBannerMaimPage
     * @apiGroup MaimPage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {array} banners Массив Id баннеров в порядке сортировки, [21, 21, 32, 15]
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "banners": [21, 22, 32, 15]
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function sortingBanners(Request $request)
    {
        $valid = Validator($request->all(),[
            'banners' => 'required',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $banners = $request->get('banners');
        if (!is_array($banners)) return response()->json(['error' => 'banners должно быть массивом'], 400);

        $this->mainPageService->sortingBanner($banners);

        return response()->json($request->only('banners'), 200);

    }
}
