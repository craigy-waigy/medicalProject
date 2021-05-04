<?php

namespace App\Http\Controllers\Api\Admin\Object;

use App\Exceptions\ApiProblemException;
use App\Services\ObjectImageService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ObjectImageController extends Controller
{
    protected $objectImageService;

    public function __construct()
    {
        $this->objectImageService = new ObjectImageService();
    }

    /**
     * @api {post} /api/admin/object/image добавление изображения к объекту
     * @apiVersion 0.1.0
     * @apiName CreateObjectImage
     * @apiGroup AdminObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file} [image] Файл изображения
     * @apiParam {string} [link_to_file] ссылка на файл изображения
     * @apiParam {integer} object_id ID объекта
     * @apiParam {String} [description]     описание изображения

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 715,
        "sorting_rule": null,
        "description": null,
        "is_main": false,
        "image": "/storage/object_gallery/diI80h8wmDowFKdte9fyztWh1hQLEDW42uNFlMno.png",
        "moderation": {
            "status_id": 3,
            "message": null
        }
    }

     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function create(Request $request)
    {
        $valid = validator($request->only('object_id', 'image', 'description', 'link_to_file'), [
            'object_id' => 'required|integer',
            'image' => 'required_without:link_to_file|file|image|mimes:jpeg,png,gif,jpg|max:8192',
            'link_to_file' => 'required_without:image',
            'description' => 'string|max:255|nullable',
        ], [
            'object_id.integer' =>  'ID объекта должен быть целочисленным',
            'image.required' =>     'Изображение не отправлено',
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не является изображением',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 8 мегабайт (8192 килобайт)',
            'description.string' => 'Описание должно быть текстом',
            'description.max' =>    'Максимальная длинна описания 255 символов',
        ]);

        if ($valid->fails()) return response($valid->errors(),400);

        if ($request->hasFile('image')) {
            $image = $this->objectImageService->create($request, null, true);
        } else {
            $image = $this->objectImageService->createByLink($request, null, true);
        }

        return response()->json($image, 200);
    }

    /**
     * @api {put} /api/admin/object/image/{imageId} Редактирование и модерация изображений
     * @apiVersion 0.1.0
     * @apiName UpdateObjectImage
     * @apiGroup AdminObjectImage
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {Integer} object_id     ID объекта - санатория
     * @apiParam {String} [description]     Описание изображения
     * @apiParam {Integer} [moderation]     Модерация {"approve": false, "message": "Причина отклонения"}

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2278,
        "sorting_rule": 1,
        "description": null,
        "is_main": false,
        "image": "/storage/object_gallery/uAE3NV8exP6oPXhD.jpeg",
        "moderation": {
            "status_id": 4,
            "message": "отклонено модератором"
        }
    }
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, int $id)
    {
        $valid = validator($request->only('object_id', 'description' ), [
            'object_id' => 'required|integer',
            'description' => 'string|max:255|nullable',
            'moderation_status' => 'integer|nullable',
            'moderator_message' => 'string|nullable',
        ], [
            'object_id.required' => 'ID объекта не отправлен',
            'object_id.integer' =>  'ID объекта должен быть целочисленным',
            'description.required' => 'Описание не предоставлено',
            'description.string' => 'Описание должно быть текстом',
            'description.max' =>    'Максимальная длинна описания 255 символов',
        ]);

        if ($valid->fails()) return response($valid->fails(),400);

        $data = $request->only('object_id', 'description', 'moderation');

        $image = $this->objectImageService->update($data, $id);

        return response()->json($image, 200);
    }

    /**
     * @api {delete} /api/admin/object/image{imageId} Удаление изображения
     * @apiVersion 0.1.0
     * @apiName DeleteObjectImage
     * @apiGroup AdminObjectImage
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
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(int $id)
    {
        $success = $this->objectImageService->delete($id);

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
     * @api {put} /api/admin/object/image-sorting/{objectId} изменение порядка изображений
     * @apiVersion 0.1.0
     * @apiName SortingObjectImage
     * @apiGroup AdminObjectImage
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
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function sorting(Request $request, int $id)
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

        $imageIds = $request->get('imageIds');

        $this->objectImageService->sorting($imageIds, $id);

        return response()->json($request->only( 'imageIds' ), 200);
    }

    /**
     * @api {put} /api/admin/object/image-main/{imageId} Установление главного изображения
     * @apiVersion 0.1.0
     * @apiName MainObjectImage
     * @apiGroup AdminObjectImage
     *
     * @apiParam {integer} object_id   ID Оъекта

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 5
    }
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function setMain(Request $request, int $id)
    {
        $valid = validator($request->only( 'object_id' ), [
            'object_id' => 'required|integer',
        ], [
            'object_id.required' => 'ID объекта не отправлен',
            'object_id.integer' =>  'ID объекта должен быть целочисленным',
        ]);

        if ($valid->fails()){
            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];
            return response($response, $response['status']);
        }

        $objectId = $request->get('object_id');

        $this->objectImageService->setMain($objectId, $id);

        return response()->json( $request->only('object_id'), 200);
    }

    /**
     * @api {get} /api/admin/object/{objectId}/images Получение изображений объекта
     * @apiVersion 0.1.0
     * @apiName GetObjectImages
     * @apiGroup AdminObjectImage
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
                "status_id": 1,
                "message": null
            }
        }
    ]
     *
     *
     * @param int $id
     * @return \Illuminate\Support\Collection
     */
    public function getImages(int $id)
    {
        return $this->objectImageService->getImages($id);
    }
}
