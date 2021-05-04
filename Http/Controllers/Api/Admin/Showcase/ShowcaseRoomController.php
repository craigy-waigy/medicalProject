<?php

namespace App\Http\Controllers\Api\Admin\Showcase;

use App\Rules\IsArray;
use App\Services\ShowcaseRoomService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShowcaseRoomController extends Controller
{
    /**
     * @var ShowcaseRoomService
     */
    protected $showcaseRoomService;

    /**
     * ShowcaseRoomController constructor.
     */
    public function __construct()
    {
        $this->showcaseRoomService = new ShowcaseRoomService();
    }

    /**
     * @api {post} /api/admin/showcase/room Добавление номера  витрины
     * @apiVersion 0.1.0
     * @apiName SaveRoom
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} title_ru Краткое описание ru
     * @apiParam {string} title_en Краткое описание en
     * @apiParam {string} description_ru Описание номера ru
     * @apiParam {string} description_en Описание номера en
     * @apiParam {integer} capacity Вместимость номера
     * @apiParam {integer} capacity_max Вместимость максимальная номера
     * @apiParam {integer} capacity_min Вместимость минимальная номера
     * @apiParam {integer} square Площадь номера
     * @apiParam {string} interior_ru Интерьер ru
     * @apiParam {string} interior_en Интерьер en
     * @apiParam {numeric} [price] Цена
     * @apiParam {boolean} active Активен/не активен
     * @apiParam {file} [image] файл изображения превью номера
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 5,
        "title_ru": "TitleRu",
        "title_en": "TitleEn",
        "image": "/storage/showcase_room/X4eHEr23Dbnr11XKMl7kMGsu1mGfcFSN30faxpLk.jpeg",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "capacity": 1,
        "capacity_min": 2,
        "capacity_max": 10,
        "square": null,
        "interior_ru": "interiorRu",
        "interior_en": "interiorEn",
        "active": false,
        "price": null,
        "updated_at": "2019-04-04 08:32:52",
        "created_at": "2019-04-04 08:32:52",
        "id": 9
    }

     *
     *
     * @param Request $request
     * @return \App\Models\ShowcaseRoom|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function createRoom(Request $request)
    {
        $valid = Validator($request->all(),[
            'object_id' => 'required|integer',
            'title_ru' => 'required|max:255',
            'title_en' => 'required|max:255',
            'active' => 'boolean',
            'capacity' => 'integer',
            'capacity_min' => 'integer',
            'capacity_max' => 'integer',
            'square' => 'integer',
            'price' => 'numeric|nullable',
            'image' => 'file|image|mimes:jpeg,png,gif,jpg|max:8192',
        ],[
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не является изображением',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 8 мегабайт (8192 килобайт)',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('title_ru', 'title_en', 'description_ru', 'description_en', 'capacity',
            'capacity_min', 'capacity_max', 'active', 'square', 'interior_ru', 'interior_en', 'price', 'alean_id');
        $objectId = $request->get('object_id');

        $room = $this->showcaseRoomService->createRoom($data, $objectId, $request);

        return response()->json($room, 201);
    }

    /**
     * @api {post} /api/admin/showcase/room/{roomId} Редактирование номера витрины
     * @apiVersion 0.1.0
     * @apiName UpdateRoom
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} [title_ru] Краткое описание ru
     * @apiParam {string} [title_en] Краткое описание en
     * @apiParam {string} [description_ru] Описание номера ru
     * @apiParam {string} [description_en] Описание номера en
     * @apiParam {string} [capacity] Вместимость номера
     * @apiParam {string} [capacity_max] Вместимость максимальная номера
     * @apiParam {string} [capacity_min] Вместимость минимальная номера
     * @apiParam {boolean} [active] Активен/не активен
     * @apiParam {integer} [square] Площадь номера
     * @apiParam {numeric} [price] Цена
     * @apiParam {string} [interior_ru] Интерьер ru
     * @apiParam {string} [interior_en] Интерьер en
     * @apiParam {file} [image] файл изображения превью номера
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 5,
        "title_ru": "TitleRu",
        "title_en": "TitleEn",
        "image": "/storage/showcase_room/X4eHEr23Dbnr11XKMl7kMGsu1mGfcFSN30faxpLk.jpeg",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "capacity": 1,
        "capacity_min": 2,
        "capacity_max": 10,
        "square": null,
        "interior_ru": "interiorRu",
        "interior_en": "interiorEn",
        "active": false,
        "price": null,
        "updated_at": "2019-04-04 08:32:52",
        "created_at": "2019-04-04 08:32:52",
        "id": 9
    }
     *
     * @param Request $request
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function updateRoom(Request $request, int $roomId)
    {
        $valid = Validator($request->all(),[
            'object_id' => 'required|integer',
            'title_ru' => 'max:255',
            'title_en' => 'max:255',
            'active' => 'boolean',
            'capacity' => 'integer',
            'capacity_min' => 'integer',
            'capacity_max' => 'integer',
            'square' => 'integer',
            'price' => 'numeric|nullable',
            'image' => 'file|image|mimes:jpeg,png,gif,jpg|max:8192',
        ],[
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не является изображением',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 8 мегабайт (8192 килобайт)',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('title_ru', 'title_en', 'description_ru', 'description_en', 'capacity',
            'capacity_min', 'capacity_max', 'active', 'square', 'interior_ru', 'interior_en', 'price', 'alean_id');

        $room =  $this->showcaseRoomService->updateRoom($data, $roomId, $request);

        return response()->json($room, 200);
    }

    /**
     * @api {delete} /api/admin/showcase/room/{roomId} Удаление номера витрины
     * @apiVersion 0.1.0
     * @apiName DeleteRoom
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "showcase_room": [
            "Витрина удалена"
        ]
    }
     *
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function deleteRoom(int $roomId)
    {
        $this->showcaseRoomService->deleteRoom($roomId);

        return response()->json(['showcase_room' =>
            ['Витрина удалена']
        ], 200);
    }

    /**
     * @api {post} /api/admin/showcase/room/{roomId}/image Добавление изображения номера витрины
     * @apiVersion 0.1.0
     * @apiName AddImage
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file} image Файл изображения
     * @apiParam {string} [description] Описание номера
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "showcase_room_id": 5,
        "image": "/storage/showcase_room/X4eHEr23Dbnr11XKMl7kMGsu1mGfcFSN30faxpLk.jpeg",
        "description": "qweqweqwe",
        "id": 16
    }
     *
     * @param Request $request
     * @param $roomId
     * @return \App\Models\ShowcaseRoomImage|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function addImage(Request $request, $roomId)
    {
        $valid = Validator($request->all(),[

            'image' => 'required|file|image|mimes:jpeg,png,gif,jpg|max:8192',
            'description' => 'max:255|nullable'
        ],[
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не является изображением',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 8 мегабайт (8192 килобайт)',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $roomImage = $this->showcaseRoomService->addImage($request, $roomId);

        return response()->json($roomImage, 201);
    }

    /**
     * @api {delete} /api/admin/showcase/room/{roomId}/image/{imageId} Удаление изображения номера витрины
     * @apiVersion 0.1.0
     * @apiName DeleteImage
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "showcase_room": [
            "Изображение удалено"
        ]
    }
     *
     * @param int $roomId
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $roomId, int $imageId)
    {
        $deleted = $this->showcaseRoomService->deleteImage($roomId, $imageId);
        if ($deleted){
            return response()->json(['showcase_room' =>
                ['Изображение удалено']
            ], 200);
        } else {

            return response()->json(['showcase_room' =>
                ['Изображение не наайдено']
            ], 404);
        }
    }

    /**
     * @api {get} /api/admin/showcase/room/{roomId} Получение номера витрины
     * @apiVersion 0.1.0
     * @apiName GetRoom
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 9,
        "object_id": 5,
        "capacity": 1,
        "capacity_min": 2,
        "capacity_max": 10,
        "active": false,
        "created_at": "2019-04-04 08:32:52",
        "updated_at": "2019-04-04 08:33:30",
        "square": null,
        "title_ru": "TitleRu",
        "title_en": "TitleEn",
        "description_ru": "descriptionRu",
        "description_en": "descriptionEn",
        "interior_ru": "interiorRu",
        "interior_en": "interiorEn"
    }
     *
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoom(int $roomId)
    {
        $room = $this->showcaseRoomService->getRoom($roomId);

        return response()->json($room, 200);
    }

    /**
     * @api {get} /api/admin/showcase/room Поиск номеров витрины
     * @apiVersion 0.1.0
     * @apiName SearchRoom
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {integer} [object_id] ID объекта-санатория
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Массив сортировки {"title_ru": "asc", "updated_at": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 1,
        "items": [
            {
                "id": 9,
                "object_id": 5,
                "capacity": 1,
                "capacity_min": 2,
                "capacity_max": 10,
                "active": false,
                "created_at": "2019-04-04 08:32:52",
                "updated_at": "2019-04-04 08:33:30",
                "square": null,
                "title_ru": "TitleRu",
                "title_en": "TitleEn",
                "description_ru": "descriptionRu",
                "description_en": "descriptionEn",
                "interior_ru": "interiorRu",
                "interior_en": "interiorEn"
            }
        ]
    }
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchRoom(Request $request)
    {
        $valid = Validator($request->all(), [
           'page' => 'integer|nullable',
           'rowsPerPage' => 'integer|nullable',
           'searchKey' => 'string|nullable',
           'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey');
        $sorting = $request->get('sorting');

        if (!is_array($sorting)) $sorting = json_decode($sorting, true);

        $filter['object_id'] = $request->get('object_id');

        $rooms = $this->showcaseRoomService->searchRoom($page, $rowsPerPage, $searchKey, $filter, $sorting);

        return response()->json($rooms, 200);
    }

    /**
     * @api {get} /api/admin/showcase/room/{roomId}/images Получение изображений номеров
     * @apiVersion 0.1.0
     * @apiName GetRoomImages
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 24,
            "showcase_room_id": 9,
            "image": "image",
            "description": "desscription"
        },
        {
            "id": 23,
            "showcase_room_id": 9,
            "image": "image",
            "description": "desscription"
        },
        {
            "id": 22,
            "showcase_room_id": 9,
            "image": "image",
            "description": "desscription"
        },
        {
            "id": 21,
            "showcase_room_id": 9,
            "image": "image",
            "description": "desscription"
        }
    ]
     *
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getImages(int $roomId)
    {
        $images = $this->showcaseRoomService->getImages($roomId);

        return response()->json($images, 200);
    }

    /**
     * @api {put} /api/admin/showcase/room/image-sorting/{showcaseRoomId} изменение порядка изображений
     * @apiVersion 0.1.0
     * @apiName SortingRoomImages
     * @apiGroup ShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} imageIds   ID Изображений в порядке сортировки, [20, 19, 18, 17]

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "imageIds": [1,2]
    }
     *
     * @param Request $request
     * @param int $roomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function sortingImage(Request $request, int $roomId)
    {
        $valid = Validator($request->all(), [
           'imageIds' => ['required', new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $imageIds = $request->get('imageIds');

        $this->showcaseRoomService->sorting($imageIds, $roomId);

        return response()->json($request->only( 'imageIds' ), 200);
    }
}
