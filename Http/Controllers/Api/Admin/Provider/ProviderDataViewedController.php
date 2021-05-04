<?php

namespace App\Http\Controllers\Api\Admin\Provider;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\ProviderDataViewedService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProviderDataViewedController extends Controller
{
    /**
     * @var ProviderDataViewedService
     */
    protected $providerDataViewedService;

    /**
     * ProviderDataViewedController constructor.
     */
    public function __construct()
    {
        $this->providerDataViewedService = new ProviderDataViewedService();
    }

    /**
     * @api {get} /api/admin/provider-data-viewed/{objectId} Список полей для которых использованы данные от поставщика
     * @apiVersion 0.1.0
     * @apiName GetProviderDataViewed
     * @apiGroup AdminProviderDataViewed
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "object_id": 66,
            "provider_id": 2,
            "viewed_fields": {
                "field422": true,
                "field1": true,
                "field12": true
            }
        },
        {
            "object_id": 66,
            "provider_id": 1,
            "viewed_fields": []
        }
    ]
     *
     * @param int $objectId
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getViewed(int $objectId)
    {
        $viewed =  $this->providerDataViewedService->getViewed($objectId);

        return response()->json($viewed, 200);
    }

    /**
     * @api {post} /api/admin/provider-data-viewed/mark-viewed Пометить поле для которого использованы данные от поставщика
     * @apiVersion 0.1.0
     * @apiName MarkViewedProviderDataViewed
     * @apiGroup AdminProviderDataViewed
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id Идентификатор объекта
     * @apiParam {integer} provider_id Идентификатор провайдера
     * @apiParam {string} viewed_field Название просмотренного поля
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "Mark viewed": true
    }
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function markViewed(Request $request)
    {
        $valid = Validator($request->all(),[
            'object_id' => 'required|integer|exists:objects,id',
            'provider_id' => 'required|integer|exists:providers,id',
            'viewed_field'=> 'required|string',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $objectId = $request->get('object_id');
        $providerId = $request->get('provider_id');
        $viewedField = $request->get('viewed_field');

        $marked =  $this->providerDataViewedService->markViewed($objectId, $providerId, $viewedField);

        if ($marked) {
            return response()->json(['Mark viewed' => true], 200);

        } else {
            return response()->json(['Mark viewed' => false], 400);
        }

    }

    /**
     * @api {post} /api/admin/provider-data-viewed/mark-unviewed Убрать пометку с поля для которого использованы данные от поставщика
     * @apiVersion 0.1.0
     * @apiName MarkUnviewedProviderDataViewed
     * @apiGroup AdminProviderDataViewed
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} object_id Идентификатор объекта
     * @apiParam {integer} provider_id Идентификатор провайдера
     * @apiParam {string} viewed_field Название просмотренного поля
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "Mark unviewed": true
    }
     *
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function markUnviewed(Request $request)
    {
        $valid = Validator($request->all(),[
            'object_id' => 'required|integer|exists:objects,id',
            'provider_id' => 'required|integer|exists:providers,id',
            'viewed_field'=> 'required|string',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $objectId = $request->get('object_id');
        $providerId = $request->get('provider_id');
        $viewedField = $request->get('viewed_field');

        $this->providerDataViewedService->markUnviewed($objectId, $providerId, $viewedField);

        return response()->json(['Mark unviewed' => true], 200);

    }

}
