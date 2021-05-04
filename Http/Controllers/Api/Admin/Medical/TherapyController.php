<?php

namespace App\Http\Controllers\Api\Admin\Medical;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\TherapyService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TherapyController extends Controller
{
    protected $therapyService;

    public function __construct()
    {
        $this->therapyService = new TherapyService();
    }

    /**
     * @api {get} /api/admin/therapy Получение и поиск мет. лечения
     * @apiVersion 0.1.0
     * @apiName SearchTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {integer} [disease_id] Идентификатор заболевания
     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc", "diseases_count": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 301,
        "items": [
            {
                "id": 1,
                "name_ru": "Сбор анамнеза и жалоб общетерапевтический",
                "name_en": ""
                "active": true,
                "diseases_count": "9"
            },
            {
                "id": 2,
                "name_ru": "Визуальный осмотр общетерапевтический",
                "name_en": ""
                "active": true,
                "diseases_count": "9"
            },
            {
                "id": 3,
                "name_ru": "Пальпация общетерапевтическая",
                "name_en": ""
                "active": true,
                "diseases_count": "9"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function index(?Request $request)
    {
        $valid = Validator($request->only('page', 'rowsPerPage', 'searchKey'),[
            'disease_id' => 'integer|nullable',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $filterParams = $request->only('disease_id');
        $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->therapyService->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, null);
    }

    /**
     * @api {post} /api/admin/therapy Сохранение нового мет. лечения
     * @apiVersion 0.1.0
     * @apiName AddTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  name_ru Название
     * @apiParam {String}  name_en Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [source_ru] источник на англ.
     * @apiParam {string}  [source_en] источник на англ.
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name_ru": "nameRu",
        "name_en": "nameEn",
        "desc_ru": "descriptionRu",
        "desc_en": "descriptionEn",
        "source_ru": "sourceRuRu",
        "source_en": "sourceEnEnEn",
        "active": true,
        "id": 327
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function createTherapy(Request $request)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'active' => 'boolean',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $data = $request->only('name_ru', 'name_en', 'desc_ru', 'desc_en', 'source_ru', 'source_en');
        $therapy = $this->therapyService->createTherapy($data);

        return response()->json($therapy, 200);
    }

    /**
     * @api {put} /api/admin/therapy/{therapyId} Редактирование мет. лечения
     * @apiVersion 0.1.0
     * @apiName EditTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String}  [name_ru] Название
     * @apiParam {String}  [name_en] Название
     * @apiParam {string}  [description_ru] описание на русском
     * @apiParam {string}  [description_en] описание на англ.
     * @apiParam {string}  [source_ru] источник на англ.
     * @apiParam {string}  [source_en] источник на англ.
     * @apiParam {array}  [diseases] Массив идентификаторов заболеваний [23,323,43,54]
     * @apiParam {array} [tags_ru] Тэги для поиска RU
     * @apiParam {array} [tags_en] Тэги для поиска EN
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name_ru": "nameRu",
        "name_en": "nameEn",
        "desc_ru": "descriptionRu",
        "desc_en": "descriptionEn",
        "source_ru": "sourceRuRu",
        "source_en": "sourceEnEnEn",
        "active": true,
        "id": 327,
        "tags_ru": [],
        "tags_en": []
    }
     * @param Request $request
     * @param int $therapyId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function editTherapy(Request $request, int $therapyId)
    {
        $valid = Validator($request->all(), [
            'name_ru' => 'string|max:255',
            'name_en' => 'string|max:255',
            'active' => 'boolean',
            'diseases' => [ new IsArray ],
            'tags_ru' => [ new IsArray ],
            'tags_en' => [ new IsArray ],
        ]);

        if ($valid->fails()) return response($valid->errors(), 400);

        $data = $request->only('name_ru', 'name_en', 'desc_ru', 'desc_en', 'source_ru', 'source_en', 'diseases',
            'active', 'tags_ru', 'tags_en');
        $therapy = $this->therapyService->editTherapy($data, $therapyId);

        return response()->json($therapy, 200);
    }

    /**
     * @api {get} /api/admin/therapy/{therapyId} Получение мет. лечения
     * @apiVersion 0.1.0
     * @apiName GetTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 239,
        "name_ru": "Электрофорез лекарственных средств при болезнях печени и желчевыводящих путей",
        "name_en": "",
        "desc_ru": "нет определения",
        "desc_en": "",
        "source_ru": "",
        "source_en": "",
        "active": true,
        "alias": "elektroforez-lekarstvennykh-sredstv-pri-boleznyakh-pecheni-i-zhelchevyvodyashchikh-putey-239",
        "medical_profiles": [
            {
                "id": 15,
                "alias": "bolezni-oporno-dvigatelnogo-apparata-15",
                "name_ru": "Болезни опорно-двигательного аппарата",
                "name_en": "Diseases of the musculoskeletal system"
            },
            {
                "id": 22,
                "alias": "testovyy-profil-22",
                "name_ru": "Тестовый профиль",
                "name_en": "test profile"
            },
            {
                "id": 10,
                "alias": "bolezni-zheludochno-kishechnogo-trakta-10",
                "name_ru": "Болезни желудочно-кишечного тракта",
                "name_en": "Diseases of the gastrointestinal tract"
            }
        ],
        "seo": {
            "therapy_id": 239,
            "for": "therapy-page",
            "h1_ru": null,
            "h1_en": null,
            "title_ru": null,
            "title_en": null,
            "url": "elektroforez-lekarstvennykh-sredstv-pri-boleznyakh-pecheni-i-zhelchevyvodyashchikh-putey-239",
            "meta_description_ru": null,
            "meta_description_en": null,
            "meta_keywords_ru": null,
            "meta_keywords_en": null
        },
        "diseases": [
            {
                "id": 2,
                "parent": 1,
                "name_ru": "КИШЕЧНЫЕ ИНФЕКЦИИ",
                "name_en": "Intestinal infectious diseases"
            },
            {
                "id": 3,
                "parent": 2,
                "name_ru": "Холера",
                "name_en": "Cholera"
            }
        ],
        "images": [],
        "tags_ru": [],
        "tags_en": []
    }
     *
     * @param int $therapyId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getTherapy(int $therapyId)
    {
        return $this->therapyService->getTherapy($therapyId);
    }

    /**
     * @api {delete} /api/admin/therapy/{therapyId} Удаление мет. лечения
     * @apiVersion 0.1.0
     * @apiName DeleteTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "therapy": [
            "Метод лечения удален"
        ]
    }
     *
     * @param int $therapyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTherapy(int $therapyId)
    {
        $deleted = $this->therapyService->deleteTherapy($therapyId);
        if ($deleted){

            return response()->json(['therapy' =>[
                'Метод лечения удален'
            ]], 200);
        } else {
            return response()->json(['therapy' =>[
                'Метод лечения не найден'
            ]], 404);
        }
    }

    /**
     * @api {post} /api/admin/therapy/{therapyId}/image Добавление изображения
     * @apiVersion 0.1.0
     * @apiName AddImageTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file}  image Файл изображения
     * @apiParam {string}  [description] Название изображения
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "therapy_id": 324,
        "image": "/storage/therapy/TkFwVIczO3SNVvkpQj8WSquVnSorQmNud8D3CSSz.jpeg",
        "description": "test",
        "id": 6
    }
     * @param Request $request
     * @param int $therapyId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function addTherapyImage(Request $request, int $therapyId)
    {
        $valid = Validator($request->all( ),[
            'image' => 'file|image|max:5128',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        $image = $this->therapyService->addTherapyImage($request, $therapyId);
        return response()->json($image, 200);
    }

    /**
     * @api {get} /api/admin/therapy/{therapyId}/images Получение изображений
     * @apiVersion 0.1.0
     * @apiName GetImagesTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 8,
            "therapy_id": 324,
            "description": "test",
            "image": "/storage/therapy/rzwVY7UFS8iYuUqIjpKLLXA3KajEEdQJrlxW9pyD.jpeg"
        },
        {
            "id": 7,
            "therapy_id": 324,
            "description": "test",
            "image": "/storage/therapy/prIEurbkKvIrXCxN2rgWoCoQsTOZGwwNX7SoJ1JY.jpeg"
        },
        {
            "id": 6,
            "therapy_id": 324,
            "description": "test",
            "image": "/storage/therapy/TkFwVIczO3SNVvkpQj8WSquVnSorQmNud8D3CSSz.jpeg"
        }
    ]
     *
     * @param int $therapyId
     * @return mixed
     */
    public function getTherapyImages(int $therapyId)
    {
        return $this->therapyService->getTherapyImages($therapyId);
    }

    /**
     * @api {delete} /api/admin/therapy/{therapyId}/image/{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteImageTherapy
     * @apiGroup AdminTherapy
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "therapy": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $therapyId
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteTherapyImage(int $therapyId,  int $imageId)
    {
        $deleted = $this->therapyService->deleteTherapyImage($therapyId, $imageId);

        if ($deleted){

            return response()->json(['news' =>[
                'Изображение удалено'
            ]], 200);
        } else {
            return response()->json(['news' =>[
                'Изображение не найдено'
            ]], 404);
        }
    }
}
