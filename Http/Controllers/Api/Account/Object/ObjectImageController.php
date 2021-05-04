<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Services\ObjectImageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ObjectImageController extends Controller
{
    /**
     * @var ObjectImageService
     */
    protected $objectImageService;

    /**
     * ObjectImageController constructor.
     */
    public function __construct()
    {
        $this->objectImageService = new ObjectImageService();
    }

    /**
     * @api {post} /api/account/object/image добавление изображения к объекту
     * @apiVersion 0.1.0
     * @apiName CreateObjectImage
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file} image Файл изображения
     * @apiParam {String} [description]     Описание изображения

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2926,
        "sorting_rule": null,
        "description": "werqwerqwer",
        "is_main": false,
        "image": "/storage/object_gallery/njAX3lx8jFDh3aJHTMJHwynw195Y0HObct7YEPHu.jpeg",
        "moderation": {
            "status": 2,
            "message": null
        }
    }

     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function saveImage(Request $request)
    {
        $valid = validator($request->only('object_id', 'image', 'description' ), [
            'image' => 'required|file|image|mimes:jpeg,png|max:5128',
            'description' => 'string|max:255|nullable',
        ], [
            'image.required' =>     'Изображение не отправлено',
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не содержит изображения',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 5 мегабайт',
            'description.string' => 'Описание должно быть текстом',
            'description.max' =>    'Максимальная длинна описания 255 символов',
        ]);

        if ($valid->fails()) return response($valid->errors(), 400);

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        $image = $this->objectImageService->create($request, $objectId, false);

        return response($image, 200);
    }

    /**
     * @api {get} /api/account/object/images Получение изображений объекта
     * @apiVersion 0.1.0
     * @apiName GetObjectImages
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 2295,
            "sorting_rule": 0,
            "description": null,
            "is_main": false,
            "image": "/storage/object_gallery/qeoLAqb8rwIhsgQY.jpeg",
            "moderation": {
                "status": 1,
                "message": null
            }
        },
            {
            "id": 2297,
            "sorting_rule": 1,
            "description": null,
            "is_main": false,
            "image": "/storage/object_gallery/1XAGNYKsb0GlVsh7.jpeg",
            "moderation": {
                "status": 1,
                "message": null
            }
        }
    ]
     *
     *
     * @return \Illuminate\Support\Collection
     * @throws ApiProblemException
     */
    public function getImages()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);

        $objectId = Auth::user()->object->id;
        return $this->objectImageService->getImages($objectId);
    }

    /**
     * @api {put} /api/account/object/image/{imageId} изменение описания к изображению
     * @apiVersion 0.1.0
     * @apiName UpdateObjectImage
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} description     описание изображения

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2926,
        "sorting_rule": null,
        "description": "werqwerqwer",
        "is_main": false,
        "image": "/storage/object_gallery/njAX3lx8jFDh3aJHTMJHwynw195Y0HObct7YEPHu.jpeg",
        "moderation": {
            "status_id": 2,
            "message": null
        }
    }
     *
     * @param Request $request
     * @param int $imageId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function updateImage(Request $request, int $imageId)
    {
        $valid = validator($request->all(), [
            'description' => 'required|string|max:255',
        ], [
            'description.required' => 'Описание не предоставлено',
            'description.string' => 'Описание должно быть текстом',
            'description.max' =>    'Максимальная длинна описания 255 символов',
        ]);

        if ($valid->fails()) return response($valid->errors(), 400);
        $data = $request->only( 'description');

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет прикрепленного объекта', 422);
        $data['object_id'] = Auth::user()->object->id;

        $image =  $this->objectImageService->update($data, $imageId);

        return response()->json($image, 200);
    }

    /**
     * @api {delete} /api/account/object/image{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteObjectImage
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objectImage": [
            "Изображение успешно удалено"
        ]
    }
     *
     *
     * @param int $imageId
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage(int $imageId)
    {
        $objectId = Auth::user()->object->id;
        $success = $this->objectImageService->delete($imageId, $objectId);

        if ($success){

            return response()->json(['objectImage' => [
                'Изображение успешно удалено'
            ] ], 200);
        } else {

            return response()->json(['objectImage' => [
                'Изображение не найдено'
            ] ], 404);
        }
    }

    /**
     * @api {put} /api/account/object/image-sorting изменение порядка изображений
     * @apiVersion 0.1.0
     * @apiName SortingObjectImage
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {array} imageIds   ID Изображений в порядке сортировки, [20, 19, 18, 17]

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objectImage": [
            "Изображения отсортированы"
        ]
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function sorting(Request $request)
    {
        $valid = validator($request->only( 'imageIds' ), [
            'imageIds' => 'required',
        ], [
            'imageIds.required' => 'Не отправлен порядок сортировки',
        ]);

        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }
        $objectId = Auth::user()->object->id;
        $imageIds = $request->get('imageIds');

        $this->objectImageService->sorting($imageIds, $objectId);

        return response()->json(['objectImage' => [
            'Изображения отсортированы'
        ] ], 200);
    }

    /**
     * @api {put} /api/account/object/image-main/{imageId} Установление главного изображения
     * @apiVersion 0.1.0
     * @apiName MainObjectImage
     * @apiGroup AccountObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     *

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "objectImage": [
            "Изображение установлено главным"
        ]
    }

     * @param int $imageId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function setMain(int $imageId)
    {
        $objectId = Auth::user()->object->id;

        $this->objectImageService->setMain($objectId, $imageId);

        return response()->json(['objectImage' => [
            'Изображение установлено главным'
        ] ], 200);
    }
}
