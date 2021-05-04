<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class GeoService
{
    use ImageTrait;
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * GeoService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Получение стран
     *
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param null|string $searchKey
     * @param null|string $locale
     * @param null|array $sorting
     * @param null|array $params
     * @param null|bool $isVisible Скрывать неактивные страны
     * @return array
     * @throws ApiProblemException
     */
    public function getCountries(?int $page, ?int $rowsPerPage, ?string $searchKey, ?array $sorting = null,
                                 ?string $locale = null, ?bool $isVisible = false, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);
        $filter = [];
        if ($isVisible) $filter[] = ['is_visible', true];

        $qb = Country::where($filter);

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
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id', 'name_ru as name', 'latitude', 'longitude', 'crop_image',
                        'alias', 'telephone_code', 'country_code']);
                    $qb->withCount('objects');

                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id', 'name_en as name', 'latitude', 'longitude', 'crop_image_en as crop_image',
                        'alias', 'telephone_code', 'country_code']);
                    $qb->withCount('objects');
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }

        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            })->select(['id', 'name_ru', 'name_en', 'latitude', 'longitude', 'crop_image',
                'alias', 'is_visible', 'latitude', 'longitude']);
            $qb->withCount('objects');
        }

        if (!empty($params['has_object'])){
            $qb->whereRaw("id in(
            SELECT country_id FROM objects WHERE is_visibly = true
            )");
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение регионов
     *
     * @param int $countryId
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param null|string $searchKey
     * @param null|string $locale
     * @param null|array $sorting
     * @param null|bool $isVisible
     * @param null|array $params
     * @return array
     * @throws
     */
    public function getRegions(?int $countryId, ?int $page, ?int $rowsPerPage, ?string $searchKey, ?array $sorting = null,
                               string $locale = null, ?bool $isVisible = false, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);
        $filter = [];
        if (!is_null($countryId)) $filter[] = ['country_id', $countryId];
        if ($isVisible) $filter[] = ['is_visible', true];

        $qb = Region::where($filter);

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
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id',  'country_id', 'name_ru as name', 'latitude', 'longitude', 'crop_image', 'alias']);
                    $qb->with('country:id,name_ru as name,telephone_code,country_code');
                    $qb->withCount('objects');
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id', 'country_id', 'name_en as name', 'latitude', 'longitude', 'crop_image_en as crop_image',
                        'alias', 'is_visible']);
                    $qb->with('country:id,name_en as name,telephone_code,country_code');
                    $qb->withCount('objects');
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            })->select(['id', 'country_id', 'name_ru', 'name_en', 'latitude', 'longitude', 'crop_image', 'alias', 'is_visible']);
            $qb->with('country:id,name_ru');
            $qb->withCount('objects');
        }

        if (!empty($params['has_object'])){
            $qb->whereRaw("id in(
            SELECT region_id FROM objects WHERE is_visibly = true
            )");
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение городов
     *
     * @param int $countryId
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param null|string $searchKey
     * @param int|null $regionId
     * @param string|null $locale
     * @param null|array $sorting
     * @param null|bool $isVisible
     * @param null|array $params
     * @return array
     * @throws ApiProblemException
     */
    public function getCities(?int $countryId, ?int $page, ?int $rowsPerPage, ?string $searchKey,
                              ?int $regionId = null, ?array $sorting = null, string $locale = null,
                              ?bool $isVisible = false, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);
        $filter = [];
        if (!is_null($countryId)) $filter[] = ['country_id', $countryId];
        if (!is_null($regionId)) $filter[] = ['region_id', $regionId];
        if ($isVisible) $filter[] = ['is_visible', true];

        $qb = City::where($filter);

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
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id', 'region_id', 'country_id', 'name_ru as name', 'latitude', 'longitude', 'crop_image', 'alias']);
                    $qb->with('region:id,name_ru as name');
                    $qb->with('country:id,name_ru as name,telephone_code,country_code');
                    $qb->withCount('objects');
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $searchCond = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");
                            return $searchCond;
                        }
                    })->select(['id', 'region_id', 'country_id', 'name_en as name', 'latitude', 'longitude', 'crop_image_en as crop_image', 'alias']);
                    $qb->with('region:id,name_en as name');
                    $qb->with('country:id,name_en as name,telephone_code');
                    $qb->withCount('objects');
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }

        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            })->select(['id', 'country_id', 'region_id', 'name_ru', 'name_en', 'latitude', 'longitude', 'crop_image', 'alias', 'is_visible', 'profiles_block_title_ru', 'profiles_block_title_en'])
                ->with('region:id,name_ru', 'country:id,name_ru');
            $qb->withCount('objects');
        }

        if (!empty($params['has_object'])){
            $qb->whereRaw("id in(
            SELECT city_id FROM objects WHERE is_visibly = true
            )");
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение крупных городов
     *
     * @param int $countryId
     * @param int $rowsPerPage
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getTopCity(int $countryId, int $rowsPerPage, string $locale)
    {
        $filter[] = ['country_id', $countryId];

        $qb = City::where($filter);
        switch ($locale){
            case 'ru' :
                $qb->select(['id', 'region_id', 'country_id', 'name_ru as name', 'latitude', 'longitude']);
                $qb->with(
                    'region:id,name_ru as name',
                    'country:id,telephone_code'
                    );
                $qb->withCount('objects');
                break;

            case 'en' :
                $qb->select(['id', 'region_id', 'country_id', 'name_en as name', 'latitude', 'longitude']);
                $qb->with(
                    'region:id,name_en as name',
                    'country:id,telephone_code'
                );
                $qb->withCount('objects');
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }
        $cities = $qb->orderBy('population', 'desc')->take($rowsPerPage)->get();

        return $cities;
    }

    /**
     * Создание страны
     *
     * @param Request $request
     * @return Country
     */
    public function createCountry(Request $request)
    {
        $newCountry = new Country();

        $newCountry->name_ru = $request->get('name_ru');
        $newCountry->name_en = $request->get('name_en') ?? null;
        $newCountry->slug = $request->get('slug') ?? null;
        $newCountry->is_visible = $request->get('is_visible') ?? false;
        $newCountry->description_ru = $request->get('description_ru') ?? null;
        $newCountry->description_en = $request->get('description_en') ?? null;
        $newCountry->recomm = $request->get('recomm') ?? null;
        $newCountry->profiles_block_title_ru = $request->get('profiles_block_title_ru') ?? "";
        $newCountry->profiles_block_title_en = $request->get('profiles_block_title_en') ?? "";
        $newCountry->regions_block_title_ru = $request->get('regions_block_title_ru') ?? "";
        $newCountry->regions_block_title_en = $request->get('regions_block_title_en') ?? "";

        if ($request->hasFile('image'))
        {
            $path = $request->file('image')->store('regions');
            $pathCrop = $request->file('image')->store('regions_crop');

            $newCountry->image = Storage::url($path);
            $newCountry->crop_image = Storage::url($pathCrop);
        }
        $newCountry->save();

        return $newCountry;
    }

    /**
     * Обновление страны
     *
     * @param Request $request
     * @param int $countryId
     * @return bool
     * @throws ApiProblemException
     */
    public function updateCountry(Request $request, int $countryId)
    {
        $country = Country::find($countryId);
        if (is_null($country)) throw new ApiProblemException('Страна не найдена', 404);

        if ($request->hasFile('image'))
        {
            Storage::delete('regions/' . basename($country->image));

            $path = $request->file('image')->store('regions');
            $country->image = Storage::url($path);
            $this->optimizeImage($country->image, 'regions');
        }
        if ($request->hasFile('crop_image')){
            Storage::delete('regions_crop/' . basename($country->crop_image));
            $path = $request->file('crop_image')->store('regions_crop');
            $country->crop_image = Storage::url($path);

        } else {
            if (isset($path) ){
                Storage::delete('regions_crop/' . basename($country->crop_image));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($path);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($path);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $country->crop_image = Storage::url('regions_crop/' . basename($path));
            }
        }

        if ($request->hasFile('image_en'))
        {
            Storage::delete('regions/' . basename($country->image_en));

            $pathEn = $request->file('image_en')->store('regions');
            $country->image_en = Storage::url($pathEn);
            $this->optimizeImage($country->image_en, 'regions');
        }
        if ($request->hasFile('crop_image_en')){
            Storage::delete('regions_crop/' . basename($country->crop_image_en));
            $pathEn = $request->file('crop_image_en')->store('regions_crop');
            $country->crop_image_en = Storage::url($pathEn);

        } else {
            if (isset($pathEn)){
                Storage::delete('regions_crop/' . basename($country->crop_image_en));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($pathEn);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($pathEn);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $country->crop_image_en = Storage::url('regions_crop/' . basename($pathEn));
            }
        }

        if ($request->hasFile('image_en'))
        {
            Storage::delete('regions/' . basename($country->image_en));

            $path = $request->file('image_en')->store('regions');
            $country->image_en = Storage::url($path);
        }
        if ($request->hasFile('crop_image_en')){
            Storage::delete('regions_crop/' . basename($country->crop_image_en));
            $path = $request->file('crop_image_en')->store('regions_crop');
            $country->crop_image_en = Storage::url($path);

        } else {
            if (isset($path)  && is_null($country->crop_image_en)){
                Storage::delete('regions_crop/' . basename($country->crop_image_en));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($path);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($path);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $country->crop_image_en = Storage::url('regions_crop/' . basename($path));
            }
        }

        $data = $request->only('is_visible', 'description_ru',
            'description_en', 'recomm_ru', 'recomm_en', 'name_ru', 'name_en', 'tags_ru', 'tags_en',
            'profiles_block_title_ru', 'profiles_block_title_en', 'regions_block_title_ru', 'regions_block_title_en', 'latitude', 'longitude');

        foreach ($data as $field=>$value){
            $country->$field = $value;
        }
        $country->save();

        return $country;
    }

    /**
     * Получение страны
     *
     * @param int|null $countryId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getCountry(?int $countryId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            if (is_null($alias)) throw new ApiProblemException('Алиас не отправлен', 422);
            $country = Country::where('alias', $alias)->where('is_visible', true);
            switch ($locale){
                case 'ru' :
                    $country->select([
                        'id', 'name_ru as name', 'description_ru as description',
                        'recomm_ru as recomm', 'latitude', 'longitude', 'image', 'telephone_code', 'country_code',
                        'profiles_block_title_ru as profiles_block_title', 'regions_block_title_ru as regions_block_title'
                    ]);
                    $country->with(
                        'seo:id,country_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords'
                    );
                    break;

                case 'en' :
                    $country->select([
                        'id', 'name_en as name', 'description_en as description',
                        'recomm_en as recomm', 'latitude', 'longitude', 'image_en as image', 'telephone_code', 'country_code',
                        'profiles_block_title_en as profiles_block_title', 'regions_block_title_en as regions_block_title'
                    ]);
                    $country->with(
                        'seo:id,country_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $country = $this->getCountryLocaleFilter($country, $locale);
        } else {
            $country = Country::where('id', $countryId);
            $country->with('seo');
        }
        $country = $country->first();
        if (is_null($country)) throw new ApiProblemException('Страна не найдена', 404);
        $country->isFavorite();

        $country['recommendations'] = RecommendationService::getRecommendationsShort(
            RecommendationService::LOCATION_TYPE_COUNTRY, $country->id, $locale
        );

        return $country;
    }

    /**
     * Удаление страны
     *
     * @param int $countryId
     * @return bool
     */
    public function deleteCountry(int $countryId)
    {
        $country = Country::find($countryId);

        if (!is_null($country)){

            Storage::delete('regions/' . basename($country->image));
            Storage::delete('regions_crop/' . basename($country->crop_image));
            $country->delete();

            return true;
        } else return false;
    }


    /**
     * Создание региона
     *
     * @param Request $request
     * @return Region
     */
    public function createRegion(Request $request)
    {
        $newRegion = new Region();

        $newRegion->country_id = $request->get('country_id');
        $newRegion->name_ru = $request->get('name_ru');
        $newRegion->name_en = $request->get('name_en');
        $newRegion->is_visible = $request->get('is_visible') ?? false;
        $newRegion->description_ru = $request->get('description_ru') ?? "";
        $newRegion->description_en = $request->get('description_en') ?? "";
        $newRegion->tags_ru = $request->get('tags_ru') ?? [];
        $newRegion->tags_en = $request->get('tags_en') ?? [];
        $newRegion->profiles_block_title_ru = $request->get('profiles_block_title_ru') ?? "";
        $newRegion->profiles_block_title_en = $request->get('profiles_block_title_en') ?? "";
        $newRegion->cities_block_title_ru = $request->get('cities_block_title_ru') ?? "";
        $newRegion->cities_block_title_en = $request->get('cities_block_title_en') ?? "";
        $newRegion->latitude = $request->get('latitude') ?? null;
        $newRegion->longitude = $request->get('longitude') ?? null;



        if ($request->hasFile('image'))
        {
            $path = $request->file('image')->store('regions');
            $pathCrop = $request->file('image')->store('regions_crop');

            $newRegion->image = Storage::url($path);
            $newRegion->crop_image = Storage::url($pathCrop);
        }

        if ($request->hasFile('image_en'))
        {
            $path = $request->file('image_en')->store('regions');
            $pathCrop = $request->file('image_en')->store('regions_crop');

            $newRegion->image_en = Storage::url($path);
            $newRegion->crop_image_en = Storage::url($pathCrop);
        }

        $newRegion->save();

        return $newRegion;
    }

    /**
     * Обновление региона
     *
     * @param Request $request
     * @param int $regionId
     * @return bool
     * @throws ApiProblemException
     */
    public function updateRegion(Request $request, int $regionId)
    {
        $region = Region::find($regionId);
        if (is_null($region)) throw new ApiProblemException('Регион не найден', 404);

        if ($request->hasFile('image'))
        {
            Storage::delete('regions/' . basename($region->image));

            $path = $request->file('image')->store('regions');
            $region->image = Storage::url($path);
            $this->optimizeImage($region->image, 'regions');
        }
        if ($request->hasFile('crop_image')){
            Storage::delete('regions_crop/' . basename($region->crop_image));
            $path = $request->file('crop_image')->store('regions_crop');
            $region->crop_image = Storage::url($path);

        } else {
            if (isset($path)){
                Storage::delete('regions_crop/' . basename($region->crop_image));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($path);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($path);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $region->crop_image = Storage::url('regions_crop/' . basename($path));
            }
        }

        if ($request->hasFile('image_en'))
        {
            Storage::delete('regions/' . basename($region->image_en));

            $pathEn = $request->file('image_en')->store('regions');
            $region->image_en = Storage::url($pathEn);
            $this->optimizeImage($region->image_en, 'regions');
        }
        if ($request->hasFile('crop_image_en')){
            Storage::delete('regions_crop/' . basename($region->crop_image_en));
            $pathEn = $request->file('crop_image_en')->store('regions_crop');
            $region->crop_image_en = Storage::url($pathEn);

        } else {
            if (isset($pathEn)){
                Storage::delete('regions_crop/' . basename($region->crop_image_en));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($pathEn);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($pathEn);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $region->crop_image_en = Storage::url('regions_crop/' . basename($pathEn));
            }
        }

        $data = $request->only( 'is_visible', 'description_ru',
            'description_en', 'recomm_ru', 'recomm_en', 'name_ru', 'name_en', 'tags_ru', 'tags_en',
            'profiles_block_title_ru', 'cities_block_title_ru', 'profiles_block_title_en', 'cities_block_title_en', 'latitude', 'longitude');

        foreach ($data as $field=>$value){
            $region->$field = $value;
        }
        $region->save();

        return $region;
    }

    /**
     * Получение региона
     *
     * @param int|null $regionId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getRegion(?int $regionId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            if (is_null($alias)) throw new ApiProb5lemException('Алиас не отправлен', 422);
            $region = Region::where('alias', $alias)->where('is_visible', true);
            switch ($locale){
                case 'ru' :
                    $region->select([
                        'id', 'country_id', 'name_ru as name', 'description_ru as description',
                        'recomm_ru as recomm', 'latitude', 'longitude', 'image',
                        'profiles_block_title_ru as profiles_block_title', 'cities_block_title_ru as cities_block_title'
                    ]);
                    $region->with(
                        'seo:id,region_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'publicCountry:id,name_ru as name,alias,telephone_code,country_code'
                    );
                    break;

                case 'en' :
                    $region->select([
                        'id', 'country_id', 'name_en as name', 'description_en as description',
                        'recomm_en as recomm', 'latitude', 'longitude', 'image_en as image',
                        'profiles_block_title_en as profiles_block_title', 'cities_block_title_en as cities_block_title'
                    ]);
                    $region->with(
                        'seo:id,region_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'publicCountry:id,name_en as name,alias,telephone_code,country_code'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $region = $this->getRegionLocaleFilter($region, $locale);
        } else {
            $region = Region::where('id', $regionId);
            $region->with('seo', 'country:id,name_ru');
        }
        $region = $region->first();
        if (is_null($region)) throw new ApiProblemException('Регион не найден', 404);
        $region->isFavorite();

        $region['recommendations'] = RecommendationService::getRecommendationsShort(
            RecommendationService::LOCATION_TYPE_REGION, $region->id, $locale
        );

        return $region;
    }

    /**
     * Удаление региона
     *
     * @param int $regionId
     * @return bool
     */
    public function deleteRegion(int $regionId)
    {
        $region = Region::find($regionId);
        if (!is_null($region)){

            Storage::delete('regions/' . basename($region->image));
            Storage::delete('regions_crop/' . basename($region->crop_image));
            $region->delete();

            return true;
        } else return false;
    }

    /**
     * Создание города
     *
     * @param Request $request
     * @return City
     */
    public function createCity(Request $request)
    {
        $newCity = new City();

        $newCity->country_id = $request->get('country_id');
        $newCity->region_id = $request->get('region_id');
        $newCity->name_ru = $request->get('name_ru');
        $newCity->name_en = $request->get('name_en') ?? null;
        $newCity->is_visible = $request->get('is_visible') ?? false;
        $newCity->description_ru = $request->get('description_ru') ?? "";
        $newCity->description_en = $request->get('description_en') ?? "";
        $newCity->tags_ru = $request->get('tags_ru') ?? [];
        $newCity->tags_en = $request->get('tags_en') ?? [];
        $newCity->profiles_block_title_ru = $request->get('profiles_block_title_ru') ?? "";
        $newCity->profiles_block_title_en = $request->get('profiles_block_title_en') ?? "";
        $newCity->latitude = $request->get('latitude') ?? null;
        $newCity->longitude = $request->get('longitude') ?? null;


        if ($request->hasFile('image'))
        {
            $path = $request->file('image')->store('regions');
            $pathCrop = $request->file('image')->store('regions_crop');

            $newCity->image = Storage::url($path);
            $newCity->crop_image = Storage::url($pathCrop);
        }

        if ($request->hasFile('image_en'))
        {
            $path = $request->file('image_en')->store('regions');
            $pathCrop = $request->file('image_en')->store('regions_crop');

            $newCity->image_en = Storage::url($path);
            $newCity->crop_image_en = Storage::url($pathCrop);
        }

        $newCity->save();

        return $newCity;
    }

    /**
     * Обновление города
     *
     * @param Request $request
     * @param int $cityId
     * @return bool
     * @throws ApiProblemException
     */
    public function updateCity(Request $request, int $cityId)
    {
        $city = City::find($cityId);
        if (is_null($city)) throw new ApiProblemException('Город не найден', 404);

        if ($request->hasFile('image'))
        {
            Storage::delete('regions/' . basename($city->image));

            $path = $request->file('image')->store('regions');
            $city->image = Storage::url($path);
            $this->optimizeImage($city->image, 'regions');
        }
        if ($request->hasFile('crop_image')){
            Storage::delete('regions_crop/' . basename($city->crop_image));
            $path = $request->file('crop_image')->store('regions_crop');
            $city->crop_image = Storage::url($path);
        } else {
            if (isset($path)){
                Storage::delete('regions_crop/' . basename($city->crop_image));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($path);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($path);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $city->crop_image = Storage::url('regions_crop/' . basename($path));
            }
        }

        if ($request->hasFile('image_en'))
        {
            Storage::delete('regions/' . basename($city->image_en));

            $pathEn = $request->file('image_en')->store('regions');
            $city->image_en = Storage::url($pathEn);
            $this->optimizeImage($city->image_en, 'regions');
        }
        if ($request->hasFile('crop_image_en')){
            Storage::delete('regions_crop/' . basename($city->crop_image_en));
            $pathEn = $request->file('crop_image_en')->store('regions_crop');
            $city->crop_image_en = Storage::url($pathEn);

        } else {
            if (isset($pathEn)){
                Storage::delete('regions_crop/' . basename($city->crop_image_en));
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions') . DIRECTORY_SEPARATOR  . basename($pathEn);
                $imageSavePath = storage_path('app' . DIRECTORY_SEPARATOR . 'regions_crop') . DIRECTORY_SEPARATOR  . basename($pathEn);
                Image::make($imagePath)->resize(400, null, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($imageSavePath);
                $city->crop_image_en = Storage::url('regions_crop/' . basename($pathEn));
            }
        }

        $data = $request->only( 'slug', 'is_visible', 'description_ru',
            'description_en', 'recomm_ru', 'recomm_en' , 'country_id', 'name_ru', 'name_en', 'tags_ru', 'tags_en',
            'profiles_block_title_ru', 'profiles_block_title_en', 'latitude', 'longitude');

        foreach ($data as $field=>$value){
            $city->$field = $value;
        }
        $city->save();

        return $city;
    }

    /**
     * Получение города
     *
     * @param int|null $cityId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getCity(?int $cityId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            if (is_null($alias)) throw new ApiProblemException('Алиас не отправлен', 422);
            $city = City::where('alias', $alias)->where('is_visible', true);
            switch ($locale){
                case 'ru' :
                    $city->select([
                        'id', 'country_id', 'region_id', 'name_ru as name', 'description_ru as description',
                        'recomm_ru as recomm', 'latitude', 'longitude', 'image', 'profiles_block_title_ru as profiles_block_title'
                    ]);
                    $city->with(
                        'seo:id,city_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'publicRegion:id,name_ru as name,alias',
                        'publicCountry:id,name_ru as name,alias,telephone_code,country_code'
                    );
                    break;

                case 'en' :
                    $city->select([
                        'id', 'country_id', 'region_id', 'name_en as name', 'description_en as description',
                        'recomm_en as recomm', 'latitude', 'longitude', 'image_en as image', 'profiles_block_title_en as profiles_block_title'
                    ]);
                    $city->with(
                        'seo:id,city_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'publicRegion:id,name_en as name,alias',
                        'publicCountry:id,name_en as name,alias,telephone_code'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $city = $this->getCityLocaleFilter($city, $locale);
        } else {
            $city = City::where('id', $cityId);
            $city->with('seo', 'region:id,name_ru', 'country:id,name_ru');
        }

        $city = $city->first();

        if (is_null($city)) throw new ApiProblemException('Город не найден', 404);
        $city->isFavorite();

        $city['recommendations'] = RecommendationService::getRecommendationsShort(
            RecommendationService::LOCATION_TYPE_CITY, $city->id, $locale
        );

        return $city;
    }

    /**
     * Удаление города
     *
     * @param int $cityId
     * @return bool
     */
    public function deleteCity(int $cityId)
    {
        $city = City::find($cityId);
        if (!is_null($city)){

            Storage::delete('regions/' . basename($city->image));
            Storage::delete('regions_crop/' . basename($city->crop_image));
            $city->delete();

            return true;
        } else return false;
    }

    /**
     * Удаление изображений страны
     *
     * @param int $countryId
     * @param array $imageTypes
     * @throws ApiProblemException
     */
    public function deleteCountryImage(int $countryId, array $imageTypes)
    {
        $country = Country::find($countryId);
        if (is_null($country)) throw new ApiProblemException('Страна не найдена', 404);
        foreach ($imageTypes as $imageType){
            Storage::delete('regions/' . basename($country->$imageType));
            $country->$imageType = null;
        }
        $country->save();
    }

    /**
     * Удаление изображений региона
     *
     * @param int $regionId
     * @param array $imageTypes
     * @throws ApiProblemException
     */
    public function deleteRegionImage(int $regionId, array $imageTypes)
    {
        $region = Region::find($regionId);
        if (is_null($region)) throw new ApiProblemException('Регион не найден', 404);
        foreach ($imageTypes as $imageType){
            if (in_array($imageType, ['image', 'image_en',])){
                Storage::delete('regions/' . basename($region->$imageType));
            }
            if (in_array($imageType, ['crop_image', 'crop_image_en'])){
                Storage::delete('regions_crop/' . basename($region->$imageType));
            }
            $region->$imageType = null;
        }
        $region->save();
    }

    /**
     * Удаление изображений города
     *
     * @param int $regionId
     * @param array $imageTypes
     * @throws ApiProblemException
     */
    public function deleteCityImage(int $regionId, array $imageTypes)
    {
        $city = City::find($regionId);
        if (is_null($city)) throw new ApiProblemException('Город не найден', 404);
        foreach ($imageTypes as $imageType){
            if (in_array($imageType, ['image', 'image_en',])){
                Storage::delete('regions/' . basename($city->$imageType));
            }
            if (in_array($imageType, ['crop_image', 'crop_image_en'])){
                Storage::delete('regions/' . basename($city->$imageType));
            }

            $city->$imageType = null;
        }
        $city->save();
    }
}
