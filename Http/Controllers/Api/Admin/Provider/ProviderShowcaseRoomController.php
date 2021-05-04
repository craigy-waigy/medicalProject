<?php

namespace App\Http\Controllers\Api\Admin\Provider;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\ProviderShowcaseRoomService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProviderShowcaseRoomController extends Controller
{
    /**
     * @var ProviderShowcaseRoomService
     */
    protected $service;

    /**
     * ProviderShowcaseRoom constructor.
     */
    public function __construct()
    {
        $this->service = new ProviderShowcaseRoomService();
    }

    /**
     * @api {post} /api/admin/provider-showcase-room Создание связи номера витрины номеру у поставщика
     * @apiVersion 0.1.0
     * @apiName CreateProviderShowcaseRoom
     * @apiGroup AdminProviderShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  showcase_room_id идентификатор номера витрины в здравпродукте
     * @apiParam {integer}  provider_id идентификатор провайдера в здравпродукте
     * @apiParam {string}  provider_room_id идентификатор номера у поставщика
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "showcase_room_id": "228",
        "provider_id": "1",
        "provider_room_id": "sdfgdsgw4134",
        "id": 4
    }

     *
     * @param Request $request
     * @return \App\Models\ProviderShowcaseRoom|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws
     */
    public function create(Request $request)
    {
        $valid = Validator($request->all(),[
            'showcase_room_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'provider_room_id' => 'required|string',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        return $this->service->create($request);
    }

    /**
     * @api {get} /provider-showcase-room/{showcaseRoomId} Получение связи номера витрины номеру у поставщика
     * @apiVersion 0.1.0
     * @apiName GetProviderShowcaseRoom
     * @apiGroup AdminProviderShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "showcase_room_id": 228,
        "provider_id": 1,
        "provider_room_id": "room123"
    },
    {
        "id": 3,
        "showcase_room_id": 228,
        "provider_id": 2,
        "provider_room_id": "room569"
    }
     * @param int $showcase_room_id
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $showcase_room_id)
    {
        $pscr = $this->service->get($showcase_room_id);

        return response()->json($pscr, 200);
    }

    /**
     * @api {get} /provider-showcase-room/{id} Удаление связи номера витрины номеру у поставщика
     * @apiVersion 0.1.0
     * @apiName DeleteProviderShowcaseRoom
     * @apiGroup AdminProviderShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "showcase_room_id": 228,
        "provider_id": 1,
        "provider_room_id": "sdfgdsgw4134"
    }
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException

     */
    public function delete(int $id)
    {
        $deleted = $this->service->delete($id);

        if ($deleted) {
            return response()->json(['mood' => [
                'Связь удалена'
            ]], 200);

        } else {
            return response()->json(['mood' => [
                'Связь не удалена'
            ]], 404);
        }
    }

    /**
     * @api {post} /api/admin/provider-showcase-room/{$id} Редактирование связи номера витрины номеру у поставщика
     * @apiVersion 0.1.0
     * @apiName EditProviderShowcaseRoom
     * @apiGroup AdminProviderShowcaseRoom
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer}  showcase_room_id идентификатор номера витрины в здравпродукте
     * @apiParam {integer}  provider_id идентификатор провайдера в здравпродукте
     * @apiParam {string}  provider_room_id идентификатор номера у поставщика
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "showcase_room_id": "228",
        "provider_id": "1",
        "provider_room_id": "new5454"
    }
     *
     * @param Request $request
     * @param int $id
     * @return bool|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function update(Request $request, int $id)
    {
        $valid = Validator($request->all(),[
            'showcase_room_id' => 'required|integer',
            'provider_id' => 'required|integer',
            'provider_room_id' => 'required|string',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $pscr = $this->service->update($request, $id);

        return response()->json($pscr, 200);
    }
}
