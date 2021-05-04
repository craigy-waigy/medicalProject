<?php

namespace App\Http\Controllers\Api\Admin\Faq;

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
     * @api {get} /api/admin/faq Получение и поиск в Админке faq
     * @apiVersion 0.1.0
     * @apiName SearchFaq
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
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
                "question_ru": "Как вопрос",
                "question_en": "How question",
                "answer_ru": "Ответ на вопрос",
                "answer_en": "Answer",
                "faq_tags": [
                    "sd",
                    "sdsd"
                ],
                "created_at": "2019-02-26 15:31:19",
                "updated_at": "2019-02-26 15:31:19"
            },
            {
                "id": 3,
                "question_ru": "Как вопрос",
                "question_en": "How question",
                "answer_ru": "Ответ на вопрос",
                "answer_en": "Answer",
                "faq_tags": [
                    "sd",
                    "sdsd"
                ],
                "created_at": "2019-02-26 15:31:54",
                "updated_at": "2019-02-26 15:31:54"
            }
        ]
    }
     *
     * @param Request $request
     * @return array
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function search(Request $request)
    {
        $valid = Validator($request->all(), [
            'page' => 'integer',
            'rowsPerPage' => 'integer',
            'searchKey' => 'string',
            'faq_tags' => [  new PageScopeRule ],
            'sorting' => [ new IsArray ],
        ]);
        if ($valid->fails()) response()->json($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;
        $faqTags =  json_decode($request->get('faq_tags') ?? null);
        $sorting = $request->get('sorting');
        if (!is_array($sorting)) $sorting = json_decode($request->get('sorting'), true);

        return $this->faqService->search($page, $rowsPerPage, $searchKey, $faqTags, null, $sorting);
    }

    /**
     * @api {post} /api/admin/faq Добавление faq в Админке
     * @apiVersion 0.1.0
     * @apiName AddFaq
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} question_ru Вопрос на русс.
     * @apiParam {String} [question_en] Вопрос на англ.
     * @apiParam {String} answer_ru Ответ на русск.
     * @apiParam {String} answer_en Ответ на анг.
     * @apiParam {json}  [faq_tags] Область видимости страниц ["main", "therapy", "others"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "question_ru": "Как вопрос?",
        "question_en": "How question",
        "answer_ru": "Так и ответ",
        "answer_en": "Same answer",
        "faq_tags": [
            "therapy"
        ],
        "updated_at": "2019-02-27 16:47:50",
        "created_at": "2019-02-27 16:47:50",
        "id": 26
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFaq(Request $request)
    {
        $valid = Validator($request->all(), [
            'question_ru' => 'required|string',
            'question_en' => 'string|nullable',
            'answer_ru' => 'required|string',
            'answer_en' => 'required|string|nullable',
            'faq_tags' => [ 'required', new PageScopeRule ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('question_ru', 'question_en', 'answer_ru', 'answer_en', 'faq_tags');
        $faq = $this->faqService->addFaq($data);

        return response()->json($faq, 200);
    }

    /**
     * @api {put} /api/admin/faq/{faqId} Редактирование faq в Админке
     * @apiVersion 0.1.0
     * @apiName EditFaq
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} [question_ru] Вопрос на русс.
     * @apiParam {String} [question_en] Вопрос на англ.
     * @apiParam {String} [answer_ru] Ответ на русск.
     * @apiParam {String} [answer_en] Ответ на анг.
     * @apiParam {json}   [faq_tags] Область видимости страниц ["main", "therapy", "others"]
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "question_ru": "Как вопрос?",
        "question_en": "How question",
        "answer_ru": "Так и ответ",
        "answer_en": "Same answer",
            "faq_tags": [
            "therapy"
        ],
        "updated_at": "2019-02-27 16:47:50",
        "created_at": "2019-02-27 16:47:50",
        "id": 26
    }
     *
     * @param Request $request
     * @param int $faqId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\NotFoundException
     */
    public function editFaq(Request $request, int $faqId)
    {
        $valid = Validator($request->all(), [
            'question_ru' => 'string',
            'question_en' => 'string|nullable',
            'answer_ru' => 'string',
            'answer_en' => 'string|nullable',
            'faq_tags' => [ new PageScopeRule ],
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);
        $data = $request->only('question_ru', 'question_en', 'answer_ru', 'answer_en', 'faq_tags');
        $faq = $this->faqService->editFaq($data, $faqId);

        return response()->json($faq, 200);
    }

    /**
     * @api {get} /api/admin/faq/{faqId} Получение faq в Админке
     * @apiVersion 0.1.0
     * @apiName GetFaq
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "question_ru": "Как вопрос?",
        "question_en": "How question",
        "answer_ru": "Так и ответ",
        "answer_en": "Same answer",
        "faq_tags": [
            "therapy"
        ],
        "updated_at": "2019-02-27 16:47:50",
        "created_at": "2019-02-27 16:47:50",
        "id": 26
    }
     *
     * @param int $faqId
     * @return mixed
     * @throws \App\Exceptions\NotFoundException
     * @throws \App\Exceptions\UnsupportLocaleException
     */
    public function getFaq(int $faqId)
    {
        return $this->faqService->getFaq($faqId);
    }

    /**
     * @api {delete} /api/admin/faq/{faqId} Удаление faq в Админке
     * @apiVersion 0.1.0
     * @apiName DeleteFaq
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
     * @param int $faqId
     * @throws \App\Exceptions\NotFoundException
     */
    public function deleteFaq(int $faqId)
    {
        $this->faqService->deleteFaq($faqId);
    }

    /**
     * @api {get} /api/admin/faq/tags Получение faq-тэгов в Админке
     * @apiVersion 0.1.0
     * @apiName GetFaqTags
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    [
        {
            "id": 1,
            "name": "Бронирование и аннулирование",
            "slug": "bron-and-null",
            "description": "Бронирование и аннулирование"
        },
        {
            "id": 10,
            "name": "Трансфер",
            "slug": "transfer",
            "description": "Трансфер"
        }
    ]
     *
     * @return \App\Models\FaqTag[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTags()
    {
        return $this->faqService->getTags();
    }

    /**
     * @api {post} /api/admin/faq/tag Добавление нового faq-тэга в Админке
     * @apiVersion 0.1.0
     * @apiName AddFaqTags
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {String} name имя нового тэга
     * @apiParam {String} description Ну и описание тэга для чего он собвственно
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "name": "имя нового тэга",
        "slug": "а это его слаг",
        "description": "Ну и описание тэга для чего он собвственно",
        "id": 12
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addTag(Request $request)
    {
        $valid = Validator($request->all(), [
           'name' => 'required|string',
           'description' => 'string|nullable',
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $data = $request->only('name', 'description');

        $tag = $this->faqService->addTag($data);

        return response()->json($tag, 200);
    }

    /**
     * @api {delete} /api/admin/faq/tag/{tagId} Удаление faq-тэга в Админке
     * @apiVersion 0.1.0
     * @apiName DeleteFaqTags
     * @apiGroup FAQ
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
     * @param int $faqTagId
     * @throws \App\Exceptions\FaqTagAlreadyUseException
     * @throws \App\Exceptions\NotFoundException
     */
    public function deleteFaqTag(int $faqTagId)
    {
        $this->faqService->deleteFaqTag($faqTagId);
    }
}
