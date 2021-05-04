<?php

namespace App\Http\Controllers\Api\Counters;

use App\Services\ViewingCounterService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ViewingCounterController extends Controller
{
    /**
     * @var ViewingCounterService
     */
    protected $viewingCounterService;

    /**
     * ViewingCounterController constructor.
     *
     * @param ViewingCounterService $viewingCounterService
     */
    public function __construct(ViewingCounterService $viewingCounterService)
    {
        $this->viewingCounterService = $viewingCounterService;
    }

    /**
     * @api {post} /api/counter/viewing Счетчик просмотров
     * @apiVersion 0.1.0
     * @apiName Viewing
     * @apiGroup Counters
     *
     * @apiParam {integer} [geo_ip_country_id] ID страны пользователя
     * @apiParam {integer} [geo_ip_region_id] ID региона пользователя
     * @apiParam {integer} [geo_ip_city_id] ID города пользователя
     *
     * @apiParam {integer} [country_id] ID Курорта страны
     * @apiParam {integer} [region_id] ID Курорта региона
     * @apiParam {integer} [city_id] ID Курорта города
     *
     * @apiParam {integer} [medical_profile_id] ID Мед.профиля
     * @apiParam {integer} [therapy_id] ID метода лечения
     * @apiParam {integer} [disease_id] ID заболевания
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 201 OK
    {"counter":"success incremented"}
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \App\Exceptions\ApiProblemException
     */
    public function counter(Request $request)
    {
        $valid = Validator([
            'geo_ip_country_id' => 'nullable|integer',
            'geo_ip_region_id' => 'nullable|integer',
            'geo_ip_city_id' => 'nullable|integer',

            'country_id' => 'nullable|integer',
            'region_id' => 'nullable|integer',
            'city_id' => 'nullable|integer',

            'medical_profile_id' => 'nullable|integer',
            'therapy_id' => 'nullable|integer',
            'disease_id' => 'nullable|integer',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $geoIpParams['geo_ip_country_id'] = $request->get('geo_ip_country_id');
        $geoIpParams['geo_ip_region_id'] = $request->get('geo_ip_region_id');
        $geoIpParams['geo_ip_city_id'] = $request->get('geo_ip_city_id');

        $params['country_id'] = $request->get('country_id');
        $params['region_id'] = $request->get('region_id');
        $params['city_id'] = $request->get('city_id');

        $params['medical_profile_id'] = $request->get('medical_profile_id');
        $params['therapy_id'] = $request->get('therapy_id');
        $params['disease_id'] = $request->get('disease_id');

        $this->viewingCounterService->counter($geoIpParams, $params);

        return response(['counter' => 'success incremented'], 201);
    }
}
