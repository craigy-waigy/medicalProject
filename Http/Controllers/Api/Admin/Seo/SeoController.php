<?php

namespace App\Http\Controllers\Api\Admin\Seo;

use App\Exceptions\ApiProblemException;
use App\Models\SeoInformation;
use App\Services\SeoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SeoController extends Controller
{
    /**
     * @var SeoService
     */
    protected $seoService;

    /**
     * SeoController constructor.
     */
    public function __construct()
    {
        $this->seoService = new SeoService();
    }

    /**
     * @api {post} /api/admin/seo сохранение SEO - информации
     * @apiVersion 0.1.0
     * @apiName Set
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} for "main-page" Для главной страницы
     * @apiParam {string} for. "main-news-page" Для главной страницы новостей
     * @apiParam {string} for.. "list-news-page" Для страницы списка новостей
     * @apiParam {string} for... "news-page" Для страницы новости (дополнительно нужно отправить параметр news_id)
     * @apiParam {string} for.... "object-page" Для страницы объекта (дополнительно нужно отправить параметр object_id)
     * @apiParam {string} for..... "disease-page" Для страницы заболевания (дополнительно нужно отправить параметр disease_id)
     * @apiParam {string} for...... "medical-profile-page" Для страницы профиля лечения (дополнительно нужно отправить параметр medical_profile_id)
     * @apiParam {string} for....... "therapy-page" Для страницы метода лечения (дополнительно нужно отправить параметр therapy_id)
     * @apiParam {string} for........ "country-page" Для страницы описания страны (дополнительно нужно отправить параметр country_id)
     * @apiParam {string} for......... "region-page" Для страницы описания региона (дополнительно нужно отправить параметр region_id)
     * @apiParam {string} for.......... "city-page" Для страницы описания города (дополнительно нужно отправить параметр city_id)
     * @apiParam {string} for........... "search-object-page" Для страницы поиска обэектов
     * @apiParam {string} for............ "search-disease-page" Для страницы поиска заболеваний
     * @apiParam {string} for............. "search-medical-profile-page" Для страницы поиска профиля лечения
     * @apiParam {string} for.............. "search-therapy-page" Для страницы поиска метода лечения
     * @apiParam {string} for............... "search-medical-profile-page" Для страницы поиска
     * @apiParam {string} for................ "search-geo-page" Для страницы поиска географии
     * @apiParam {string} for................. "feedback-page" Для страницы отзывов
     * @apiParam {string} for.................. "partners-page" Для страницы партнеров
     * @apiParam {string} for................... "offer" Для страницы спецпредложения (дополнительно нужно отправить параметр offer_id)
     * @apiParam {string} for.................... "about" Для страницы раздела Опроекте (дополнительно нужно отправить параметр about_id)
     * @apiParam {string} for..................... "partner" Для страницы Партнера (дополнительно нужно отправить параметр partner_id)
     * @apiParam {string} for...................... "publication" Для страницы публикации Партнера (дополнительно нужно отправить параметр publication_id)
     * @apiParam {string} for....................... "service" Для услуг (дополнительно нужно отправить параметр service_id)
     * @apiParam {string} for........................ "partner-type" Для типов партнера (дополнительно нужно отправить параметр partner_type_id)
     * @apiParam {string} for......................... "publication-type" Для типов публикаций (дополнительно нужно отправить параметр publication_type_id)
     * @apiParam {string} for.......................... "offers-page" Для спецпредложений
     * @apiParam {string} for........................... "country-list" Для списка стран
     * @apiParam {string} for............................ "region-list" Для списка регионов
     * @apiParam {string} for............................. "city-list" Для списка городов
     *
     * @apiParam {string} for "main-page" Для главной страницы
     * @apiParam {string} [h1_ru] Заголовок H1
     * @apiParam {string} [h1_en] Заголовок H1
     * @apiParam {string} [title_ru] Заголовок
     * @apiParam {string} [title_en] Заголовок
     * @apiParam {string} [url] алиас
     * @apiParam {string} [meta_description_ru] Описание
     * @apiParam {string} [meta_description_en] Описание
     * @apiParam {string} [meta_keywords_ru] Ключевые слова
     * @apiParam {string} [meta_keywords_en] Ключевые слова
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "for": "region-page",
        "h1_ru": "это h1",
        "h1_en": "это h1",
        "title_ru": "title",
        "title_en": "title",
        "url": "url thi is",
        "meta_description_ru": "это для страницы region",
        "meta_description_en": "это для страницы region",
        "meta_keywords_ru": "keys"
        "meta_keywords_en": "keys"
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \App\Exceptions\SeoForException
     * @throws \App\Exceptions\NotFoundException
     * @throws \App\Exceptions\ApiProblemException
     */
    public function setSeo(Request $request)
    {
        $for = $request->get('for');
        $rules = $this->seoService->getValidatorRules($for);

        $valid = Validator($request->all(), $rules);
        if ( $valid->fails() ){
            return response(['errors' => $valid->errors()], 400);
        }

        switch ($for){

            case SeoInformation::FOR_NEWS :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'news_id');
                return $this->seoService->setForNews($data, $for);

            case SeoInformation::FOR_OBJECT_PAGE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'object_id');
                return $this->seoService->setForObject($data, $for);

            case SeoInformation::FOR_DISEASE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'disease_id');
                return $this->seoService->setForDisease($data, $for);

            case SeoInformation::FOR_MEDICAL_PROFILE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'medical_profile_id');
                return $this->seoService->setForMedicalProfile($data, $for);

            case SeoInformation::FOR_THERAPY :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'therapy_id');
                return $this->seoService->setForTherapy($data, $for);

            case SeoInformation::FOR_COUNTRY :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'country_id');
                return $this->seoService->setForCountry($data, $for);

            case SeoInformation::FOR_REGION :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'region_id');
                return $this->seoService->setForRegion($data, $for);

            case SeoInformation::FOR_CITY :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'city_id');
                return $this->seoService->setForCity($data, $for);

            case SeoInformation::FOR_OFFER :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'offer_id');
                return $this->seoService->setForOffer($data, $for);

            case SeoInformation::FOR_ABOUT :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'about_id');
                return $this->seoService->setForAbout($data, $for);

            case SeoInformation::FOR_PARTNER :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'partner_id');
                return $this->seoService->setForPartner($data, $for);

            case SeoInformation::FOR_PUBLICATION :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'publication_id');
                return $this->seoService->setForPublication($data, $for);

            case SeoInformation::FOR_SERVICE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'service_id');
                return $this->seoService->setForService($data, $for);

            case SeoInformation::FOR_PARTNER_TYPE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'partner_type_id');
                return $this->seoService->setForPartnerType($data, $for);

            case SeoInformation::FOR_PUBLICATION_TYPE :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en', 'publication_type_id');
                return $this->seoService->setForPublicationType($data, $for);

            default :
                $data = $request->only('h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                    'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en');
                return $this->seoService->setMeta($data, $for);
        }
    }

    /**
     * @api {get} /api/admin/seo получение SEO - информации
     * @apiVersion 0.1.0
     * @apiName Get
     * @apiGroup SeoAdmin
     *
     * @apiDescription Для страниц ниже seo-информация передается также с данными для страницы:
     * <ul>
     *  <li>Страница объекта</li>
     *  <li>Страница новости</li>
     *  <li>Страница заболевания</li>
     *  <li>Страница спецпредложения</li>
     *  <li>Страница профиля лечения</li>
     *  <li>Страница метода лечения</li>
     *  <li>Страница описания страны</li>
     *  <li>Страница описания региона</li>
     *  <li>Страница описания города</li>
     *  <li>Страницы разделов "О проекте"</li>
     *  <li>Страницы партнера</li>
     *  <li>Страницы типа партнера</li>
     *  <li>Страницы типа публикации партнера</li>
     *  <li>Страницы публикации партнера</li>
     *
     * </ul>
     *
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} for "main-page" Для главной страницы
     * @apiParam {string} for. "main-news-page" Для главной страницы новостей
     * @apiParam {string} for.. "list-news-page" Для страницы списка новостей
     * @apiParam {string} for... "news-page" Для страницы новости (дополнительно нужно отправить параметр news_id)
     * @apiParam {string} for.... "object-page" Для страницы объекта (дополнительно нужно отправить параметр object_id)
     * @apiParam {string} for..... "disease-page" Для страницы заболевания (дополнительно нужно отправить параметр disease_id)
     * @apiParam {string} for...... "medical-profile-page" Для страницы профиля лечения (дополнительно нужно отправить параметр medical_profile_id)
     * @apiParam {string} for....... "therapy-page" Для страницы метода лечения (дополнительно нужно отправить параметр therapy_id)
     * @apiParam {string} for........ "country-page" Для страницы описания страны (дополнительно нужно отправить параметр country_id)
     * @apiParam {string} for......... "region-page" Для страницы описания региона (дополнительно нужно отправить параметр region_id)
     * @apiParam {string} for.......... "city-page" Для страницы описания города (дополнительно нужно отправить параметр city_id)
     * @apiParam {string} for........... "search-object-page" Для страницы поиска обэектов
     * @apiParam {string} for............ "search-disease-page" Для страницы поиска заболеваний
     * @apiParam {string} for............. "search-medical-profile-page" Для страницы поиска профиля лечения
     * @apiParam {string} for.............. "search-therapy-page" Для страницы поиска метода лечения
     * @apiParam {string} for............... "search-medical-profile-page" Для страницы поиска
     * @apiParam {string} for................ "search-geo-page" Для страницы поиска географии
     * @apiParam {string} for................. "feedback-page" Для страницы отзывов
     * @apiParam {string} for.................. "partners-page" Для страницы партнеров
     * @apiParam {string} for................... "offer" Для страницы спецпредложения (дополнительно нужно отправить параметр offer_id)
     * @apiParam {string} for.................... "about" Для страницы раздела Опроекте (дополнительно нужно отправить параметр about_id)
     * @apiParam {string} for..................... "partner" Для страницы Партнера (дополнительно нужно отправить параметр partner_id)
     * @apiParam {string} for...................... "publication" Для страницы публикации Партнера (дополнительно нужно отправить параметр publication_id)
     * @apiParam {string} for....................... "service" Для услуг (дополнительно нужно отправить параметр service_id)
     * @apiParam {string} for........................ "partner-type" Для типов партнера (дополнительно нужно отправить параметр partner_type_id)
     * @apiParam {string} for......................... "publication-type" Для типов публикаций (дополнительно нужно отправить параметр publication_type_id)
     * @apiParam {string} for.......................... "offers-page" Для спецпредложений
     * @apiParam {string} for........................... "country-list" Для списка стран
     * @apiParam {string} for............................ "region-list" Для списка регионов
     * @apiParam {string} for............................. "city-list" Для списка городов
     *
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "for": "region-page",
        "h1_ru": "это h1",
        "h1_en": "это h1",
        "title_ru": "title",
        "title_en": "title",
        "url": "url thi is",
        "meta_description_ru": "это для страницы region",
        "meta_description_en": "это для страницы region",
        "meta_keywords_ru": "keys"
        "meta_keywords_en": "keys"
    }
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \App\Exceptions\SeoForException
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getSeo(Request $request)
    {
        $for = $request->get('for');
        $rules = $this->seoService->getValidatorRules($for);

        $valid = Validator($request->all(), $rules);
        if ( $valid->fails() ){
            return response(['errors' => $valid->errors()], 400);
        }
        $cond = $request->only('news_id', 'object_id', 'disease_id', 'medical_profile_id',
            'therapy_id', 'country_id', 'region_id', 'city_id', 'service_id', 'partner_type_id', 'publication_type_id');

        return $this->seoService->getMeta($for, $cond);
    }

    /**
     * @api {get} /api/admin/seo/robots получение robots.txt
     * @apiVersion 0.1.0
     * @apiName GetRobots
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "content": "robots content"
     }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRobots()
    {
        $content = $this->seoService->getRobots();

        return response()->json($content, 200);
    }

    /**
     * @api {post} /api/admin/seo/robots сохранение robots.txt
     * @apiVersion 0.1.0
     * @apiName SetRobots
     * @apiGroup SeoAdmin
     *
     * @apiDescription Для корректного сохранения необходимо передавать json-объектом.
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} content Контент файла robots.txt
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "message": "Файл сохранен"
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setRobots(Request $request)
    {
        $valid = Validator($request->all(),[
           'content' => 'present'
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);

        $content = $request->get('content');
        $this->seoService->setRobots($content);

        return response()->json(['message' => 'Файл сохранен']);
    }

    /**
     * @api {post} /api/admin/seo/template создание / редактирование шаблона
     * @apiVersion 0.1.0
     * @apiName AddTemplate
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} for "discount" Для скидок / акций
     * @apiParam {string} for. "country" Для страны
     * @apiParam {string} for.. "region" Для региона
     * @apiParam {string} for... "city" Для города
     * @apiParam {string} for.... "medical_profiles" Для мед. профиля
     * @apiParam {string} for..... "therapies" Для мет. лечения
     * @apiParam {string} for...... "diseases" Для заболеваний
     * @apiParam {string} for....... "services" Для услуг
     * @apiParam {string} for........ "beside" Для критерия рядом
     * @apiParam {string} for......... "stars" Для звездность
     * @apiParam {string} for.......... "rating" Для рейтинга
     *
     * @apiParam {string} [meta_description_ru] ru шаблон для meta-description
     * @apiParam {string} [meta_description_en] en шаблон для meta-description
     * @apiParam {string} [text_ru] ru шаблон для текста
     * @apiParam {string} [text_en] en шаблон для текста
     * @apiParam {string} [title_ru] ru шаблон для мета-title
     * @apiParam {string} [title_en] en шаблон для мета-title

     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK

     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function setTemplate(Request $request)
    {
        $valid = Validator($request->all(), [
            'for' => 'required|string',
            'meta_description_ru' => 'nullable|string',
            'meta_description_en' => 'nullable|string',
            'text_ru' => 'nullable|string',
            'text_en' => 'nullable|string',
            'title_ru' => 'nullable|string',
            'title_en' => 'nullable|string',

        ]);
        if ( $valid->fails() ) return response()->json($valid->errors(), 400);

        $for = $request->get('for');
        $this->seoService->validateTemplate($for); //Валидируем параметр for

        $data = $request->only(
            'title_ru',
            'title_en',
            'meta_description_ru',
            'meta_description_en',
            'text_ru',
            'text_en'
        );

        $template = $this->seoService->setTemplate($for, $data);

        return response()->json($template, 200);
    }

    /**
     * @api {get} /api/admin/seo/template получение шаблона
     * @apiVersion 0.1.0
     * @apiName GetTemplate
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     *
     * @apiParam {string} for "discount" Для скидок / акций
     * @apiParam {string} for. "country" Для страны
     * @apiParam {string} for.. "region" Для региона
     * @apiParam {string} for... "city" Для города
     * @apiParam {string} for.... "medical_profiles" Для мед. профиля
     * @apiParam {string} for..... "therapies" Для мет. лечения
     * @apiParam {string} for...... "diseases" Для заболеваний
     * @apiParam {string} for....... "services" Для услуг
     * @apiParam {string} for........ "beside" Для критерия рядом
     * @apiParam {string} for......... "stars" Для звездность
     * @apiParam {string} for.......... "rating" Для рейтинга
     *
     *
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 3,
        "for": "country",
        "meta_description_ru": "template",
        "meta_description_en": "template",
        "text_ru": "template",
        "text_en": "template",
        "title_ru": "",
        "title_en": ""
    }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\ApiProblemException
     */
    public function getTemplate(Request $request)
    {
        $valid = Validator($request->all(), [
            'for' => 'required|string',
        ]);
        if ( $valid->fails() ) return response()->json($valid->errors(), 400);

        $for = $request->get('for');
        $this->seoService->validateTemplate($for); //Валидируем параметр for

        $template = $this->seoService->getTemplate($for);

        return response()->json($template, 200);
    }

    /**
     * @api {get} /api/admin/seo/filter-url получение списка мета-данных url фильтра здравниц
     * @apiVersion 0.1.0
     * @apiName listMetaDataFilterUrl
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [page] Страница
     * @apiParam {integer} [rowsPerPage] Количество результатов на странице
     * @apiParam {String} [searchKey] Ключевое слово для поиска
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     *
    {
        "page": 1,
        "rowsPerPage": 10,
        "total": 3,
        "items": [
            {
                "id": 3,
                "url": "werqwerqwerwer21",
                "title_ru": "title_ru",
                "title_en": "title_en",
                "description_ru": "description_ru",
                "description_en": "description_en",
                "text_ru": "text_ru",
                "text_en": "text_en"
            },
            {
                "id": 4,
                "url": "werqwerqwerwer21\/4324",
                "title_ru": "title_ru",
                "title_en": "title_en",
                "description_ru": "description_ru",
                "description_en": "description_en",
                "text_ru": "text_ru",
                "text_en": "text_en"
            },
            {
                "id": 1,
                "url": "discount\/disease-alias\/beside-jz\/stars-3",
                "title_ru": "title_ru",
                "title_en": "title_en",
                "description_ru": "description_ru",
                "description_en": "description_en",
                "text_ru": "text_ru",
                "text_en": "text_en"
            }
        ]
    }
     *
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function listSeoFilterUrl(Request $request)
    {
        $valid = Validator($request->all(),[
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'searchKey' => 'string|nullable',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $searchKey = $request->get('searchKey') ?? null;

        $seoFilterUrls = $this->seoService->listSeoFilterUrl($page, $rowsPerPage, $searchKey);

        return response()->json($seoFilterUrls, 200);
    }

    /**
     * @api {get} /api/admin/seo/filter-url/{seo_filter_url_id} получение мета-данных url фильтра здравниц
     * @apiVersion 0.1.0
     * @apiName GetMetaDataFilterUrl
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "url": "pyatigorsk/bolezni-ukha-gorla-i-nosa/bolezni-oporno-dvigatelnogo-apparata",
        "title_ru": "title_ru",
        "title_en": "title_en",
        "description_ru": "description_ru",
        "description_en": "description_en",
        "text_ru": "text_ru",
        "text_en": "text_en"
    }
     *
     *
     * @param int $seoFilterUrlId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function getSeoFilterUrl(int $seoFilterUrlId)
    {
        $seoFilterUrl = $this->seoService->getSeoFilterUrl($seoFilterUrlId);

        return response()->json($seoFilterUrl, 200);
    }

    /**
     * @api {post} /api/admin/seo/filter-url сохранение/обновление мета-данных url фильтра здравниц
     * @apiVersion 0.1.0
     * @apiName SetMetaDataFilterUrl
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} [seo_filter_url_id] ID url
     * @apiParam {string} url url ( например: pyatigorsk/bolezni-ukha-gorla-i-nosa/bolezni-oporno-dvigatelnogo-apparata )
     * @apiParam {string} title_ru ru meta-title
     * @apiParam {string} [title_en] en meta-title
     * @apiParam {string} description_ru ru meta-description
     * @apiParam {string} [description_en] en meta-description
     * @apiParam {string} text_ru ru text
     * @apiParam {string} [text_en] en text
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "id": 1,
        "url": "pyatigorsk/bolezni-ukha-gorla-i-nosa/bolezni-oporno-dvigatelnogo-apparata",
        "title_ru": "title_ru",
        "title_en": "title_en",
        "description_ru": "description_ru",
        "description_en": "description_en",
        "text_ru": "text_ru",
        "text_en": "text_en"
    }
     *
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * @throws ApiProblemException
     */
    public function setSeoFilterUrl(Request $request)
    {
        $valid = Validator($request->all(),[
            'seo_filter_url_id' => 'nullable|integer',
            'url' => 'required|string|max:1000',
            'title_ru' => 'required|string',
            'title_en' => 'string|nullable',
            'description_ru' => 'required|string',
            'description_en' => 'string|nullable',
            'text_ru' => 'required|string',
            'text_en' => 'string|nullable',
        ]);
        if ($valid->fails()) return response($valid->errors(), 400);

        $seoFilterUrlId = $request->get('seo_filter_url_id');
        $data = $request->only(
            'url',
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'text_ru',
            'text_en'
            );
        $seoFilterUrl = $this->seoService->setSeoFilterUrl($data, $seoFilterUrlId);

        return response()->json($seoFilterUrl, 200);
    }

    /**
     * @api {delete} /api/admin/seo/filter-url/{seo_filter_url_id} удаление мета-данных url фильтра здравниц
     * @apiVersion 0.1.0
     * @apiName DeleteMetaDataFilterUrl
     * @apiGroup SeoAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
     {
        "message": "url удален"
     }
     *
     * @param int $seoFilterUrlId
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiProblemException
     */
    public function deleteSeoFilterUrl(int $seoFilterUrlId)
    {
        $this->seoService->deleteSeoFilterUrl($seoFilterUrlId);

        return response()->json(['message' => 'url удален'], 200);
    }
}
