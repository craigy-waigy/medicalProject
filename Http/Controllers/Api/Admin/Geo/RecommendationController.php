<?php

namespace App\Http\Controllers\Api\Admin\Geo;

use App\Exceptions\ApiProblemException;
use App\Models\Recommendation;
use App\Rules\IsArray;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RecommendationController extends Controller
{
    /**
     * @var RecommendationService
     */
    protected $recommendationService;

    /**
     * RecommendationController constructor.
     */
    public function __construct()
    {
        $this->recommendationService = new RecommendationService();
    }

    /**
     * @api {post} /api/admin/recommendation создание рекомендации городов, регионов стран
     * @apiVersion 0.1.0
     * @apiName createRecommendation
     * @apiGroup GeoRecommendationAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} type тип локации ('city', 'region', 'country')
     * @apiParam {integer} location_id идентификатор города, региона, страны
     * @apiParam {string} [recommendation_ru] текст рекомендации
     * @apiParam {string} [recommendation_en] текст рекомендации
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
     HTTP/1.1 200 OK
     {
        "location_id": "88",
        "type": "country",
        "recommendation_ru": "рекомендация 2",
        "recommendation_en": "recomm eng 2",
        "id": 5
     }
     *
     * @param Request $request
     * @return Recommendation
     */
    public function createRecommendation(Request $request)
    {
        $locationType = $request->post('type');
        if ($locationType){
            $typeTable = RecommendationService::getPluralLocation($locationType);
        } else return response()->json(['error' =>['Некорректный тип']], 400);

        $valid = Validator($request->all(),[
            'type' => 'required|string|in:city,region,country',
            'location_id' => 'required|integer|exists:' . $typeTable . ',id',
            'recommendation_ru' => 'string|nullable',
            'recommendation_en' => 'string|nullable',
        ]);
        if ($valid->fails()){

            $response = [
                'status' => 400,
                'errors' => $valid->errors(),
            ];

            return response($response, $response['status']);
        }

        return $this->recommendationService->createRecommendation($request);
    }

    /**
     * @api {get} /api/admin/recommendation поиск рекомендаций городов, регионов, стран
     * @apiVersion 0.1.0
     * @apiName searchRecommendation
     * @apiGroup GeoRecommendationAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} type тип локации ('city', 'region', 'country')
     * @apiParam {string} location_id идентификатор города, региона, страны
     * @apiParam {integer} [page] номер страницы
     * @apiParam {integer} [rowsPerPage] количество результатов на страницу
     * @apiParam {string} [locale] локаль ('ru', 'en')

     * @apiParam {json}  [sorting] Массив сортировки {"name_ru": "asc"}
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        {
            "page": 1,
            "rowsPerPage": 10,
            "total": 2,
            "items": [
                {
                    "id": 5,
                    "type": "country",
                    "location_id": 88,
                    "recommendation": "рекомендация 5",
                },
                {
                    "id": 4,
                    "type": "country",
                    "location_id": 88,
                    "recommendation": "рекомендация 4",
                }
            ]
        }
     *
     * @throws ApiProblemException
     * @param Request $request
     * @return array|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function searchRecommendation(Request $request)
    {
        $locationType = $request->post('type');
        if ($locationType){
            $typeTable = RecommendationService::getPluralLocation($locationType);
        } else return response()->json(['error' =>['Некорректный тип']], 400);

        $valid = Validator($request->all(), [
            'type' => 'required|string|in:city,region,country',
            'location_id' => 'required|integer|nullable|exists:' . $typeTable . ',id',
            'page' => 'integer|nullable',
            'rowsPerPage' => 'integer|nullable',
            'locale' => 'string|nullable',
            'sorting' => [ new IsArray ]
        ]);
        if ($valid->fails()) return response()->json($valid->errors(), 400);


        $locationType = $request->get('type');
        $locationId = $request->get('location_id');
        $sorting = $request->get('sorting');
        $page = $request->get('page') ?? 1;
        $rowsPerPage = $request->get('rowsPerPage') ?? 10;
        $locale = $request->get('locale') ?? 'ru';
        if (!is_array($sorting)) $sorting = json_decode($sorting, true);
        $recommendations = $this->recommendationService->getRecommendations($locationType, $locationId, $page, $rowsPerPage, $sorting, $locale);

        return response()->json($recommendations, 200);
    }

    /**
     * @api {get} /api/admin/recommendation/{recommendationId} получение рекомендации города, региона, страны
     * @apiVersion 0.1.0
     * @apiName GetRecommendation
     * @apiGroup GeoRecommendationAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        {
            "id": 6,
            "type": "country",
            "location_id": 88,
            "recommendation_ru": "рекомендация 2",
            "recommendation_en": "recomm eng 2",
        }
     * @throws ApiProblemException
     * @param int $recommendationId идентификатор города, страны, региона
     * @return mixed
     */
    public function getRecommendation(int $recommendationId)
    {
        $valid = Validator([
            'id' => $recommendationId
        ], [
            'id' => 'required|integer|exists:recommendations,id',
        ]);
        if ($valid->fails()){
            return response($valid->errors(), 400);
        }
        $recommendation = $this->recommendationService->getRecommendation($recommendationId);

        return response()->json($recommendation, 200);
    }

    /**
     * @api {post} /api/admin/recommendation/{recommendationId} редактирование рекомендации города, региона, страны
     * @apiVersion 0.1.0
     * @apiName UpdateRecommendation
     * @apiGroup GeoRecommendationAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {string} type тип локации ('city', 'region', 'country')
     * @apiParam {integer} location_id идентификатор города, страны, региона
     * @apiParam {string} [recommendation_ru] рекомендации на русском
     * @apiParam {string} [recommendation_en] рекомендации на английском
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
       HTTP/1.1 200 OK
        {
            "id": 4,
            "type": "city",
            "location_id": "22",
            "recommendation_ru": "текст рекомендации 111",
            "recommendation_en": "recommendation text 111",
        }
     * @throws ApiProblemException
     * @param Request $request
     * @param {string} type тип локации ('city', 'region', 'country')
     * @param {integer} id идентификатор города, страны, региона
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function updateRecommendation(Request $request, int $recommendationId)
    {
        $locationType = $request->post('type');
        if ($locationType){
            $typeTable = RecommendationService::getPluralLocation($locationType);
        } else return response()->json(['error' =>['Некорректный тип']], 400);

        $valid = Validator($request->all(),[
            'type' => 'required|string|in:city,region,country',
            'location_id' => 'integer|nullable|exists:' . $typeTable . ',id',
            'recommendation_ru'=> 'string|nullable',
            'recommendation_en'=> 'string|nullable',
        ]);
        if ($valid->fails()){
            return response($valid->errors(), 400);
        }
        $recommendation = $this->recommendationService->updateRecommendation($request, $recommendationId);

        return response($recommendation, 200);
    }

    /**
     * @api {delete} /api/admin/recommendation/{$recommendationId} удаление рекомендации города, региона, страны
     * @apiVersion 0.1.0
     * @apiName DeleteRecommendation
     * @apiGroup GeoRecommendationAdmin
     *
     * @apiHeader {string} Authorization access-token
     *
     * @apiParam {integer} $recommendationId идентификатор рекомендации города, региона, страны
     *
     * @apiSuccessExample {json} Ответ сервера в случае успеха:
        HTTP/1.1 200 OK
        {
            "message": "удалено"
        }
     *
     *
     * @param int $recommendationId id идентификатор города, страны, региона
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteRecommendation(int $recommendationId)
    {
        $valid = Validator([
            'id' => $recommendationId
        ], [
            'id' => 'required|integer|exists:recommendations,id',
        ]);
        if ($valid->fails()){
            return response($valid->errors(), 400);
        }
        $deleted = $this->recommendationService->deleteRecommendation($recommendationId);
        if ($deleted){
            return response()->json(['geo' =>[
                'Удалено'
            ]], 200);
        } else {
            return response()->json(['geo' =>[
                'Не найдено'
            ]], 404);
        }
    }
}
