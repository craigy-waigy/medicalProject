<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Jobs\CollectMainSearchPrompt;
use App\Models\Country;
use App\Models\Disease;
use App\Models\DiseasesMedicalProfile;
use App\Models\FavoriteGeography;
use App\Models\FavoriteObject;
use App\Models\Mood;
use App\Models\ObjectMedicalProfile;
use App\Models\ObjectMedicalProfileExcludeDisease;
use App\Models\ObjectMood;
use App\Models\ObjectTherapy;
use App\Models\Region;
use App\Models\SeoFilterUrl;
use App\Models\SeoInformation;
use App\Models\SeoTemplate;
use App\Models\Service;
use App\Models\Therapy;
use App\Models\MedicalProfile;
use App\Models\City;
use App\Exceptions\UnsupportLocaleException;
use App\Models\ObjectPlace;
use App\Libraries\Models\PaginatorFormat;
use App\Traits\ElasticsearchTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SearchService extends Model
{
    use ElasticsearchTrait;
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * SearchService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Общий поиск
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param string $searchKey
     * @param string $locale
     * @return array
     * @throws UnsupportLocaleException
     */
    public function mainSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru')
    {
        $response = [
            'objects' => $this->objectSearch($page, $rowsPerPage, $searchKey, $locale, false),
            'geo' =>[
                'city' => $this->citySearch($page, $rowsPerPage, $searchKey, $locale),
                'region' => $this->regionSearch($page, $rowsPerPage, $searchKey, $locale),
                'country' => $this->countrySearch($page, $rowsPerPage, $searchKey, $locale),
            ],
            'medical_profile' => $this->medicalProfileSearch($page, $rowsPerPage, $searchKey, $locale),
            'disease' => $this->diseaseSearch($page, $rowsPerPage, $searchKey, $locale),
            'therapy' => $this->therapySearch($page, $rowsPerPage,  $searchKey, null, $locale),
        ];

        if (!is_null($searchKey))
            CollectMainSearchPrompt::dispatchNow($searchKey, $response, $locale);

        return $response;
    }

    /**
     * Всепоиск
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @return array
     * @throws UnsupportLocaleException
     */
    public function searchAll(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru')
    {
        $results = [];
        $skip = ($page - 1)* $rowsPerPage;

        if (is_null($searchKey))
            return $this->paginatorFormat->dataFormat($page, $rowsPerPage, 0, []);

        $totalObject = $this->objectSearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalCountry = $this->countrySearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalRegion = $this->regionSearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalCity = $this->citySearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalMedicalProfiles = $this->medicalProfileSearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalTherapy = $this->therapySearch($page, $rowsPerPage,  $searchKey, null, $locale)['total'];
        $totalDisease = $this->diseaseSearch($page, $rowsPerPage, $searchKey, $locale)['total'];

        $total = $totalCountry + $totalRegion + $totalCity + $totalObject + $totalMedicalProfiles + $totalTherapy + $totalDisease;

        $iteration = 1;

        $countryIterate = $regionIterate = $cityIterate = $objectIterate = $medicalProfileIterate =
        $therapyIterate = $diseaseIterate = true;

        while ( $countryIterate || $regionIterate || $cityIterate || $objectIterate
            || $medicalProfileIterate || $therapyIterate || $diseaseIterate){

            if ( $this->checkIterationList($totalCountry, $iteration, $rowsPerPage) ){
                $items = $this->countrySearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'country';
                    $results[] = $item;
                }
            } else
                $countryIterate = false;

            if ( $this->checkIterationList($totalRegion, $iteration, $rowsPerPage) ){
                $items = $this->regionSearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'region';
                    $results[] = $item;
                }
            } else
                $regionIterate = false;

            if  ( $this->checkIterationList($totalCity, $iteration, $rowsPerPage) ){
                $items = $this->citySearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'city';
                    $results[] = $item;
                }
            } else
                $cityIterate = false;

            if ( $this->checkIterationList($totalObject, $iteration, $rowsPerPage) ){
                $items = $this->objectSearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'object';
                    $results[] = $item;
                }
            } else
                $objectIterate = false;

            if  ( $this->checkIterationList($totalMedicalProfiles, $iteration, $rowsPerPage) ){
                $items = $this->medicalProfileSearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'medical_profile';
                    $results[] = $item;
                }
            }
            else
                $medicalProfileIterate = false;

            if  ( $this->checkIterationList($totalDisease, $iteration, $rowsPerPage) ){
                $items = $this->diseaseSearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'disease';
                    $results[] = $item;
                }
            } else
                $diseaseIterate = false;

            if ( $this->checkIterationList($totalTherapy, $iteration, $rowsPerPage) ) {
                $items = $this->therapySearch($iteration, $rowsPerPage,  $searchKey, null, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'therapy';
                    $results[] = $item;
                }
            } else
                $therapyIterate = false;

            $iteration++;
        }

        $results = collect($results);
        $results = $results->sortByDesc( 'score' )->values();
        $results = $results->splice($skip, $rowsPerPage);

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $results);
    }

    /**
     * Поиск курортов
     *
     * @param $page
     * @param $rowsPerPage
     * @param $searchKey
     * @param $locale
     * @return array
     * @throws UnsupportLocaleException
     */
    public function searchResort(int $page, int $rowsPerPage, ?string $searchKey, string $locale)
    {
        $results = [];
        $skip = ($page - 1)* $rowsPerPage;

        if (is_null($searchKey))
            return $this->paginatorFormat->dataFormat($page, $rowsPerPage, 0, []);

        $totalCountry = $this->countrySearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalRegion = $this->regionSearch($page, $rowsPerPage, $searchKey, $locale)['total'];
        $totalCity = $this->citySearch($page, $rowsPerPage, $searchKey, $locale)['total'];

        $total = $totalCountry + $totalRegion + $totalCity;

        $iteration = 1;
        $countryIterate = $regionIterate = $cityIterate = true;

        while ( $countryIterate || $regionIterate || $cityIterate ){
            if ( $this->checkIterationList($totalCountry, $iteration, $rowsPerPage) ){
                $items = $this->countrySearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'country';
                    $results[] = $item;
                }
            } else
                $countryIterate = false;

            if ( $this->checkIterationList($totalRegion, $iteration, $rowsPerPage)  ){
                $items = $this->regionSearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'region';
                    $results[] = $item;
                }
            } else
                $regionIterate = false;

            if  ( $this->checkIterationList($totalCity, $iteration, $rowsPerPage) ){
                $items = $this->citySearch($iteration, $rowsPerPage, $searchKey, $locale, true);
                foreach ($items['items'] as $item){
                    $item->type = 'city';
                    $results[] = $item;
                }
            } else
                $cityIterate = false;

            $iteration++;
        }

        $results = collect($results);
        $results = $results->sortByDesc( 'score' )->values();
        $results = $results->splice($skip, $rowsPerPage);

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $results);
    }

    /**
     * Проверка на добавление в листинг при итерации
     *
     * @param int $total
     * @param $iteration
     * @param $rowsPerPage
     * @return bool
     */
    public function checkIterationList(int $total, $iteration, $rowsPerPage)
    {
        return ($iteration * $rowsPerPage) <= ( $total + $rowsPerPage);
    }

    /**
     * Получение подсказки поиска
     *
     * @param null|string $searchKey
     * @param int $rowsPerPage
     * @return mixed
     */
    public function mainSearchPrompt(?string $searchKey, int $rowsPerPage)
    {
        if (!is_null($searchKey)){
            $searchKey = mb_strtolower($searchKey);
            $prompts = $this->searchESMainSearchPrompt($searchKey, $rowsPerPage);
        } else $prompts = [];

        return $prompts;
    }

    /**
     * Поиск объектов - санаториев
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool|null $extended
     * @return array
     * @throws UnsupportLocaleException
     */
    public function objectSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru', ?bool $extended = false)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['is_visibly', true];

        $queryBuilder = ObjectPlace::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESObject($locale, $searchKey);

            $queryBuilder->whereIn('id', $elasticData['ids']);
        }

        switch($locale){
            case 'ru':
                if ($extended)
                    $queryBuilder->select(['id', 'country_id', 'region_id', 'city_id', 'title_ru as title', 'alias', 'description_ru as description', 'viewing_count', 'street_view_link']);
                else
                    $queryBuilder->select(['id', 'country_id', 'region_id', 'city_id', 'title_ru as title', 'alias', 'viewing_count', 'street_view_link']);

                $queryBuilder->with('region:id,name_ru as name');
                $queryBuilder->with('city:id,name_ru as name');
                $queryBuilder->with('country:id,name_ru as name');
                break;
            case 'en' :
                if ($extended)
                    $queryBuilder->select(['id', 'country_id', 'region_id', 'city_id', 'title_en as title', 'alias', 'description_en as description', 'viewing_count', 'street_view_link']);
                else
                    $queryBuilder->select(['id', 'country_id', 'region_id', 'city_id', 'title_en as title', 'alias', 'viewing_count', 'street_view_link']);

                $queryBuilder->with('region:id,name_en as name');
                $queryBuilder->with('city:id,name_en as name');
                $queryBuilder->with('country:id,name_en as name');
                break;

            default : throw new UnsupportLocaleException();
        }
        if ($extended)
            $queryBuilder->with('moderatedImages:id,object_id,thumbs as image,description,sorting_rule,is_main');

        $total = $queryBuilder->count();
        $items = $queryBuilder->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();
        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            //$item->title = $elasticData['hits'][$item->id]['highlight']['title'][0] ?? strip_tags($item->title);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск городов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool $extended
     * @param array|null $ids
     * @param array|null $aliases
     * @param bool|null $hasObjects
     * @param array|null $params
     * @return array
     * @throws UnsupportLocaleException
     */
    public function citySearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                               bool $extended = false, ?array $ids = null, ?array $aliases = null,
                               ?bool $hasObjects = null, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];

        $qb = City::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESCity($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }

        if (!empty($params['sorting'])){
            foreach ($params['sorting'] as $fields => $sort){
                $qb->orderBy($fields, $sort);
            }
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE city_id = cities.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'country_id', 'region_id', 'name_ru as name', 'description_ru as description', 'crop_image as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'country_id', 'region_id', 'name_ru as name', 'alias'], $extendedSelect));
                    $qb->with('seo:id,city_id,title_ru as title,order');
                }
                $qb->with('region:id,name_ru as name');
                $qb->with('country:id,name_ru as name');
                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'country_id', 'region_id', 'name_en as name', 'description_en as description', 'crop_image_en as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'country_id', 'region_id', 'name_en as name', 'alias'], $extendedSelect));
                    $qb->with('seo:id,city_id,title_en as title,order');
                }
                $qb->with('region:id,name_en as name');
                $qb->with('country:id,name_en as name');
                break;

            default : throw new UnsupportLocaleException();
        }
        if (!is_null($ids)){
            $qb->whereIn('id', $ids);
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        } else $qb->where('is_visible', true);

        if (!is_null($hasObjects) && (bool)$hasObjects){
            $qb->whereRaw("id in(
            SELECT city_id FROM objects WHERE is_visibly = true
            )");
        }
        if (!empty($params['country_id'])){
            $qb->where('country_id', (int)$params['country_id']);
        }
        if ($extended || !empty($aliases)){
            $qb->withCount(['objects AS count_objects' => function($q){ $q->where('is_visibly', true); }]);
            $qb = $this->getCityLocaleFilter($qb, $locale);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();

        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск регионов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool $extended
     * @param array|null $ids
     * @param array|null $aliases
     * @param bool|null $hasObjects
     * @param array|null $params
     * @return array
     * @throws UnsupportLocaleException
     */
    public function regionSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                                 bool $extended = false, ?array $ids = null, ?array $aliases = null,
                                 ?bool $hasObjects = null, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];

        $qb = Region::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESRegion($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }

        if (!empty($params['sorting'])){
            foreach ($params['sorting'] as $fields => $sort){
                $qb->orderBy($fields, $sort);
            }
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE region_id = regions.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'country_id', 'name_ru as name', 'description_ru as description', 'crop_image as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'country_id', 'name_ru as name', 'alias'], $extendedSelect));
                    $qb->with('seo:id,region_id,title_ru as title,order');
                }
                $qb->with('country:id,name_ru as name');
                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'country_id', 'name_en as name', 'description_en as description', 'crop_image_en as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'country_id', 'name_en as name', 'alias'], $extendedSelect));
                    $qb->with('seo:id,region_id,title_en as title,order');
                }
                $qb->with('country:id,name_ru as name');
                break;

            default : throw new UnsupportLocaleException();
        }
        if (!is_null($ids)){
            $qb->whereIn('id', $ids);
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        } else $qb->where('is_visible', true);

        if (!is_null($hasObjects) && (bool)$hasObjects){
            $qb->whereRaw("id in(
            SELECT region_id FROM objects WHERE is_visibly = true
            )");
        }
        if (!empty($params['country_id'])){
            $qb->where('country_id', (int)$params['country_id']);
        }
        if ($extended || !empty($aliases)){
            $qb->withCount(['objects AS count_objects' => function($q){ $q->where('is_visibly', true); }]);
            $qb = $this->getRegionLocaleFilter($qb, $locale);
        }
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();

        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск стран
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool $extended
     * @param array|null $ids
     * @param array|null $aliases
     * @param bool|null $hasObjects
     * @param array|null $params
     * @return array
     * @throws UnsupportLocaleException
     */
    public function countrySearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                                  bool $extended = false, ?array $ids = null, ?array $aliases = null,
                                  ?bool $hasObjects = null, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];

        $qb = Country::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESCountry($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }

        if (!empty($params['sorting'])){
            foreach ($params['sorting'] as $fields => $sort){
                $qb->orderBy($fields, $sort);
            }
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE country_id = countries.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'name_ru as name', 'description_ru as description', 'crop_image as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_ru as name', 'alias' ], $extendedSelect));
                    $qb->with('seo:id,country_id,title_ru as title,order');
                }
                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'name_en as name', 'description_en as description', 'crop_image_en as image', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_en as name', 'alias'], $extendedSelect));
                    $qb->with('seo:id,country_id,title_en as title,order');
                }
                break;
            default : throw new UnsupportLocaleException();
        }
        if (!is_null($ids)){
            $qb->whereIn('id', $ids);
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        } else $qb->where('is_visible', true);

        if (!is_null($hasObjects) && (bool)$hasObjects){
            $qb->whereRaw("id in(
            SELECT country_id FROM objects WHERE is_visibly = true
            )");
        }
        if ($extended){
            $qb->withCount(['objects AS count_objects' => function($q){ $q->where('is_visibly', true); }]);
            $qb = $this->getCountryLocaleFilter($qb, $locale);
        }
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();

        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск мед. профилей
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool $extended
     * @param array|null $aliases
     * @param array|null $params
     * @param array|null $objectIds
     * @return array
     * @throws UnsupportLocaleException
     */
    public function medicalProfileSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                                         bool $extended = false, ?array $aliases = null, ?array $params = null, ?array $objectIds = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['active', true];

        $qb = MedicalProfile::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESMedicalProfile($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        }
        if(isset($params['basic'])){
            $qb->where('basic', $params['basic']);
        }

        if (!is_null($objectIds)){
            $qb->whereHas('objects', function ($q) use($objectIds){
                $q->where('is_visibly', true);
                $q->whereIn('objects.id', $objectIds);
            });
        } else if (isset($params['city_id']) || isset($params['region_id']) || isset($params['country_id'])) {
            $query = ObjectPlace::where('is_visibly', true);
            if(isset($params['city_id'])){
                $query->where('city_id', $params['city_id']);
            }
            if(isset($params['region_id'])){
                $query->where('region_id', $params['region_id']);
            }
            if(isset($params['country_id'])){
                $query->where('country_id', $params['country_id']);
            }
            $objectPlaceIds = $query->select('id')->pluck('id');

            $qb->whereHas('objects', function ($q) use($objectPlaceIds){
                $q->where('is_visibly', true);
                $q->whereIn('objects.id', $objectPlaceIds);
            });
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE medical_profile_id = medical_profiles.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'name_ru as name', 'description_ru as description', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_ru as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:medical_profile_id,order,title_ru as title');
                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'name_en as name', 'description_en as description', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_en as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:medical_profile_id,order,title_en as title');
                break;

            default : throw new UnsupportLocaleException();
        }
        $qb->with('images');
        $qb = $this->getMedicalProfileLocaleFilter($qb, $locale);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();
        if ($extended){
            $items = $this->objectHydrate($items, 'medical_profile', $params);
        }
        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response =  $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск заболеваний
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param bool $extended
     * @param array|null $aliases
     * @param array|null $objectIds
     * @return array
     * @throws UnsupportLocaleException
     */
    public function diseaseSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                                  bool $extended = false, ?array $aliases = null, ?array $objectIds = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['active', true];

        $qb = Disease::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESDisease($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE disease_id = diseases.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'name_ru as name', 'desc_ru as description', 'alias'], $extendedSelect));

                } else {
                    $qb->select(array_merge(['id', 'name_ru as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:disease_id,order,title_ru as title');

                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'name_en as name', 'desc_en as description', 'alias'], $extendedSelect));

                } else {
                    $qb->select(array_merge(['id', 'name_en as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:disease_id,order,title_en as title');

                break;

            default : throw new UnsupportLocaleException();
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        }

        if (!is_null($objectIds)){
            $objectIds = implode(',', $objectIds);


            /* Старая логика поиска через заболевания, привязанные к профилям глобально*/
           /* $qb->whereRaw("
            id in(
            SELECT disease_id FROM disease_medical_profile WHERE medical_profile_id in(
                SELECT obm.medical_profile_id FROM object_medical_profiles obm LEFT JOIN objects ob ON obm.object_id = ob.id
                WHERE ob.is_visibly = true AND ob.id in({$objectIds})
                )
            )
            ");*/

            //Новая логика поиска заболеваний через заболевания указанные для каждого объекта
            $qb->whereRaw("
            id not in(
            SELECT DISTINCT disease_id FROM object_medical_profile_exclude_diseases 
            WHERE object_id in ({$objectIds})             
                )
            ");
        }
        $qb = $this->getDiseaseLocaleFilter($qb, $locale);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();
        if ($extended){
            $items = $this->objectHydrate($items, 'disease');
        }
        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск методов лечения
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $ids
     * @param string $locale
     * @param bool $extended
     * @param array|null $aliases
     * @param array|null $objectIds
     * @return array
     * @throws UnsupportLocaleException
     */
    public function therapySearch(int $page, int $rowsPerPage, ?string $searchKey, ?array $ids = null,
                                  string $locale = 'ru', bool $extended = false, ?array $aliases = null, ?array $objectIds = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['active', true];

        $qb = Therapy::where($filter);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $elasticData = $this->searchESTherapy($locale, $searchKey);

            $qb->whereIn('id', $elasticData['ids']);
        }

        if (!is_null($ids)){
            $qb->whereIn('id', $ids);
        }
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        }
        if (!is_null($objectIds)){
            $qb->whereHas('objects', function ($q) use($objectIds){
                $q->whereIn('objects.id', $objectIds);
                $q->where('objects.is_visibly', true);
            });
        }

        $extendedSelect = [DB::raw("(SELECT count(*) FROM viewing_counters WHERE therapy_id = therapy.id) as viewing_count")];

        switch($locale){
            case 'ru':
                if ($extended){
                    $qb->select(array_merge(['id', 'name_ru as name', 'desc_ru as description', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_ru as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:therapy_id,order,title_ru as title');
                break;
            case 'en' :
                if ($extended){
                    $qb->select(array_merge(['id', 'name_en as name', 'desc_en as description', 'alias'], $extendedSelect));
                } else {
                    $qb->select(array_merge(['id', 'name_en as name', 'alias'], $extendedSelect));
                }
                $qb->with('seo:therapy_id,order,title_en as title');
                break;

            default : throw new UnsupportLocaleException();
        }
        $qb = $this->getTherapyLocaleFilter($qb, $locale);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('viewing_count', 'desc')->get();
        if ($extended){
            $items = $this->objectHydrate($items, 'therapy');
        }
        $preparedItems = [];
        foreach ($items as $item){
            $item->score = $elasticData['hits'][$item->id]['score'] ?? null;
            $item->name = $elasticData['hits'][$item->id]['highlight']['name'][0] ?? strip_tags($item->name);

            if ($extended)
                $item->description = $elasticData['hits'][$item->id]['highlight']['description'][0] ?? strip_tags($item->description);

            $preparedItems[] = $item;
        }
        $preparedItems = collect($preparedItems);
        $preparedItems = $preparedItems->sortByDesc('score')->values();

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['max_score'] = $elasticData['max_score'] ?? null;

        return $response;
    }

    /**
     * Поиск услуг
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param array|null $aliases
     * @param array|null $objectIds
     * @return array
     * @throws UnsupportLocaleException
     */
    public function serviceSearch(int $page, int $rowsPerPage, ?string $searchKey, string $locale = 'ru',
                                  ?array $aliases = null, ?array $objectIds = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);
        $search = ['searchKey' => $searchKey, 'locale' => $locale];

        $qb = Service::where($filter)->
        when($search, function ($query, $search){
            if ( !is_null($search['searchKey']) ){
                switch($search['locale']){
                    case 'ru':
                        $query = $query->whereRaw("lower(name_ru) LIKE '%{$search['searchKey']}%'");
                        break;
                    case 'en' :
                        $query = $query->whereRaw("lower(name_en) LIKE '%{$search['searchKey']}%'");
                        break;

                    default : throw new UnsupportLocaleException();
                }
                return $query;
            }
        });
        if (!is_null($aliases)){
            $qb->whereIn('alias', $aliases);
        }
        if (!is_null($objectIds)){
            $service = $this;
            $qb->whereHas('objects', function ($q) use($objectIds, $locale, $service){
                $q = $service->getObjectsLocaleFilter($q, $locale);
                $q->whereIn('objects.id', $objectIds);
            });
            $qb->where('is_filter', true);
        }
        switch($locale){
            case 'ru':
                $qb->select(['id', 'name_ru as name', 'alias']);
                $qb->with('seo:service_id,order,title_ru as title');
                break;
            case 'en' :
                $qb->select(['id', 'name_en as name', 'alias']);
                $qb->with('seo:service_id,order,title_en as title');
                break;

            default : throw new UnsupportLocaleException();
        }
        $qb = $this->getServiceLocaleFilter($qb, $locale);
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Добавление количества объектов
     *
     * @param $data
     * @param string $for disease|therapy|medical_profile|city|region|country
     * @return array
     */
    public function objectHydrate($data, string $for, ?array $params = null)
    {
        $result = [];
        switch($for){
            case 'disease' :
                foreach ($data as $item){
                    /*$count = DB::table('objects')
                        ->where('objects.is_visibly', '=', true )
                        ->whereRaw("id in(
                            SELECT object_id FROM object_medical_profiles
                            WHERE medical_profile_id in(
                                SELECT medical_profile_id FROM disease_medical_profile WHERE disease_id = {$item->id}
                            )
                        )" )

                        ->count()
                    ;*/

                    //
                    $count = DB::table('objects')
                        ->where('objects.is_visibly', '=', true )
                        ->whereRaw("
                            id not in(
                            SELECT DISTINCT object_id FROM object_medical_profile_exclude_diseases 
                            WHERE disease_id in ({$item->id})             
                            )"
                        )
                        ->count()
                    ;

                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;

            case 'therapy' :
                foreach ($data as $item){
                    $count = DB::table('object_therapies')
                        ->where('object_therapies.therapy_id', '=', $item->id )
                        ->leftJoin('objects', 'object_therapies.object_id', '=', 'objects.id')
                        ->where('objects.is_visibly', '=', true )
                        ->distinct('object_therapies.object_id')->count()
                    ;
                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;

            case 'medical_profile' :
                foreach ($data as $item){
                    $count = DB::table('object_medical_profiles')
                        ->where('medical_profile_id', '=', $item->id )
                        ->leftJoin('objects', 'object_medical_profiles.object_id', '=', 'objects.id')
                        ->where('objects.is_visibly', '=', true );
                    if (isset($params['country_id']))  $count = $count->where('objects.country_id', '=', $params['country_id'] );
                    if (isset($params['region_id']))  $count = $count->where('objects.region_id', '=', $params['region_id'] );
                    if (isset($params['city_id']))  $count = $count->where('objects.city_id', '=', $params['city_id'] );
                    $count = $count->distinct('object_id')->count();

                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;

            case 'city' :
                foreach ($data as $item){
                    $count = DB::table('objects')
                        ->where('city_id', '=', $item->id )
                        ->distinct('id')->count()
                    ;
                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;

            case 'region' :
                foreach ($data as $item){
                    $count = DB::table('objects')
                        ->where('region_id', '=', $item->id )
                        ->distinct('id')->count()
                    ;
                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;

            case 'country' :
                foreach ($data as $item){
                    $count = DB::table('objects')
                        ->where('country_id', '=', $item->id )
                        ->distinct('id')->count()
                    ;
                    $item->count_objects = $count;
                    $result[] = $item;
                }
                break;
        }

        return $result;
    }

    /**
     * Расширеный поиск объектов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param string $locale
     * @param array|null $params
     * @param float|null $lat
     * @param float|null $lon
     * @return array
     * @throws UnsupportLocaleException
     */
    public function extendedObjectSearch(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting, string $locale,
                                         ?array $params, float $lat = null, float $lon = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['is_visibly', true];

        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);
        $search = ['searchKey' => $searchKey, 'locale' => $locale];

        foreach ($params as $key=>$value) {
            if (!is_null($value)) $search[$key] = $value;
        }

        $queryBuilder = ObjectPlace::where($filter)
            ->when($search, function ($query, $search){
                switch($search['locale']){
                    case 'ru':
                        $query = $query->with('moods:moods.id,moods.name_ru as name,moods.alias,moods.image,moods.crop_image');
                        break;
                    case 'en' :
                        $query = $query->with('moods:moods.id,moods.name_en as name,moods.alias,moods.image,moods.crop_image');
                        break;

                    default : throw new UnsupportLocaleException();
                }

                return $query;
            })
            ->when($search, function ($query, $search){
                if ( !empty($search['searchKey']) ){
                    switch($search['locale']){
                        case 'ru':
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$search['searchKey']}%'");
                            break;
                        case 'en' :
                            $query = $query->whereRaw("lower(title_en) LIKE '%{$search['searchKey']}%'");
                            break;

                        default : throw new UnsupportLocaleException();
                    }
                }
                if (isset($search['in_action']) && $search['in_action'] == true){
                    $query = $query->where('in_action', true);
                }

                return $query;
            });

        $objectIds = [];
        $hasFilter = false;
        if (isset($search['medical_profiles'])){
            $hasFilter = true;
            if (!is_array($search['medical_profiles']))
                $medProfileIds = json_decode($search['medical_profiles'], true);
            else
                $medProfileIds = $search['medical_profiles'];

            $count = count($medProfileIds);
            $items = ObjectMedicalProfile::whereIn('medical_profile_id', $medProfileIds)
                ->select(['object_id'])
                ->groupBy('object_id')
                ->havingRaw("count(object_id) = {$count}")
                ->get();
            foreach ($items as $item){
                $objectIds[] = $item->object_id;
            }
        }

        if (!empty($search['moods'])){
            $hasFilter = true;
            $moodsIds = Mood::whereIn('alias', $search['moods'])->get()->pluck('id');
            $items = ObjectMood::whereIn('mood_id', $moodsIds)
                ->select(['object_id'])
                ->groupBy('object_id')
                ->get();

            foreach ($items as $item){
                $objectIds[] = $item->object_id;
            }
        }

        if (isset($search['services'])){
            if (!is_array($search['services']))
                $serviceIds = json_decode($search['services'], true);
            else
                $serviceIds = $search['services'];

            $count = count($serviceIds);
            $items = ObjectService::whereIn('service_id', $serviceIds)
                ->select(['object_id'])
                ->groupBy('object_id')
                ->havingRaw("count(object_id) = {$count}");
            if ( $hasFilter ){
                $items->whereIn('object_id', $objectIds);
                $objectIds = [];
            }
            $items = $items->get();
            foreach ($items as $item){
                $objectIds[] = $item->object_id;
            }
            $hasFilter = true;
        }
        if (isset($search['therapies'])){

            if (!is_array($search['therapies']))
                $therapyIds = json_decode($search['therapies'], true);
            else
                $therapyIds = $search['therapies'];

            $count = count($therapyIds);
            $items = ObjectTherapy::whereIn('therapy_id', $therapyIds)
                ->select(['object_id'])
                ->groupBy('object_id')
                ->havingRaw("count(object_id) = {$count}");
            if ( $hasFilter ){
                $items->whereIn('object_id', $objectIds);
                $objectIds = [];
            }
            $items = $items->get();
            foreach ($items as $item){
                $objectIds[] = $item->object_id;
            }
            $hasFilter = true;
        }
        if (isset($search['diseases'])){

            if (!is_array($search['diseases']))
                $diseaseIds = json_decode($search['diseases'], true);
            else
                $diseaseIds = $search['diseases'];
            $diseasesMedicals = DiseasesMedicalProfile::whereIn('disease_id', $diseaseIds)->get();

            $medProfileIds = [];
            foreach ($diseasesMedicals as $diseasesMedical){
                $medProfileIds[] = $diseasesMedical->medical_profile_id;
            }

            $diseasesMedicalsOff = ObjectMedicalProfileExcludeDisease::whereIn('disease_id', $diseaseIds)->get();
            $IdsWithExcludeDiseases = [];
            foreach ($diseasesMedicalsOff as $offDisease){
                $IdsWithExcludeDiseases[] = $offDisease->object_id;
            }

            $medProfiles = ObjectMedicalProfile::whereIn('medical_profile_id', $medProfileIds);
            if ( $hasFilter ){
                $medProfiles->whereIn('object_id', $objectIds);
                $objectIds = [];
            }
            $medProfiles = $medProfiles->get();
            foreach ($medProfiles as $medProfile){
                if (!in_array($medProfile->object_id, $IdsWithExcludeDiseases)){
                    $objectIds[] = $medProfile->object_id;
                }
            }
            $hasFilter = true;
        }
        if (isset($search['stars'])){
            if (!is_array($search['stars']))
                $stars = json_decode($search['stars'], true);
            else
                $stars = $search['stars'];

            $queryBuilder->whereIn('stars', $stars);
        }

        if (isset($search['city_id'])){
            $queryBuilder->where('city_id', '=', $search['city_id']);
        }
        if (isset($search['region_id'])){
            $queryBuilder->where('region_id', '=', $search['region_id']);
        }
        if (!empty($search['city_aliases'])){
            $aliases = '';
            foreach ($search['city_aliases'] as $key => $alias){
                if ($key == 0 )
                    $aliases = "'{$alias}'";
                else
                    $aliases .= ",'{$alias}'";
            }
            $queryBuilder->whereRaw("city_id in(SELECT id FROM cities WHERE alias in($aliases) )");
        }
        if (!empty($search['region_aliases'])){
            $aliases = '';
            foreach ($search['region_aliases'] as $key => $alias){
                if ($key == 0 )
                    $aliases = "'{$alias}'";
                else
                    $aliases .= ",'{$alias}'";
            }
            $queryBuilder->whereRaw("region_id in(SELECT id FROM regions WHERE alias in($aliases) )");
        }
        if (!empty($search['country_aliases'])){
            $aliases = '';
            foreach ($search['country_aliases'] as $key => $alias){
                if ($key == 0 )
                    $aliases = "'{$alias}'";
                else
                    $aliases .= ",'{$alias}'";
            }
            $queryBuilder->whereRaw("country_id in(SELECT id FROM countries WHERE alias in($aliases) )");
        }

        if (isset($search['moods']) && $search['moods'] && $moodsIds){
            $objectIdsByMoodIds = ObjectMood::whereIn('mood_id', $moodsIds)->pluck('object_id');
            $queryBuilder->whereIn('id', $objectIdsByMoodIds);
        }

        if (isset($search['ids'])){
            $queryBuilder->whereIn('id', $search['ids']);
        }

        $queryBuilder->whereHas('moderatedImages', function ($q){
            $q->havingRaw("count(*) > 0")->groupBy('id');
        });

        /**
         * Поиск рядом
         */
        if (!is_null($lat) && !is_null($lon)){
            $queryBuilder = $this->prepareQuery($queryBuilder, $locale);
            $queryBuilder->whereNotNull('lat');
            $queryBuilder->whereNotNull('lon');
            if (isset($search['beside_object_id'])){ //Если поиск рядом с санаторием - исключаем из выборки сам санаторий
                $queryBuilder->where('id', '<>', $search['beside_object_id']);
                $besideItem = ObjectPlace::find($search['beside_object_id']);
                $lat = $besideItem->lat;
                $lon = $besideItem->lon;
            }
            if (isset($search['beside_country_id'])){ //Если поиск рядом со страной - исключаем из выборки саму страну
                $queryBuilder->where('country_id', '<>', $search['beside_country_id']);
                $besideItem = Country::find($search['beside_country_id']);
                $lat = $besideItem->latitude;
                $lon = $besideItem->longitude;
            }
            if (isset($search['beside_region_id'])){ //Если поиск рядом с регионом - исключаем из выборки сам регион
                $queryBuilder->where('region_id', '<>', $search['beside_region_id']);
                $besideItem = Region::find($search['beside_region_id']);
                $lat = $besideItem->latitude;
                $lon = $besideItem->longitude;
            }
            if (isset($search['beside_city_id'])){ //Если поиск рядом с городом - исключаем из выборки сам город
                $queryBuilder->where('city_id', '<>', $search['beside_city_id']);
                $besideItem = City::find($search['beside_city_id']);
                $lat = $besideItem->latitude;
                $lon = $besideItem->longitude;
            }
            if ($hasFilter){
                $queryBuilder->whereIn('id', $objectIds);
            }

            $objects = $queryBuilder->get();
            $items = [];
            foreach ($objects as $object){
                if (!is_null($object->lat) && !is_null($object->lon)){
                    $object->radius = $this->calculateRadius($lat, $lon, $object->lat, $object->lon);
                    $items[] = $object;
                }
            }
            $items = collect($items);

            if ( !is_null($sorting)) {
                foreach ($sorting as $key => $value) {
                    if ($key == 'expensive'){
                        // сортировка дешевле
                        $items = $items->where('expensive', '=', true);
                    } elseif ($key == 'popular' ){
                        if ($value == 'asc')
                            $items = $items->sortBy($key);
                        else
                            $items = $items->sortByDesc($key);

                    } elseif ($key == 'title'){
                        if ($locale == 'ru'){
                            if ($value == 'asc')
                                $items = $items->sortBy('title_ru');
                            else
                                $items = $items->sortByDesc('title_ru');
                        }
                        if ($locale == 'en'){
                            if ($value == 'asc')
                                $items = $items->sortBy('title_en');
                            else
                                $items = $items->sortByDesc('title_en');
                        }

                    } else {
                        if ($value == 'asc')
                            $items = $items->sortBy($key);
                        else
                            $items = $items->sortByDesc($key);
                    }
                }
            } else { // Если сортировка не отправлена - сортируем по расстоянию
                $items = $items->sortBy('radius');
            }

            $total = $queryBuilder->count();
            $objectAll = $queryBuilder->get();
            $items = $items->splice($skip, $rowsPerPage);

        } else {
            $queryBuilder = $this->prepareQuery($queryBuilder, $locale);
            if ( isset($search['on_main_page']) && is_null($sorting) ){
                $queryBuilder->orderBy('on_main_page', 'desc');
                $queryBuilder->orderBy('priority_of_showing', 'desc');
            }
            $queryBuilder->when($sorting, function ($query, $sorting) use ($locale){
                if ( !is_null($sorting)) {
                    foreach ($sorting as $key => $value) {
                        if ($key == 'expensive'){
                            // сортировка дешевле
                            $query = $query->where('expensive', true);
                        } elseif ($key == 'popular' ){
                            $query = $query->orderBy('viewing_count', $value);

                        } elseif ($key == 'title'){
                            if ($locale == 'ru') $query = $query->orderBy('title_ru', $value);
                            if ($locale == 'en') $query = $query->orderBy('title_en', $value);

                        } else {
                            $query = $query->orderBy($key, $value);
                        }
                    }
                    return $query;
                } else { // Если сортировка не отправлена - сортируем рандомно
                    return $query = $query->orderBy('priority_of_showing', 'desc');
                }
            });
            if ( is_null($sorting) ){
                $queryBuilder->orderBy('priority_of_showing', 'desc');
            }
            if ($hasFilter){
                $queryBuilder->whereIn('id', $objectIds);
            }

            $total = $queryBuilder->count();
            $objectAll = $queryBuilder->get();
            $items = $queryBuilder->skip($skip)->take($rowsPerPage)->get();
        }
        $withFavorites = [];
        foreach ($items as $item){
            $withFavorites[] = $item->isFavorite();
        }
        /**
         * Response data
         */
        $responseData = ['objectIds' => null, 'stars' => [], 'in_action' => false];
        foreach ($objectAll as $item){
            $responseData['objectIds'][] = $item->id;
            if ( !in_array($item->stars, $responseData['stars']) && !is_null($item->stars))
                $responseData['stars'][] = $item->stars;
            if ($item->in_action)
                $responseData['in_action'] = true;
        }
        $objectsResult = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
        $objectsResult['response_data'] = $responseData;

        return $objectsResult;
    }

    /**
     * Подготовка запроса
     *
     * @param $queryBuilder
     * @param $locale
     * @return mixed
     * @throws UnsupportLocaleException
     */
    public function prepareQuery($queryBuilder, $locale)
    {
        switch($locale){
            case 'ru':
                $queryBuilder->with(
                    'moderatedImages:id,object_id,thumbs as image,sorting_rule,is_main',
                    'country:id,name_ru as name,alias',
                    'region:id,name_ru as name,alias',
                    'city:id,name_ru as name,alias');
                $queryBuilder->select(['id', 'title_ru as title', 'stars', 'country_id', 'region_id', 'city_id', 'lat', 'lon', 'alias', 'full_rating', 'heating_rating', 'min_price', 'in_action',
                    DB::raw("(SELECT online as max FROM sanatorium_doctors sd WHERE sd.object_id = objects.id ORDER BY online DESC LIMIT 1) as doctor_online")]);
                $medicalProfilesSelect = ['medical_profiles.id', 'name_ru as name', 'alias'];
                break;

            case 'en' :
                $queryBuilder->with(
                    'moderatedImages:id,object_id,thumbs as image,sorting_rule,is_main',
                    'country:id,name_en as name,alias',
                    'region:id,name_en as name,alias',
                    'city:id,name_en as name,alias');
                $queryBuilder->select(['id', 'title_en as title', 'stars', 'country_id', 'region_id', 'city_id', 'lat', 'lon', 'alias', 'full_rating', 'heating_rating', 'min_price', 'in_action',
                    DB::raw("(SELECT online as max FROM sanatorium_doctors sd WHERE sd.object_id = objects.id ORDER BY online DESC LIMIT 1) as doctor_online")]);
                $medicalProfilesSelect = ['medical_profiles.id', 'name_en as name', 'alias'];
                break;

            default : throw new UnsupportLocaleException();
        }
        $queryBuilder = $this->getObjectsLocaleFilter($queryBuilder, $locale);
        $queryBuilder = $this->getWithMedicalProfilesLocaleFilter($queryBuilder, $locale, 'medicalProfilesPublic', $medicalProfilesSelect);

        return $queryBuilder;
    }

    /**
     * Рассчет расстояния по координатам
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return int
     */
    public function calculateRadius(float $lat1, float $lon1, float $lat2, float $lon2)
    {
        /**
         * http://wiki.gis-lab.info/w/%D0%92%D1%8B%D1%87%D0%B8%D1%81%D0%BB%D0%B5%D0%BD%D0%B8%D0%B5_%D1%80%D0%B0%D1%81%D1%81%D1%82%D0%BE%D1%8F%D0%BD%D0%B8%D1%8F_%D0%B8_%D0%BD%D0%B0%D1%87%D0%B0%D0%BB%D1%8C%D0%BD%D0%BE%D0%B3%D0%BE_%D0%B0%D0%B7%D0%B8%D0%BC%D1%83%D1%82%D0%B0_%D0%BC%D0%B5%D0%B6%D0%B4%D1%83_%D0%B4%D0%B2%D1%83%D0%BC%D1%8F_%D1%82%D0%BE%D1%87%D0%BA%D0%B0%D0%BC%D0%B8_%D0%BD%D0%B0_%D1%81%D1%84%D0%B5%D1%80%D0%B5
         */
        $pi = pi();
        $rad = 6372795;

        $latitude1 = $lat1 * $pi/180;
        $latitude2 = $lat2 * $pi/180;
        $longtitude1 = $lon1 * $pi/180;
        $longtitude2 = $lon2 * $pi/180;

        //косинусы и синусы широт и разницы долгот
        $cl1 = cos($latitude1);
        $cl2 = cos($latitude2);
        $sl1 = sin($latitude1);
        $sl2 = sin($latitude2);
        $delta = $longtitude2 - $longtitude1;
        $cdelta = cos($delta);
        $sdelta = sin($delta);

        //вычисления длины большого круга
        $y = sqrt( pow($cl2 * $sdelta, 2) + pow($cl1 * $sl2 - $sl1 * $cl2 * $cdelta, 2) );
        $x = $cl1 * $sl2 + $cl1 * $cl2 * $cdelta;
        $ad = atan2($y, $x);

        $radius = round( $ad * $rad );

        return $radius;
    }

    /**
     * Получение объектов для показа на различных страницах
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param string $locale
     * @param bool|null $onMainPage
     * @return array
     * @throws UnsupportLocaleException
     */
    public function showObject(int $page, int $rowsPerPage, string $locale, ?bool $onMainPage = false)
    {
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['is_visibly', true];

        $skip = ($page - 1)* $rowsPerPage;

        $queryBuilder = ObjectPlace::where($filter);
        if ($onMainPage){
            $queryBuilder->orderBy('priority_of_showing', 'asc');
            $queryBuilder->orderBy('on_main_page', 'desc');
        }
        $queryBuilder = $this->prepareQuery($queryBuilder, $locale);

        $total = $queryBuilder->count();
        $items = $queryBuilder->skip($skip)->take($rowsPerPage)->orderBy('priority_of_showing', 'desc')->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Поиск географии
     *
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param array|null $countryIds
     * @param array|null $regionIds
     * @param array|null $cityIds
     * @param array|null $objectIds
     * @return array
     * @throws UnsupportLocaleException
     */
    public function geographySearch(int $rowsPerPage, ?string $searchKey, string $locale,
                                    ?array $countryIds = null, ?array $regionIds = null, ?array $cityIds = null, ?array $objectIds = null)
    {
        $searchKey = mb_strtolower($searchKey);

        $cities = City::whereNotNull('id');
        if (is_null($cityIds)){
            $cities->whereRaw("id in(SELECT city_id FROM objects) ");
            $withDescription = false;
        } else {
            $cities->whereIn('id', $cityIds);
            $cities->where('is_visible', true);
            $withDescription = true;
        }
        if (!is_null($objectIds)){
            $cities->whereHas('objects', function ($q) use($objectIds){
                $q->whereIn('id', $objectIds);
                $q->where('is_visibly', true);
            });
        }
        $cities = $this->prepareGeoQuery($cities, $locale, 'city', $searchKey, $withDescription);
        $cities = $cities->whereNotNull('alias')->take($rowsPerPage)->get();
        $itemsCity = [];
        foreach ($cities as  $city){
            $city->type = 'city';
            $itemsCity[] = $city;
        }

        $regions = Region::whereNotNull('id');
        if (is_null($regionIds)){
            $regions->whereRaw("id in(SELECT region_id FROM objects)");
            $withDescription = false;
        } else {
            $regions->whereIn('id', $regionIds);
            $regions->where('is_visible', true);
            $withDescription = true;
        }
        if (!is_null($objectIds)){
            $regions->whereHas('objects', function ($q) use($objectIds){
                $q->whereIn('id', $objectIds);
                $q->where('is_visibly', true);
            });
        }
        $regions = $this->prepareGeoQuery($regions, $locale, 'region', $searchKey, $withDescription);
        $regions->whereNotNull('alias');
        $regions = $regions->take($rowsPerPage)->get();
        $itemsRegion = [];
        foreach ($regions as  $region){
            $region->type = 'region';
            $itemsRegion[] = $region;
        }

        $countries = Country::whereNotNull('id');
        if (is_null($countryIds)){
            $countries->whereRaw("id in(SELECT country_id FROM objects)");
            $withDescription = false;
        } else {
            $countries->whereIn('id', $countryIds);
            $countries->where('is_visible', true);
            $withDescription = true;
        }
        if (!is_null($objectIds)){
            $countries->whereHas('objects', function ($q) use($objectIds){
                $q->whereIn('id', $objectIds);
                $q->where('is_visibly', true);
            });
        }
        $countries = $this->prepareGeoQuery($countries, $locale, 'country', $searchKey, $withDescription);
        $countries->whereNotNull('alias');
        $countries = $countries->take($rowsPerPage)->get();
        $itemsCountry = [];
        foreach ($countries as  $country){
            $country->type = 'country';
            $itemsCountry[] = $country;
        }

        return array_merge($itemsCity, $itemsRegion, $itemsCountry);
    }

    /**
     * Подготовка запроса
     *
     * @param $qb Запрос
     * @param string $locale
     * @param string $for
     * @param bool $withDescription
     * @param null|string $searchKey
     * @return mixed
     * @throws UnsupportLocaleException
     */
    public function prepareGeoQuery($qb, string $locale, string $for, ?string $searchKey = null, ?bool $withDescription = false)
    {
        $qb->when($searchKey, function ($query, $searchKey) use($locale) {
            if ( !is_null($searchKey) ){
                switch($locale){
                    case 'ru':
                        $query = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                        break;
                    case 'en' :
                        $query = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");
                        break;

                    default : throw new UnsupportLocaleException();
                }
                return $query;
            }
        });
        switch($locale){
            case 'ru':
                switch ($for){
                    case 'city' :
                        if ($withDescription)
                            $qb->select(['id', 'region_id', 'country_id', 'name_ru as name', 'alias',
                                'description_ru as description', 'crop_image as image']);
                        else
                            $qb->select(['id', 'region_id', 'country_id', 'name_ru as name', 'alias']);
                        $qb->with('region:id,name_ru as name');
                        $qb->with('country:id,name_ru as name');
                        $qb->with('seo:city_id,order,title_ru as title');
                        break;

                    case 'region' :
                        if ($withDescription)
                            $qb->select(['id', 'country_id', 'name_ru as name', 'alias',
                                'description_ru as description', 'crop_image as image']);
                        else
                            $qb->select(['id', 'country_id', 'name_ru as name', 'alias']);
                        $qb->with('country:id,name_ru as name');
                        $qb->with('seo:region_id,order,title_ru as title');
                        break;

                    case 'country' :
                        if ($withDescription)
                            $qb->select(['id', 'name_ru as name', 'alias', 'description_ru as description',
                                'crop_image as image']);
                        else
                            $qb->select(['id', 'name_ru as name', 'alias']);
                        $qb->with('seo:country_id,order,title_ru as title');
                        break;
                }
                break;

            case 'en' :
                switch ($for){
                    case 'city' :
                        if ($withDescription)
                            $qb->select(['id', 'region_id', 'country_id', 'name_en as name', 'alias',
                                'description_en as description', 'crop_image_en as image']);
                        else
                            $qb->select(['id', 'region_id', 'country_id', 'name_en as name', 'alias']);
                        $qb->with('region:id,name_en as name');
                        $qb->with('country:id,name_en as name');
                        $qb->with('seo:city_id,order,title_en as title');
                        break;

                    case 'region' :
                        if ($withDescription)
                            $qb->select(['id', 'country_id', 'name_en as name', 'alias',
                                'description_en as description', 'crop_image_en as image']);
                        else
                            $qb->select(['id', 'country_id', 'name_en as name', 'alias']);
                        $qb->with('country:id,name_en as name');
                        $qb->with('seo:region_id,order,title_en as title');
                        break;

                    case 'country' :
                        if ($withDescription)
                            $qb->select(['id', 'name_en as name', 'alias', 'description_ru as description',
                                'crop_image_en as image']);
                        else
                            $qb->select(['id', 'name_en as name', 'alias']);
                        $qb->select(['id', 'name_en as name', 'alias']);
                        $qb->with('seo:country_id,order,title_en as title');
                        break;
                }
                break;

            default : throw new UnsupportLocaleException();
        }

        return $qb;
    }

    /**
     * Получение списка избранных объектов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param string $locale
     * @param int $userId
     * @return array
     * @throws UnsupportLocaleException
     */
    public function getFavorites(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting,
                                 string $locale, int $userId)
    {
        $favorites = FavoriteObject::where('user_id', $userId)->get();
        $filterParams['ids'] = [];
        foreach ($favorites as $favorite){
            $filterParams['ids'][] = $favorite->object_id;
        }

        return $this->extendedObjectSearch($page, $rowsPerPage, $searchKey,  $sorting, $locale, $filterParams,
            null, null);
    }

    /**
     * Добавление в избранное объекта
     *
     * @param int $userId
     * @param int $objectId
     */
    public function addFavorite(int $userId, int $objectId)
    {
        $favorite = FavoriteObject::where('user_id', $userId)->where('object_id', $objectId)
            ->first();
        !is_null($favorite) ? : $favorite = new FavoriteObject;
        $favorite->user_id = $userId;
        $favorite->object_id = $objectId;
        $favorite->save();
    }

    /**
     * Удаление из избранного объекта
     *
     * @param int $userId
     * @param int $objectId
     * @throws ApiProblemException
     */
    public function deleteFavorite(int $userId, int $objectId)
    {
        $favorite = FavoriteObject::where('user_id', $userId)->where('object_id', $objectId)
            ->first();
        if(is_null($favorite))
            throw new ApiProblemException('Нет в избранном', 404);

        $favorite->delete();
    }

    /**
     * Добавление в избранное географии
     *
     * @param int $userId
     * @param int|null $countryId
     * @param int|null $regionId
     * @param int|null $cityId
     * @throws ApiProblemException
     */
    public function addFavoriteGeo(int $userId, ?int $countryId = null, ?int $regionId = null, ?int $cityId = null)
    {
        $favoriteGeo = FavoriteGeography::where('user_id', $userId);

        if (!is_null($countryId))
            $favoriteGeo = $favoriteGeo->where('country_id', $countryId)->first();

        elseif ( !is_null($regionId) )
            $favoriteGeo = $favoriteGeo->where('region_id', $regionId)->first();

        elseif ( !is_null($cityId) )
            $favoriteGeo = $favoriteGeo->where('city_id', $cityId)->first();
        else
            throw new ApiProblemException('Не отправлен ID страны, региона или города', 422);
        !is_null($favoriteGeo) ? : $favoriteGeo = new FavoriteGeography;

        $favoriteGeo->user_id = $userId;
        $favoriteGeo->country_id = $countryId;
        $favoriteGeo->region_id = $regionId;
        $favoriteGeo->city_id = $cityId;
        $favoriteGeo->save();
    }

    /**
     * Удаление из избранного географии
     *
     * @param int $userId
     * @param int|null $countryId
     * @param int|null $regionId
     * @param int|null $cityId
     * @throws ApiProblemException
     */
    public function deleteFavoriteGeo(int $userId, ?int $countryId = null, ?int $regionId = null, ?int $cityId = null)
    {
        $favoriteGeo = FavoriteGeography::where('user_id', $userId);

        if (!is_null($countryId))
            $favoriteGeo->where('country_id', $countryId)->delete();

        elseif ( !is_null($regionId) )
            $favoriteGeo->where('region_id', $regionId)->delete();

        elseif ( !is_null($cityId) )
            $favoriteGeo->where('city_id', $cityId)->delete();
        else
            throw new ApiProblemException('Не отправлен ID страны, региона или города', 422);
    }

    /**
     * Получение ибранной географии
     *
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param string $locale
     * @param int $userId
     * @return array
     * @throws UnsupportLocaleException
     */
    public function getFavoritesGeo(int $page, int $rowsPerPage, ?string $searchKey, string $locale, int $userId)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $favoriteGeos = FavoriteGeography::where('user_id', $userId)->get();
        $countryIds = [];
        $regionIds = [];
        $cityIds = [];
        foreach ($favoriteGeos as $favoriteGeo) {
            is_null($favoriteGeo->country_id) ? : $countryIds[] = $favoriteGeo->country_id;
            is_null($favoriteGeo->region_id) ? : $regionIds[] = $favoriteGeo->region_id;
            is_null($favoriteGeo->city_id) ? : $cityIds[] = $favoriteGeo->city_id;
        }

        $items = $this->geographySearch(9999999, $searchKey, $locale, $countryIds, $regionIds, $cityIds);
        $items = collect($items);
        $total = $items->count();
        $items = $items->splice($skip, $rowsPerPage);

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Поиск по URL
     *
     * @param null|string $url
     * @param int $rowsPerPage
     * @param string $locale
     * @param array|null $ids
     * @param float|null $lat
     * @param float|null $lon
     * @param int $page
     * @param array|null $sorting
     * @param bool|null $onMainPage
     * @return array
     * @throws ApiProblemException
     * @throws UnsupportLocaleException
     */
    public function objectSearchByUrl(?string $url, int $rowsPerPage, string $locale,
                                      ?array $ids =null, ?float $lat = null, ?float $lon = null, int $page = 1,
                                      ?array $sorting = null, ?bool $onMainPage = null)
    {
        $order = [ //Порядок блоков в URL
            'discount',
            'aliases',
            'beside',
            'stars',
            'moods',
        ];

        $aliasesBlock = 0;
        $aliasesPosition = 0;
        $aliases = [];

        $besideBlock = 0;
        $besidegPosition = 0;
        $beside = null;

        $discountBlock = 0;
        $discountPosition = 0;
        $discount = null;

        $starsBlock = 0;
        $starsPosition = 0;
        $currentStar = 0;
        $stars = null;

        $moodsBlock = 0;
        $moodsPosition = 0;
        $moods = [];
        $moodPicked = false;

        $position = 0;

        if (substr($url, '-1', 1) == '/'){
            $url = substr($url, 0, strlen($url) - 1);
        }
        $urlItems = explode('/', $url);
        foreach ($urlItems as $item){
            $position++;

            if (substr($item, 0, 6) == 'stars-'){
                $starsBlock++;
                $starsPosition = $position;

                if (!is_numeric( substr($item, 6) ) )
                    throw new ApiProblemException('Ошибка в параметре stars. Должно быть целое число', 404);

                if ($currentStar >= intval(substr($item, 6)))
                    throw new ApiProblemException('Звездность должна возрастать', 404);
                $currentStar = intval(substr($item, 6));
                if ($currentStar > 5)
                    throw new ApiProblemException('Звезность должна быть меньше 5', 404);
                $stars[] = intval(substr($item, 6));
            }elseif (substr($item, 0, 5) == 'mood-'){
                $moodPicked = true;
                $moods[] = substr($item, 5);
                $moodsBlock++;
                $moodsPosition = $position;
            }
            elseif ($item == 'discount'){
                $discountBlock++;
                $discountPosition = $position;
                $discount = true;
            }
            elseif (substr($item, 0, 7) == 'beside-'){
                $besideBlock++;
                $besidePosition = $position;
                $beside = substr($item, 7);
            }
            else {
                $aliasesBlock++;
                $aliasesPosition = $position;
                if ($item != '')
                    $aliases[] = $item;
            }

            /**
             *  Проверяем правильность порядка следования блоков фильрации ********************************************
             */
            foreach ($order as $positionCurrent => $blockName){
                $blockCount =   $blockName . 'Block';
                $blockPosition= $blockName . 'Position';

                foreach ($order as $checkPosition => $checkBlockName){
                    $checkBlockCount =   $checkBlockName . 'Block';
                    $checkBlockPosition= $checkBlockName . 'Position';
                    if ($$blockCount > 0 && $$checkBlockCount > 0){
                        if ($positionCurrent > $checkPosition ) {
                            if ($$blockPosition < $$checkBlockPosition) {
                                throw new ApiProblemException("Блок {$blockName} не на своём месте", 404);
                            }
                        }
                    } else{
                        continue;
                    }
                }
            }
            /**
             * ********************************************************************************************************
             */
        }

        /**
         * Проверяем что все переданные алиасы существуют и проверяем их порядок ***************************************
         */
        if (count($aliases) > 0){
            $seoData = SeoInformation::whereIn('url', $aliases)->whereNotNull('order')->orderBy('order', 'asc')->get();
            if (count($aliases) != count($seoData))
                throw new ApiProblemException('Один или несколько алисов не существуют', 404);
            foreach ($seoData as $key=>$seo){
                if ($seo->url != $aliases[$key])
                    throw new ApiProblemException('Не верный порядок алиасов', 404);
            }
        }

        $filter = [];

        /**
         * Заполняем данные для фильтра по критерию поиск "Рядом" ******************************************************
         */
        if (!is_null($beside)){
            $seoBeside = SeoInformation::where('url', $beside)->first();
            if (is_null($seoBeside))
                throw new ApiProblemException('Не существующий алиас для параметра beside', 404);

            if (!is_null($seoBeside->country_id)){
                $item = Country::where('id', $seoBeside->country_id);
                switch ($locale){
                    case 'ru' :
                        $item = $item->select(['id', 'name_ru as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    case 'en' :
                        $item = $item->select(['id', 'name_en as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    default : throw new UnsupportLocaleException();
                }
                $item->type = 'country';
                $beside = $item;
            }
            elseif (!is_null($seoBeside->region_id)){
                $item = Region::where('id', $seoBeside->region_id);
                switch ($locale){
                    case 'ru' :
                        $item = $item->select(['id', 'name_ru as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    case 'en' :
                        $item = $item->select(['id', 'name_en as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    default : throw new UnsupportLocaleException();
                }
                $item->type = 'region';
                $beside = $item;
            }
            elseif (!is_null($seoBeside->city_id)){
                $item = City::where('id', $seoBeside->city_id);
                switch ($locale){
                    case 'ru' :
                        $item = $item->select(['id', 'name_ru as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    case 'en' :
                        $item = $item->select(['id', 'name_en as name', 'alias', 'latitude', 'longitude'])->first();
                        break;
                    default : throw new UnsupportLocaleException();
                }
                $item->type = 'city';
                $beside = $item;
            }
            elseif (!is_null($seoBeside->object_id)){
                $item = ObjectPlace::where('id', $seoBeside->object_id);
                switch ($locale){
                    case 'ru' :
                        $item = $item->select(['id', 'title_ru as name', 'alias', 'lat as latitude', 'lon as longitude'])->first();
                        break;
                    case 'en' :
                        $item = $item->select(['id', 'title_en as name', 'alias', 'lat as latitude', 'lon as longitude'])->first();
                        break;
                    default : throw new UnsupportLocaleException();
                }
                $item->type = 'object';
                $beside = $item;
            } else {
                throw new ApiProblemException("Поиск по критерию рядом не предусмотрен для типа: {$seoBeside->for}");
            }
            $key = 'beside_' . $beside->type . '_id';
            $filter[$key] = $beside->id;
        }

        /**
         * Заполняем критерии фильтра для клиента ***********************************************************************
         */
        $therapies = $this->therapySearch(1, 99999999, null, null, $locale, false, $aliases);
        $medicalProfiles = $this->medicalProfileSearch(1, 9999999, null, $locale, false, $aliases);
        $diseases = $this->diseaseSearch(1, 9999999, null, $locale, false, $aliases);
        $services = $this->serviceSearch(1, 9999999, null, $locale, $aliases);
        $country = $this->countrySearch(1, 9999999, null, $locale, false, null, $aliases);
        $region = $this->regionSearch(1, 9999999, null, $locale, false, null, $aliases);
        $city = $this->citySearch(1, 9999999, null, $locale, false, null, $aliases);

        /**
         * Заполняем фильтрацию для выборки ****************************************************************************
         */
        if ($therapies['total'] > 0){
            foreach ($therapies['items'] as  $item){
                $filter['therapies'][] = $item->id;
            }
        } else {
            $filter['therapies'] = null;
        }

        if ($medicalProfiles['total'] > 0){
            foreach ($medicalProfiles['items'] as  $item){
                $filter['medical_profiles'][] = $item->id;
            }
        } else {
            $filter['medical_profiles'] = null;
        }
        if ($diseases['total'] > 0){
            foreach ($diseases['items'] as  $item){
                $filter['diseases'][] = $item->id;
            }
        } else {
            $filter['diseases'] = null;
        }

        if ($services['total'] > 0){
            foreach ($services['items'] as  $item){
                $filter['services'][] = $item->id;
            }
        }  else {
            $filter['services'] = null;
        }
        if ($country['total'] > 0){
            $filter['country_aliases'] = $aliases;
        } else {
            $filter['country_aliases'] = null;
        }
        if ($region['total'] > 0){
            $filter['region_aliases'] = $aliases;
        } else {
            $filter['region_aliases'] = null;
        }
        if ($city['total'] > 0){
            $filter['city_aliases'] = $aliases;
        } else {
            $filter['city_aliases'] = null;
        }
        $filter['stars'] = $stars ?? null;
        $filter['moods'] = $moods ? $moods : [];
        $filter['on_main_page'] = $onMainPage ?? null;
        $filter['in_action'] = $discount ?? null;

        if ($moods) {
            $moodItems = Mood::whereIn('alias', $moods);

            switch ($locale){
                case 'ru' :
                    $moodItems = $moodItems->select(['id', 'name_ru as name', 'alias', 'image'])->get();
                    break;
                case 'en' :
                    $moodItems = $moodItems->select(['id', 'name_en as name', 'alias', 'image'])->get();
                    break;
                default : throw new UnsupportLocaleException();
            }
            $moodItems = $moodItems->toArray();
        }

        $filterResponse = [
            'therapies' => $therapies['items'],
            'medical_profiles' => $medicalProfiles['items'],
            'diseases' => $diseases['items'],
            'services' => $services['items'],
            'country' => $country['items'][0] ?? null,
            'region' => $region['items'][0] ?? null,
            'city' => $city['items'][0] ?? null,
            'discount' => $discount,
            'beside' => $beside,
            'stars' => $stars,
            'sorting' => $sorting,
            'page' => $page,
            'latitude' => $beside->latitude ?? $lat,
            'longitude' => $beside->longitude ?? $lon,
            'block_order' => $order,
            'moods' => $moodItems ?? null,
        ];

        $geography = null;
        foreach($city['items'] as $item){
            $geography[] = $item;
        }
        foreach($region['items'] as $item){
            $geography[] = $item;
        }
        foreach($country['items'] as $item){
            $geography[] = $item;
        }
        $filterResponse['multiple_geography'] = $geography;

        /**
         * Получение объектов ******************************************************************************************
         */
        $objects = $this->extendedObjectSearch($page, $rowsPerPage, null, $sorting, $locale, $filter,
            $filterResponse['latitude'], $filterResponse['longitude']);

        $filterWithoutMoods = $filter;
        $filterWithoutMoods['moods'] = [];

        $objects4moods = $this->extendedObjectSearch(1, 999, null, $sorting, $locale, $filterWithoutMoods,
            $filterResponse['latitude'], $filterResponse['longitude']);

        $responseData = $objects['response_data'];
        unset($objects['response_data']);

        if (!is_null($filterResponse['stars'])) { // Если в результатах нет выбраных звезд, то убираем из filter_response
            $changedStars = $filterResponse['stars'];
            $filterResponse['stars'] = [];
            foreach ($changedStars as $changedStar){
                if (in_array($changedStar, $responseData['stars']))
                    $filterResponse['stars'][] = $changedStar;
            }
        }


        $filterDataMood = [];
        foreach ($objects4moods["items"] as $object) {

            if (isset($object["moods"]) && $object["moods"]) {
                foreach ($object["moods"] as $one) {
                    $filterDataMood[] = $one['id'];
                }
            }

        }

        $filterDataMood = array_unique($filterDataMood);

        if ($moodPicked && isset($moodItems[0])) {
            $pickedMoodId = $moodItems[0]['id'];
            if (!in_array($pickedMoodId, $filterDataMood)) {
                $filterDataMood[] = $pickedMoodId;
            }
        }

        if($filter["therapies"] === null
            && $filter["medical_profiles"] === null
            && $filter["diseases"] === null
            && $filter["services"] === null
            && $filter["country_aliases"] === null
            && $filter["region_aliases"] === null
            && $filter["city_aliases"] === null
            && $filter["stars"] === null
            && $filter["on_main_page"] === null
            && $filter["in_action"] === null) {

                $filterDataMood = null;
        }

        $filterData = [
            'medical_profiles' =>  null,
            'services' =>  null,
            'stars' =>  $responseData['stars'],
            'in_action' =>  $responseData['in_action'],
            'moods' => $filterDataMood,
        ];
        if ($city['total'] > 0)
            $filterData['multiple_geography'] = $this->getMultipleGeography($city['items'][0]->id, $locale, 'city');

        elseif ($region['total'] > 0)
            $filterData['multiple_geography'] = $this->getMultipleGeography($region['items'][0]->id, $locale, 'region');

        elseif ($country['total'] > 0)
            $filterData['multiple_geography'] = $this->getMultipleGeography($country['items'][0]->id, $locale, 'country');

        else
            $filterData['cities_of_region'] = null;

        $medicalProfiles = $this
            ->medicalProfileSearch(1, 9999999, null, $locale, false, null, null, $responseData['objectIds'])['items'];
        foreach ($medicalProfiles as $item) {
            $filterData['medical_profiles'][] = $item->id;
        }
        $services = $this
            ->serviceSearch(1, 9999999, null, $locale,  null, $responseData['objectIds'])['items'];
        foreach ($services as $item) {
            $filterData['services'][] = $item->id;
        }
        $filterData['objectIds'] = $responseData['objectIds'];

        $seoFilterUrl = SeoFilterUrl::where('url', $url)->first();
        if (!is_null($seoFilterUrl)){
            $objects['templates'] = null;
            $objects['custom_seo'] = $seoFilterUrl->getCustomMetaData($locale);
        } else {
            $objects['templates'] = (object)$this->getSeoTemplate($filterResponse, $locale);
            $objects['custom_seo'] = null;
        }

        $objects['filter_response'] = (object)$filterResponse;
        $objects['filter_data'] = (object)$filterData;


        return $objects;
    }

    /**
     * Получение шаблонов для страницы фильтрайии объектов санаториев
     *
     * @param array $filterResponse
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getSeoTemplate(array $filterResponse, string $locale)
    {
        $seoTemplates = SeoTemplate::whereNotNull('for');
        switch ($locale){
            case 'ru' :
                $seoTemplates->select([
                    'for',
                    'title_ru as title',
                    'meta_description_ru as meta_description',
                    'text_ru as text',
                ]);
                break;

            case 'en' :
                $seoTemplates->select([
                    'for',
                    'title_en as title',
                    'meta_description_en as meta_description',
                    'text_en as text',
                ]);
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }
        $seoTemplates = $seoTemplates->get();

        $templates = [];
        foreach ($filterResponse as $filter => $value){
            if ($filter == 'discount') {
                if ($value)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'stars'){
                if ($value)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'beside'){
                if ($value)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'country'){
                if ( !is_null($value) )
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'region'){
                if ( !is_null($value) )
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'city'){
                if ( !is_null($value) )
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'therapies'){
                if ( count($value) < 2 && count($value) > 0)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'medical_profiles'){
                if ( count($value) < 2 && count($value) > 0)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'diseases'){
                if ( count($value) < 2 && count($value) > 0)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
            elseif ($filter == 'services'){
                if ( count($value) < 2 && count($value) > 0)
                    $templates[$filter] = $seoTemplates->where('for', '=', $filter)->first();
            }
        }
        foreach ($templates as $key => $template){
            if (is_null($template))
                unset($templates[$key]);
        }

        return $templates;
    }

    /**
     * Получение списка географии
     *
     * @param int $geographyId
     * @param string $locale
     * @param string $type
     * @return mixed
     * @throws ApiProblemException
     * @throws UnsupportLocaleException
     */
    public function getMultipleGeography(int $geographyId, string $locale, string $type)
    {
        switch ($type){
            case 'city' :
                $city = City::find($geographyId);
                $items = $city->region->cities;
                $aliases = [];
                foreach ($items as $item){
                    $aliases[] = $item->alias;
                }
                $geography = $this->citySearch(1, 9999999, null, $locale, false, null, $aliases, true )['items'];
                break;

            case 'region' :
                $region = Region::find($geographyId);
                $items = $region->country->regions;
                $aliases = [];
                foreach ($items as $item){
                    $aliases[] = $item->alias;
                }
                $geography = $this->regionSearch(1, 9999999, null, $locale, false, null, $aliases, true )['items'];
                break;

            case 'country' :
                $geography = $this->countrySearch(1, 9999999, null, $locale, false, null,  null, true)['items'];
                break;

            default :
                throw new ApiProblemException('Не поддерживаемый тип географии', 500);
        }

        return $geography;
    }
}
