<?php

namespace App\Http\Controllers\Api\Common;

use App\Rules\FaqTagRule;
use App\Rules\IsArray;
use App\Rules\PageScopeRule;
use App\Services\FaqService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    /**
     * @var FaqService
     */
    protected $faqService;

    /**
     * FaqController constructor.
     */
    public function __construct()
    {
        $this->faqService = new FaqService();
    }

    /**
     * @api {get} /api/{locale}/faq Получение списка faq (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName FilterFaq
     * @apiGroup FAQ
     *
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     * @apiParam {json}  [faq_tags] Область видимости страниц ["main", "therapy", "others"]
     * @apiParam {json}  [sorting] Сортировка [{"id":"desc"}, {"name_ru": "asc"}]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 20,
        "items": [
            {
                "id": 2,
                "question": "Как вопрос",
                "answer": "Ответ на вопрос"
            },
            {
                "id": 3,
                "question": "Как вопрос",
                "answer": "Ответ на вопрос"
            }
        ]
    }
     *
     * @param Request $request
     * @param string $locale
     * @return array
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function search(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer',
            'rowsPerPage' => 'integer',
            'searchKey' => 'string',
            'faq_tags' => [ new PageScopeRule ],
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = null;
        $faqTags =  json_decode($request->get('faq_tags') ?? null);
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        return $this->faqService->search($page, $rowsPerPage, $searchKey, $faqTags, $locale, $sorting);
    }

    /**
     * @api {get} /api/{locale}/faq/{faqId} Получение faq (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName GetPublicFaq
     * @apiGroup FAQ
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 2,
        "question": "Как вопрос",
        "answer": "Ответ на вопрос"
    }
     *
     * @param string $locale
     * @param int $faqId
     * @return mixed
     * @throws \App\Exceptions\NotFoundException
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function getFaq(string $locale, int $faqId)
    {
        return $this->faqService->getFaq($faqId, $locale);
    }

}
