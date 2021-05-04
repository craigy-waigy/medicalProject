<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Offer;
use App\Models\OfferImage;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfferService
{
    use ImageTrait;
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * NewsService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск по предложениям
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $scope
     * @param array|null $sorting
     * @param bool $fromAccount
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $scope, ?array $sorting,
                           bool $fromAccount = false, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Offer::
        when($scope, function ($query, $scope) use($fromAccount){
            if ( !is_null($scope)) {
                foreach ($scope as $value){

                    if ($fromAccount) $query = $query->whereJsonContains('scope', $value, 'or');
                    else $query = $query->whereJsonContains('scope', $value, 'and');
                }
                return $query;
            }
        })
            ->when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    return $query;
                } else {
                    return $query->orderBy('id', 'asc');
                }
            });
        ;

        if (!is_null($locale)){
            $nowDate = (new \DateTime('now'))->format('Y-m-d');
            $qb->where('published_at', '<=', $nowDate);

            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select(['id',  'title_ru as title', 'short_description_ru as description','image', 'alias',
                        'published_at', 'has_booking']);
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_en) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select(['id',  'title_en as title', 'short_description_en as description','image', 'alias',
                        'published_at', 'has_booking']);
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $qb = $this->getOfferLocaleFilter($qb, $locale);

        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey)) {
                    $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            });
        }

        if ($fromAccount){
            $qb->where('is_visible', true);
        }
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);

    }

    /**
     * Создание предложения
     *
     * @param Request $request
     * @return Offer
     * @throws ApiProblemException
     */
    public function create(Request $request)
    {
        $offer = new Offer();
        if ($request->hasFile('image'))
        {
            $path = $request->file('image')->store('offer');
            $offer->image = Storage::url($path);
        }
        $data = $request->only(
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'scope',
            'has_booking',
            'is_visible',
            'short_description_ru',
            'short_description_en',
            'published_at'
        );
        foreach ($data as $field => $value){
            if ($field == 'scope'){
                $offer->$field = json_decode($value, true);
            } else {
                if ( is_null($value) ) $value = '';
                $offer->$field = $value;
            }
        }
        if ($offer->is_visible){
            if ( is_null($offer->published_at) )
                throw new ApiProblemException('Не указана дата публикации', 404);
        }
        $offer->save();

        return $offer;
    }

    /**
     * Получение предложения
     *
     * @param int|null $offerId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(?int $offerId, ?string $locale = null, ?string $alias = null)
    {
        if (is_null($locale)){
            $offer = Offer::where('id', $offerId)->with('seo')->first();
            if (is_null($offer)) throw new ApiProblemException('Запись не найдена', 404);

        } else {
            $nowDate = (new \DateTime('now'))->format('Y-m-d');
            $offer = Offer::where('alias', $alias)->where('is_visible', true)->where('published_at', '<=', $nowDate);
            switch ($locale){
                case 'ru' :
                    $offer->select(['id', 'title_ru as title', 'description_ru as description',
                         'published_at', 'image', 'alias', 'has_booking']);
                    $offer->with(
                        'seo:id,offer_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'images:id,offer_id,image,description'
                    );
                    break;

                case 'en' :
                    $offer->select(['id', 'title_en as title', 'description_en as description',
                        'published_at', 'image', 'alias', 'has_booking']);
                    $offer->with(
                        'seo:id,news_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'images:id,offer_id,image,description'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $offer = $this->getOfferLocaleFilter($offer, $locale);
            $offer = $offer->first();
        }
        if (is_null($offer)) throw new ApiProblemException('Запись не найдена', 404);

        return $offer;
    }

    /**
     * Обновление предложения
     *
     * @param Request $request
     * @param int $offerId
     * @return bool
     * @throws ApiProblemException
     */
    public function update(Request $request, int $offerId)
    {
        $offer = Offer::find($offerId);
        if (is_null($offer)) throw new ApiProblemException('Предложение не найдено', 404);

        if ($request->hasFile('image'))
        {
            Storage::delete('offer/' . basename($offer->image));
            $path = $request->file('image')->store('offer');
            $offer->image = Storage::url($path);
        }
        $data = $request->only(
            'title_ru',
            'title_en',
            'description_ru',
            'description_en',
            'scope',
            'is_visible',
            'has_booking',
            'short_description_ru',
            'short_description_en',
            'published_at'
        );
        foreach ($data as $field => $value){
            if ($field == 'scope'){
                $offer->$field = json_decode($value, true);
            } else {
                if ( is_null($value) ) $value = '';
                $offer->$field = $value;
            }
        }
        if ($offer->is_visible){
            if ( is_null($offer->published_at) )
                throw new ApiProblemException('Не указана дата публикации', 404);
        }
        $offer->save();

        return $offer;
    }

    /**
     * Удаление предложения
     *
     * @param int $offerId
     * @return bool
     */
    public function delete(int $offerId)
    {
        $images = OfferImage::where('offer_id', $offerId)->get();
        if (!is_null($images)){
            foreach ($images as $image){
                Storage::delete('offer/' . basename($image->image));
                $image->delete();
            }
        }

        $offer = Offer::find($offerId);
        if (!is_null($offer)){
            Storage::delete('offer/' . basename($offer->image));
            $offer->seo()->delete();
            $offer->delete();
            $deleted = true;
        } else {

            $deleted = false;
        }

        return $deleted;
    }

    /**
     * Добавление изображения
     *
     * @param Request $request
     * @param int $offerId
     * @return OfferImage|null
     */
    public function addImage(Request $request, int $offerId)
    {
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('offer');
            $image = new OfferImage();
            $image->offer_id = $offerId;
            $image->image = Storage::url($path);
            $image->description = $request->get('description') ?? null;

            $this->optimizeImage($image->image, 'offer');

            $image->save();

            return $image;
        } else {

            return null;
        }
    }

    /**
     * Получение изображений
     *
     * @param int $offerId
     * @return mixed
     */
    public function getImages(int $offerId)
    {
        return OfferImage::where('offer_id', $offerId)->get();
    }

    /**
     * Удаление изображения
     *
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(int $imageId)
    {
        $image = OfferImage::find($imageId);
        if (!is_null($image)){
            Storage::delete('offer/' . basename($image->image));
            $image->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Изменение изображения
     *
     * @param Request $request
     * @param int $offerId
     * @return bool
     */
    public function changeImage(Request $request, int $offerId)
    {
        $offer = Offer::find($offerId);
        if ( !is_null($offer) ){
            Storage::delete('offer/' . basename($offer->image));
            $path = $request->file('image')->store('offer');
            $offer->image = Storage::url($path);
            $offer->save();
            $changed = true;

        } else {
            $changed = false;
        }

        return $changed;
    }
}
