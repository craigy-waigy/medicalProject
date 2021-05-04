<?php

namespace App\Http\Controllers\Api\Common;

use App\Exceptions\ApiProblemException;
use App\Rules\IsArray;
use App\Services\PublicMoodService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MoodController extends Controller
{
    /**
     * @var MoodService
     */
    protected $moodService;

    /**
     * MoodController constructor.
     */
    public function __construct()
    {
        $this->moodService = new PublicMoodService();
    }

    /**
     * @api {get} /api/{locale}/moods Получение и поиск mood-тегов
     * @apiVersion 0.1.0
     * @apiName SearchMood
     * @apiGroup PublicMood
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {string} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [sorting] Сортировка {"id": "desc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 50,
        "total": 4,
        "items": [
            {
                "id": 1,
                "name": "Ок",
                "alias": "ok",
                "crop_image": "/storage/moods_crop/DoX12ULbleuF8xR3X9OEDytstWwk4BzKUoNKHUwN.jpeg"
            },
            {
                "id": 2,
                "name": "Мать и дитя",
                "alias": "mother-and-child",
                "crop_image": "/storage/moods_crop/jY27yLMSdCDpXhBQ6wbSm1yI4KOJSX5oxQ5EZgCD.jpeg"
            }
        ]
    }
     *
     * @param Request|null $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function search(?Request $request, string $locale = 'ru')
    {
        $valid = Validator($request->only('page', 'rowsPerPage', 'searchKey'),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 50;
        $searchKey = $request->get('searchKey') ?? null;
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true) ?? null;

        return $this->moodService->search($page, $rowsPerPage, $searchKey, $sorting, $locale);
    }

}
