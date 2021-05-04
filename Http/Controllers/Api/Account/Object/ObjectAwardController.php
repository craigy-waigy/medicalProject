<?php

namespace App\Http\Controllers\Api\Account\Object;

use App\Exceptions\ApiProblemException;
use App\Models\ObjectPlace;
use App\Services\ObjectAwardService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ObjectAwardController extends Controller
{
    protected $objectAwardService;

    public function __construct()
    {
        $this->objectAwardService = new ObjectAwardService();
    }

    /**
     * @api {post} /api/account/object/award добавление изображения награды
     * @apiVersion 0.1.0
     * @apiName CreateObjectAward
     * @apiGroup AccountObjectAward
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {file} image Файл изображения
     * @apiParam {String} [description]     описание награды

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 33,
        "image": "/storage/awards/dlkBOIPdjArHPvTwpWnhY26YEbOX6WsNizeRAzF9.jpeg",
        "description": "tratatushki-tratata",
        "updated_at": "2019-05-24 06:33:38",
        "created_at": "2019-05-24 06:33:38",
        "id": 1
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function store(Request $request)
    {
        $valid = validator($request->only('image', 'description' ), [
            'image' => 'required|file|image|mimes:jpeg,png|max:5128|nullable',
        ], [

            'image.required' =>     'Изображение не отправлено',
            'image.file' =>         'Изображение должно быть файлом',
            'image.image' =>        'Выбранный файл не содержит изображения',
            'image.mimes' =>        'Изображение должно быть формата *.jpeg или *.png',
            'image.max' =>          'Максимальный размер изображения 5 мегабайт',
        ]);

        if ($valid->fails()) return response($valid->errors(), 400);

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 422);
        $objectId = Auth::user()->object->id;

        $award = $this->objectAwardService->create($request, $objectId);

        return response($award, 200);
    }

    /**
     * @api {put} /api/account/object/award/{imageId} изменение описания к изображению награды
     * @apiVersion 0.1.0
     * @apiName UpdateObjectAward
     * @apiGroup AccountObjectAward
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} description     описание изображения награды

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "object_id": 33,
        "image": "/storage/awards/dlkBOIPdjArHPvTwpWnhY26YEbOX6WsNizeRAzF9.jpeg",
        "description": "tratatushki-tratata",
        "updated_at": "2019-05-24 06:33:38",
        "created_at": "2019-05-24 06:33:38",
        "id": 1
    }
     *
     * @param Request $request
     * @param int $awardId
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, int $awardId)
    {
        $valid = validator($request->only('image', 'description' ), [
            'description' => 'required',
        ], [

            'description.required' =>     'Необходимо предоставить описание награды',

        ]);

        if ($valid->fails()) return response($valid->errors(), 404);

        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 422);
        $objectId = Auth::user()->object->id;

        $data = $request->only('description');

        $award = $this->objectAwardService->update($data, $awardId, $objectId);

        return response()->json($award, 200);
    }

    /**
     * @api {get} /api/account/object/awards Получение изображений наград
     * @apiVersion 0.1.0
     * @apiName GetObjectAward
     * @apiGroup AccountObjectAward
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 7,
            "image_link": "/backend/object_awards/14_4aF80LKdGMSiu4zDaJAS.jpg",
            "description": "tratata"
        },
        {
            "id": 8,
            "image_link": "/backend/object_awards/14_lDsBdhNmB3RTxHfJw3WA.jpg",
            "description": "tratata"
        },
        {
            "id": 9,
            "image_link": "/backend/object_awards/14_yodUlUn5hxNM0yDz9wLk.jpg",
            "description": "tratata"
        },
        {
            "id": 10,
            "image_link": "/backend/object_awards/14_xDqsddKjDxFPtg417vh7.jpg",
            "description": "tratata"
        }
    ]
     *
     *
     * @return \Illuminate\Support\Collection
     * @throws ApiProblemException
     */
    public function get()
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 422);
        $objectId = Auth::user()->object->id;

        return $this->objectAwardService->get($objectId);
    }

    /**
     * @api {delete} /api/account/object/image{imageId} Удаление изображения награды
     * @apiVersion 0.1.0
     * @apiName DeleteObjectAward
     * @apiGroup AccountObjectAward
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
     * @param int $awardId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function delete(int $awardId)
    {
        if (is_null(Auth::user()->object))
            throw new ApiProblemException('У пользователя нет объекта', 422);
        $objectId = Auth::user()->object->id;

        $this->objectAwardService->delete($awardId, $objectId);

        return response()->json(['objectImage' => [
            'Награда успешно удалена'
        ] ], 200);
    }
}
