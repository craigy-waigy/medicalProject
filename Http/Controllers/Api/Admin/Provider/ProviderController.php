<?php

namespace App\Http\Controllers\Api\Admin\Provider;

use App\Services\ProviderService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * @var ProviderService
     */
    protected $providerService;

    /**
     * ProviderController constructor.
     */
    public function __construct()
    {
        $this->providerService = new ProviderService();
    }

    /**
     * @api {get} /api/admin/provider Получение и поиск провайдеров
     * @apiVersion 0.1.0
     * @apiName SearchProvider
     * @apiGroup AdminProvider
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 50,
        "total": 1,
        "items": [
            {
                "id": 1,
                "provider_name": "Алеан"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function search(?Request $request)
    {
        $valid = Validator($request->only('page', 'rowsPerPage'),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 50;

        return $this->providerService->search($page, $rowsPerPage);
    }


}
