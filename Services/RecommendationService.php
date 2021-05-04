<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Recommendation;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class RecommendationService
{
    use LocaleControlTrait;

    const LOCATION_TYPE_CITY = 'city';
    const LOCATION_TYPE_REGION = 'region';
    const LOCATION_TYPE_COUNTRY = 'country';

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * RecommendationService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Создание рекомендации города, региона, страны
     *
     * @param Request $request
     * @return Recommendation
     */
    public function createRecommendation(Request $request)
    {
        $locationType = $request->post('type');

        $recommendation  = new Recommendation();
        $recommendation->location_id = $request->post('location_id');
        $recommendation->type = $request->post('type');
        $recommendation->recommendation_ru = $request->get('recommendation_ru')  ?? "";
        $recommendation->recommendation_en = $request->get('recommendation_en')  ?? "";

        $recommendation->save();

        return $recommendation;
    }

    /**
     * Получение рекомендаций города, страны, региона
     *
     * @param string $locationType
     * @param int [$locationId]
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param string|null $locale
     * @param null|array $sorting
     * @return array
     * @throws ApiProblemException
     */
    public function getRecommendations(string $locationType, ?int $locationId, ?int $page, ?int $rowsPerPage, ?array $sorting = null, string $locale = 'ru')
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        if (!is_null($locationId)) $filter[] = ['location_id', $locationId];

        $qb = Recommendation::where($filter)->orderBy('id', 'asc');

        $qb->when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {
                foreach ($sorting as $key => $value) {
                    $orderBy = $query->orderBy($key, $value);
                }
                return $orderBy;
            } else {
                return $query->orderBy('updated_at', 'desc');
            }
        });

        if (!is_null($locale)){

            switch ($locale){
                case 'ru' :
                    $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_ru as recommendation']);
                    break;

                case 'en' :
                    $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_en as recommendation']);

                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }

        } else {
            $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_ru as recommendation']);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage,  $total, $items);
    }

    /**
     * Обновление рекомендации локации (город, регион, страна)
     *
     * @param Request $request
     * @param int $recommendationId
     * @return Recommendation
     * @throws ApiProblemException
     */
    public function updateRecommendation(Request $request, int $recommendationId)
    {
        $recommendation = Recommendation::find($recommendationId);


        if (is_null($recommendation)) throw new ApiProblemException('Рекомендация для локации не найдена', 404);

        $data = $request->only('type', 'location_id', 'recommendation_ru', 'recommendation_en');

        foreach ($data as $field => $value){
            $recommendation->$field = $value;
        }

        $recommendation->save();

        return $recommendation;
    }

    /**
     * Получение рекомендации
     *
     * @param int $recommendationId
     * @param null|string [$locale]
     * @return mixed
     * @throws ApiProblemException
     */
    public function getRecommendation(int $recommendationId, ?string $locale = 'ru')
    {
        $filter = [];
        $filter[] = ['id', $recommendationId];

        $qb = Recommendation::where($filter);
        $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_ru', 'recommendation_en']);

        $item = $qb->get()->first();

        return $item;
    }

    /**
     * Удаление рекомендации города, региона, страны
     *
     * @param int $recommendationId
     * @return bool
     */
    public function deleteRecommendation(int $recommendationId)
    {
        $recommendation = Recommendation::find($recommendationId);

        if (!is_null($recommendation)){
            $recommendation->delete();
            return true;
        } else return false;
    }

    public static function getPluralLocation($type) {
        $types = [
            'city' => 'cities',
            'region' => 'regions',
            'country' => 'countries'
        ];

        if (isset($types[$type])) {
            return $types[$type];
        }
    }

    /**
     * Получение рекомендаций города, страны, региона
     *
     * @param string $locationType
     * @param int $locationId
     * @param string|null $locale
     * @return array
     * @throws ApiProblemException
     */
    public static function getRecommendationsShort(string $locationType, ?int $locationId, ?string $locale)
    {
        $filter = [];
        if (!is_null($locationId)) $filter[] = ['location_id', $locationId];

        $qb = Recommendation::where($filter);

        if (!is_null($locale)){

            switch ($locale){
                case 'ru' :
                    $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_ru as recommendation']);
                    break;

                case 'en' :
                    $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_en as recommendation']);

                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }

        } else {
            $qb = $qb->select(['id', 'type', 'location_id', 'recommendation_ru as recommendation']);
        }

        $items = $qb->orderBy('id', 'asc')->get();

        return $items;
    }
}
