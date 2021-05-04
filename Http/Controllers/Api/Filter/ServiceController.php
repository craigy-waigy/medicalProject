<?php

namespace App\Http\Controllers\Api\Filter;

use App\Services\ObjectServicesSevice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ServiceController extends Controller
{
    protected $objectServicesService;

    public function __construct()
    {
        $this->objectServicesService = new ObjectServicesSevice();
    }

    /**
     *
     * @api {get} /api/filter/service получение - поиск списка услуг
     * @apiVersion 0.1.0
     * @apiName GetServices
     * @apiGroup Filter
     *
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 3,
        "total": 3,
        "items": [
            {
                "id": 52,
                "name_ru": "Детская комната",
                "name_en": null,
                "service_category_id": 1
            },
            {
                "id": 65,
                "name_ru": "Детский городок",
                "name_en": null,
                "service_category_id": 1
            },
            {
                "id": 77,
                "name_ru": "Детская площадка",
                "name_en": null,
                "service_category_id": 1
            }
        ]
    }
     *
     * @param Request $request
     * @return array
     */
    public function searchService(Request $request)
   {
       $page = $request->get('page') ?? 1;
       $rowsPerPage = $request->get('rowsPerPage') ?? 10;
       $searchKey = $request->get('searchKey') ?? null;

       return $this->objectServicesService->search($page, $rowsPerPage, $searchKey);
   }
}
