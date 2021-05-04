<?php

namespace App\Http\Controllers\Api\Common;

use App\Services\ServicesService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    /**
     * @var ServicesService
     */
    protected $servicesService;

    /**
     * ServiceController constructor.
     */
    public function __construct()
    {
        $this->servicesService = new ServicesService();
    }

    /**
     * @api {get} /api/{locale}/service-filter Получение списка услуг для фильтра (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetServiceFilterPublic
     * @apiGroup Filter
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 13,
            "filter_name": "Теннисный корт",
            "alias": alias,
            "seo": {
                "id": 35143,
                "service_id": 13,
                "order": 321,
                "title": null
            }
        },
        {
            "id": 22,
            "filter_name": "Крытый бассейн",
            "alias": alias,
            "seo": {
                "id": 35147,
                "service_id": 22,
                "order": 325,
                "title": null
            }
        }
    ]
     *
     * @param string $locale
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getServicesFilter(string $locale)
    {
        $services = $this->servicesService->getServicesFilter($locale);

        return response()->json($services, 200);
    }
}
