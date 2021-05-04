<?php

namespace App\Http\Controllers\Api\Common;

use App\Exceptions\ApiProblemException;
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
     * @api {get} /api/{locale}/seo получение SEO - информации (locale = ru или en)
     * @apiVersion 0.1.0
     * @apiName Get
     * @apiGroup Seo
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
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
    HTTP/1.1 200 OK
    {
        "for": "region-page",
        "h1": "это h1",
        "title": "title",
        "url": "url thi is",
        "meta_description": "это для страницы region",
        "meta_keywords": "keys"
    }
     *
     * @param Request $request
     * @param string $locale
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|mixed
     * @throws \App\Exceptions\SeoForException
     * @throws ApiProblemException
     */
    public function getSeo(Request $request, string $locale)
    {
        $valid = Validator($request->all(), [
            'for' => 'required|string',
        ]);
        if ( $valid->fails() ) return response()->json($valid->errors(), 400);

        $for = $request->get('for');
        $rules = $this->seoService->getValidatorRules($for);

        $valid = Validator($request->all(), $rules);
        if ( $valid->fails() ){
            return response(['errors' => $valid->errors()], 400);
        }
        $cond = $request->only('news_id', 'object_id', 'disease_id', 'medical_profile_id',
            'therapy_id', 'country_id', 'region_id', 'city_id', 'service_id', 'partner_type_id', 'publication_type_id');

        $seoInfo = $this->seoService->getMeta($for, $cond, $locale);

        return response()->json($seoInfo, 200);
    }
}
