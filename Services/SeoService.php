<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Exceptions\NotFoundException;
use App\Exceptions\SeoForException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\About;
use App\Models\City;
use App\Models\Country;
use App\Models\Disease;
use App\Models\MedicalProfile;
use App\Models\News;
use App\Models\ObjectPlace;
use App\Models\Offer;
use App\Models\Partner;
use App\Models\PartnerType;
use App\Models\Publication;
use App\Models\PublicationType;
use App\Models\Region;
use App\Models\SeoFilterUrl;
use App\Models\SeoInformation;
use App\Models\SeoTemplate;
use App\Models\Service;
use App\Models\Therapy;

class SeoService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * SeoService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Получение правила валидации запросов
     *
     * @param null|string $for
     * @return array
     * @throws SeoForException
     */
    public function getValidatorRules(?string $for)
    {
        $mainRules = [
            'h1' => 'string|max:255|nullable',
            'title' => 'string|max:255|nullable',
            'url' => 'string|max:255|nullable',
        ];
        switch ($for){
            case SeoInformation::FOR_MAIN_PAGE :
                $rules = [];
                break;

            case SeoInformation::FOR_MAIN_NEWS :
                $rules = [];
                break;

            case SeoInformation::FOR_LIST_NEWS :
                $rules = [];
                break;

            case SeoInformation::FOR_NEWS :
                $rules = [ 'news_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_OBJECT_PAGE :
                $rules = [ 'object_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_DISEASE :
                $rules = [ 'disease_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_MEDICAL_PROFILE :
                $rules = [ 'medical_profile_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_THERAPY :
                $rules = [ 'therapy_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_COUNTRY :
                $rules = [ 'country_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_REGION :
                $rules = [ 'region_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_CITY :
                $rules = [ 'city_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_SEARCH_OBJECT_PAGE :
                $rules = [];
                break;

            case SeoInformation::FOR_SEARCH_DISEASE :
                $rules = [];
                break;

            case SeoInformation::FOR_SEARCH_MEDICAL_PROFILE :
                $rules = [];
                break;

            case SeoInformation::FOR_SEARCH_THERAPY :
                $rules = [];
                break;

            case SeoInformation::FOR_SEARCH_GEO :
                $rules = [];
                break;

            case SeoInformation::FOR_FEEDBACK :
                $rules = [];
                break;

            case SeoInformation::FOR_PARTNERS :
                $rules = [];
                break;

            case SeoInformation::FOR_OFFER :
                $rules = [ 'offer_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_ABOUT :
                $rules = [ 'about_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_PARTNER :
                $rules = [ 'partner_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_PUBLICATION :
                $rules = [ 'publication_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_SERVICE :
                $rules = [ 'service_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_PARTNER_TYPE :
                $rules = [ 'partner_type_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_PUBLICATION_TYPE :
                $rules = [ 'publication_type_id' => 'required|integer' ];
                break;

            case SeoInformation::FOR_OFFERS_PAGE :
                $rules = [];
                break;

            case SeoInformation::FOR_COUNTRY_LIST :
                $rules = [];
                break;

            case SeoInformation::FOR_REGION_LIST :
                $rules = [];
                break;

            case SeoInformation::FOR_CITY_LIST :
                $rules = [];
                break;

            default : throw new SeoForException();
        }

        return array_merge( $mainRules, $rules);
    }

    /**
     * Сохранение SEO информации
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws ApiProblemException
     */
    public function setMeta(array $data, $for)
    {
        $this->checkAlias($data['url'], $for);
        $seo = SeoInformation::where('for', $for)->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }


    /**
     * Получение SEO информации
     *
     * @param string $for
     * @param array|null $cond
     * @param string|null $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getMeta(string $for, array $cond = null, string $locale = null)
    {
        $filter[] = ['for', $for];
        if (!is_null($cond)){
                foreach ($cond as $field=>$id){
                    $filter[] = [$field, $id];
                }
        }
        $seoInformation = SeoInformation::where($filter);
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $seoInformation->select([
                        'for',
                        'h1_ru as h1',
                        'title_ru as title',
                        'url',
                        'meta_description_ru as meta_description',
                        'meta_keywords_ru as meta_keywords',
                    ]);
                    break;

                case 'en' :
                    $seoInformation->select([
                        'for',
                        'h1_en as h1',
                        'title_en as title',
                        'url',
                        'meta_description_en as meta_description',
                        'meta_keywords_en as meta_keywords',
                    ]);
                    break;

                default :
                    throw new ApiProblemException("Не поддерживаямая локаль", 404);
            }

        } else {
            $seoInformation->select(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
        }

        return $seoInformation->first();
    }

    /**
     * Сео для санатория
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForObject(array $data, $for)
    {
        $item = ObjectPlace::find($data['object_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'object_id', 'value' => $data['object_id']]);
            $count = ObjectPlace::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('object_id', $data['object_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для новости
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForNews(array $data, $for)
    {
        $item = News::find($data['news_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'news_id', 'value' => $data['news_id']]);
            $count = News::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('news_id', $data['news_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для заболевания
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForDisease(array $data, $for)
    {
        $item = Disease::find($data['disease_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'disease_id', 'value' => $data['disease_id']]);
            $count = Disease::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('disease_id', $data['disease_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для метода лечения
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForTherapy(array $data, $for)
    {
        $item = Therapy::find($data['therapy_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'therapy_id', 'value' => $data['therapy_id']]);
            $count = Therapy::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('therapy_id', $data['therapy_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для мед. профиля
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForMedicalProfile(array $data, $for)
    {
        $item = MedicalProfile::find($data['medical_profile_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'medical_profile_id', 'value' => $data['medical_profile_id']]);
            $count = MedicalProfile::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('medical_profile_id', $data['medical_profile_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для предложения
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForOffer(array $data, $for)
    {
        $item = Offer::find($data['offer_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'offer_id', 'value' => $data['offer_id']]);
            $count = Offer::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('offer_id', $data['offer_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для предложения
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForAbout(array $data, $for)
    {
        $item = About::find($data['about_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'about_id', 'value' => $data['about_id']]);
            $count = About::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('about_id', $data['about_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для страницы города
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForCity(array $data, $for)
    {
        $item = City::find($data['city_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'city_id', 'value' => $data['city_id']]);
            $count = City::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('city_id', $data['city_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для страницы страны
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForCountry(array $data, $for)
    {
        $item = Country::find($data['country_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'country_id', 'value' => $data['country_id']]);
            $count = Country::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('country_id', $data['country_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для страницы региона
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws NotFoundException
     * @throws ApiProblemException
     */
    public function setForRegion(array $data, $for)
    {
        $item = Region::find($data['region_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'region_id', 'value' => $data['region_id']]);
            $count = Region::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('region_id', $data['region_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для страницы партнера
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws ApiProblemException
     * @throws NotFoundException
     */
    public function setForPartner(array $data, $for)
    {
        $item = Partner::find($data['partner_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'partner_id', 'value' => $data['partner_id']]);
            $count = Partner::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('partner_id', $data['partner_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Сео для страницы публикации
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws ApiProblemException
     * @throws NotFoundException
     */
    public function setForPublication(array $data, $for)
    {
        $item = Publication::find($data['publication_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'publication_id', 'value' => $data['publication_id']]);
            $count = Publication::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('publication_id', $data['publication_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Seo для услуг
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws ApiProblemException
     * @throws NotFoundException
     */
    public function setForService(array $data, $for)
    {
        $item = Service::find($data['service_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'service_id', 'value' => $data['service_id']]);
            $count = Service::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('service_id', $data['service_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Seo для типа партнеров
     *
     * @param array $data
     * @param $for
     * @return mixed
     * @throws ApiProblemException
     * @throws NotFoundException
     */
    public function setForPartnerType(array $data, $for)
    {
        $item = PartnerType::find($data['partner_type_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'partner_type_id', 'value' => $data['partner_type_id']]);
            $count = PartnerType::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('partner_type_id', $data['partner_type_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }

    public function setForPublicationType(array $data, $for)
    {
        $item = PublicationType::find($data['publication_type_id']);
        if (is_null($item)) throw new NotFoundException();

        if (!empty($data['url'])){
            $this->checkAlias($data['url'], $for, ['field' => 'publication_type_id', 'value' => $data['publication_type_id']]);
            $count = PublicationType::where('alias', $data['url'])->where('id', '<>', $item->id)->count();
            if ($count > 0) throw new ApiProblemException('Указанный url уже используется', 412);

            $item->alias = $data['url'];
            $item->save();
        }
        $seo = SeoInformation::where('publication_type_id', $data['publication_type_id'])->first();
        if (is_null($seo)){
            $seo = new SeoInformation();
            $seo->for = $for;
        }
        foreach ($data as $field=>$value){
            $seo->$field = $value;
        }
        $seo->save();

        $this->refreshOrder();

        return $seo->where('id', $seo->id)
            ->first(['for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru', 'meta_description_en',
                'meta_keywords_ru', 'meta_keywords_en']);
    }




    /**
     * Проверка существование алиаса
     *
     * @param string $alias
     * @param string $for
     * @param array|null $exclude
     * @throws ApiProblemException
     */
    public function checkAlias(?string $alias, string $for, array $exclude = null)
    {
        if (!is_null($exclude)) {
            $seo = SeoInformation::where($exclude['field'], $exclude['value'])->first();
            $count = SeoInformation::where('url', $alias);
            if (!is_null($seo))
                $count->where('id', '<>', $seo->id);
            $count = $count->count();
        } else {
            $count = SeoInformation::where('url', $alias)->where('for', '<>', $for)
                ->count();
        }

        if ($count > 0)
            throw new ApiProblemException('Алиас уже используется', 400);
    }

    /**
     * Обновление порядка фильтрации
     */
    public function refreshOrder()
    {
        return;

        $order = 1;
        /**
         * ======================= Блок № 1 =====================================================
         */
        $items = SeoInformation::where('for', 'country-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 2 =====================================================
         */
        $items = SeoInformation::where('for', 'region-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 3 =====================================================
         */
        $items = SeoInformation::where('for', 'city-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 4 =====================================================
         */
        $items = SeoInformation::where('for', 'medical-profile-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 5 =====================================================
         */
        $items = SeoInformation::where('for', 'therapy-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 6 =====================================================
         */
        $items = SeoInformation::where('for', 'disease-page')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
        /**
         * ======================= Блок № 7 =====================================================
         */
        $items = SeoInformation::where('for', 'service')->orderBy('id')->whereNotNull('url')->get();
        foreach ($items as $item){
            $item->order = $order;
            $order++;
            $item->save();
        }
    }

    /**
     * Получение robots.txt
     *
     * @return array
     */
    public function getRobots()
    {
        $filePath = storage_path('app/seo/robots.txt');
        $content = file_get_contents($filePath);

        return ['content' => $content];
    }

    /**
     * Сохранение robots.txt
     *
     * @param $content
     */
    public  function setRobots($content)
    {
        $filePath = storage_path('app/seo/robots.txt');

        activity()->performedOn( new SeoInformation )
            ->withProperties(['content' => $content])
            ->log('файл robots.txt обновлен');

        file_put_contents($filePath, $content);
    }

    /**
     * Редактирование/Создание шаблона
     *
     * @param string $for
     * @param array $data
     * @return SeoTemplate
     */
    public function setTemplate(string $for, array $data)
    {
        $template = SeoTemplate::where('for', $for)->first();
        if (is_null($template)){
            $template = new SeoTemplate();
            $template->for = $for;
        }
        foreach ($data as $field=>$value ){
            $template->$field = $value;
        }
        $template->save();

        return $template;
    }

    /**
     * Получение шаблона
     *
     * @param string $for
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getTemplate(string $for, ?string $locale = null)
    {
        $template = SeoTemplate::where('for', $for);
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $template->select([
                        'for',
                        'discount_ru as discount',
                        'country_ru as country',
                        'region_ru as region',
                        'city_ru as city',
                        'medical_profiles_ru as medical_profiles',
                        'therapies_ru as therapies',
                        'diseases_ru as diseases',
                        'services_ru as services',
                        'beside_ru as beside',
                        'stars_ru as stars',
                        'rating_ru as rating',
                        'meta_description_ru as meta_description',
                        'text_en as text',
                    ]);
                    break;

                case 'en' :
                    $template->select([
                        'for',
                        'discount_en as discount',
                        'country_en as country',
                        'region_en as region',
                        'city_en as city',
                        'medical_profiles_en as medical_profiles',
                        'therapies_en as therapies',
                        'diseases_en as diseases',
                        'services_en as services',
                        'beside_en as beside',
                        'stars_en as stars',
                        'rating_en as rating',
                        'meta_description_en as meta_description',
                        'text_en as text',
                    ]);
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
        }

        $template = $template->first();
        if (is_null($template))
            throw new ApiProblemException('Шаблон не найден', 404);

        return $template;
    }

    /**
     * Валидация for для сохранения шаблона
     *
     * @param null|string $for
     * @throws ApiProblemException
     */
    public function validateTemplate(?string $for)
    {
        if (is_null($for))
            throw new ApiProblemException('Параметр for не отправлен', 400);

        $valid = in_array($for, [
            'discount',
            'country',
            'region',
            'city',
            'medical_profiles',
            'therapies',
            'diseases',
            'services',
            'beside',
            'stars',
            'rating',
            ]);
        if (!$valid)
            throw new ApiProblemException("Параметр for: $for не определен на сервере");
    }

    /**
     * Получение списка url для фильтра здравниц
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @return array
     */
    public function listSeoFilterUrl(int $page, int $rowsPerPage, ?string $searchKey = null)
    {
        $skip = $skip = ($page - 1)* $rowsPerPage;
        $seoFilterUrls = SeoFilterUrl::whereNotNull('id');
        if (!is_null($searchKey)){
            $searchKey = mb_strtolower($searchKey);
            $seoFilterUrls->whereRaw("lower(url) LIKE '%{$searchKey}%'");
        }
        $total = $seoFilterUrls->count();
        $items = $seoFilterUrls->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение мета-данных по id url фильтра здравниц
     *
     * @param int $seoFilterUrlId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getSeoFilterUrl(int $seoFilterUrlId)
    {
        $seoFilterUrl = SeoFilterUrl::find($seoFilterUrlId);
        if (is_null($seoFilterUrl))
            throw new ApiProblemException('Запись c указанным ID не найдена', 404);

        return $seoFilterUrl;
    }

    /**
     * Сохранение или обновление мета-данных url фильтра здравниц
     *
     * @param array $data
     * @param int|null $seoFilterUrlId
     * @return mixed
     * @throws ApiProblemException
     */
    public function setSeoFilterUrl(array $data, ?int $seoFilterUrlId = null)
    {
        if (!is_null($seoFilterUrlId)){
            $seoFilterUrl = SeoFilterUrl::find($seoFilterUrlId);
            if (is_null($seoFilterUrl))
                throw new ApiProblemException('Запись c указанным ID не найдена', 404);

            $countUrl = SeoFilterUrl::where('url', $data['url'])->where('id', '<>', $seoFilterUrlId)->count();
            if ($countUrl > 0)
                throw new ApiProblemException('Указанный url уже существует', 422);

        } else {
            $countUrl = SeoFilterUrl::where('url', $data['url'])->count();
            if ($countUrl > 0)
                throw new ApiProblemException('Указанный url уже существует', 422);

            $seoFilterUrl = new SeoFilterUrl();
        }
        foreach ($data as $field => $value){
            $seoFilterUrl->$field = $value;
        }
        $seoFilterUrl->save();

        return $this->getSeoFilterUrl($seoFilterUrl->id);
    }

    /**
     * Удаление мета-данных url фильтра здравниц
     *
     * @param int $seoFilterUrlId
     * @throws ApiProblemException
     */
    public function deleteSeoFilterUrl(int $seoFilterUrlId)
    {
        $seoFilterUrl = SeoFilterUrl::find($seoFilterUrlId);
        if (is_null($seoFilterUrl))
            throw new ApiProblemException('Запись c указанным ID не найдена', 404);

        $seoFilterUrl->delete();
    }
}
