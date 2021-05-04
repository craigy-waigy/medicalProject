<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\Banner;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;
use Illuminate\Support\Facades\Storage;

class BannerService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * BannerService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Сохранение баннера
     *
     * @param Request $request
     * @return Banner
     */
    public function add(Request $request)
    {
        $banner = new Banner;
        if ($request->hasFile('image_ru')){
            $path = $request->file('image_ru')->store('banners');
            $banner->image_ru = Storage::url($path);
        }
        if ($request->hasFile('image_en')){
            $path = $request->file('image_en')->store('banners');
            $banner->image_en = Storage::url($path);
        }
        $data = $request->only('name_ru', 'name_en', 'active', 'url_ru', 'url_en', 'code_ru', 'code_en', 'show_offer_request');
        foreach ($data as $field=>$value){
            $banner->$field = $value;
        }
        $scope = $request->get('page_scope') ?? '';
        if (!is_array($scope)) $scope = json_decode($scope, true);
        $banner->page_scope = $scope;

        $banner->save();

        return $banner;
    }

    /**
     * Редактирование баннера
     *
     * @param Request $request
     * @param int $bannerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function edit(Request $request, int $bannerId)
    {
        $banner = Banner::find($bannerId);
        if (is_null($banner)) throw new ApiProblemException('Баннер не найден', 404);

        if ($request->hasFile('image_ru')){
            $path = $request->file('image_ru')->store('banners');
            Storage::delete('banners/' . basename($banner->image_ru));
            $banner->image_ru = Storage::url($path);
        }
        if ($request->hasFile('image_en')){
            $path = $request->file('image_en')->store('banners');
            Storage::delete('banners/' . basename($banner->image_en));
            $banner->image_en = Storage::url($path);
        }
        $data = $request->only('name_ru', 'name_en', 'active', 'url_ru', 'url_en', 'code_ru', 'code_en', 'show_offer_request');
        foreach ($data as $field=>$value){
            if (!is_null($value) || $field == 'code_ru' || $field == 'code_en')
                $banner->$field = $value;
        }
        $scope = $request->get('page_scope') ?? '[]';
        if (!is_array($scope)) $scope = json_decode($scope, true);
        $banner->page_scope = $scope;

        $banner->save();

        return $banner;
    }

    /**
     * Поиск баннеров
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $scope
     * @param array|null $sorting
     * @return array
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $scope,
                           ?array $sorting = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Banner::
            when($scope, function ($query, $scope){
                if (!is_null($scope)){
                    foreach ($scope as $value){
                        $query = $query->whereJsonContains('page_scope', $value, 'and');
                    }
                    return $query;
                }
        })
            ->when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $orderBy = $query->orderBy($key, $value);
                    }
                    return $orderBy;
                } else {
                    return $query->orderBy('updated_at', 'desc');
                }
            })
            ->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    return $query->whereRaw("
                        lower(name_ru) LIKE '%{$searchKey}%' OR
                        lower(name_en) LIKE '%{$searchKey}%'
                    ");
                }
            });
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение баннера
     *
     * @param int $bannerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $bannerId)
    {
        $banner = Banner::find($bannerId);
        if (is_null($banner)) throw new ApiProblemException('Баннер не найден', 404);

        return $banner;
    }

    /**
     * Удаление баннера
     *
     * @param int $bannerId
     * @throws ApiProblemException
     */
    public function delete(int $bannerId)
    {
        $banner = Banner::find($bannerId);
        if (is_null($banner)) throw new ApiProblemException('Баннер не найден', 404);

        Storage::delete('banners/' . basename($banner->image_ru));
        Storage::delete('banners/' . basename($banner->image_en));

        $banner->delete();
    }

    /**
     * Показ баннера
     *
     * @param string $locale
     * @param array $scope
     * @return mixed
     * @throws ApiProblemException
     */
    public function getBannerPublic(string $locale, array $scope)
    {
        $qb = Banner::where('active', true);
        $qb->when($scope, function ($query, $scope){
            if (!is_null($scope)){
                foreach ($scope as $value){
                    $query = $query->whereJsonContains('page_scope', $value, 'and');
                }
                return $query;
            }
        });
        switch ($locale){
            case 'ru' :
                $qb->select(['id', 'name_ru as name', 'image_ru as image', 'url_ru as url', 'code_ru as code', 'show_offer_request']);
                break;

            case 'en' :
                $qb->select(['id', 'name_en as name', 'image_en as image', 'url_en as url', 'code_en as code', 'show_offer_request']);
                break;
             default : throw new ApiProblemException('Неподдерживаемая локаль', 422);
        }
        $count = $qb->count();
        $skip = rand(0, $count - 1);
        $banner = $qb->skip($skip)->first();

        if (is_null($banner)) throw new ApiProblemException('Баннер не найден', 404);
        $banner->increment('count_shows');
        unset($banner->count_shows);

        return $banner;
    }
}
