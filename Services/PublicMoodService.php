<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\Mood;
use App\Libraries\Models\PaginatorFormat;

class PublicMoodService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * MoodService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск Mood-тегов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @return array
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting = null, $locale = 'ru')
    {
        $skip = ($page - 1) * $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Mood::
            when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $orderBy = $query->orderBy($key, $value);
                    }
                    return $orderBy;
                } else {
                    return $query->orderBy('id', 'desc');
                }
            });

        switch ($locale){
            case 'ru' :
                $qb->when($searchKey, function ($query, $searchKey){
                    if (!is_null($searchKey)){
                        $query = $query->whereRaw("lower(name_ru) LIKE '%{$searchKey}%'");

                        return $query;
                    }
                })->select(['id', 'name_ru as name', 'alias', 'crop_image']);
                break;

            case 'en' :
                $qb->when($searchKey, function ($query, $searchKey){
                    if (!is_null($searchKey)){
                        $query = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                        return $query;
                    }
                })->select(['id', 'name_en as name', 'alias', 'crop_image']);
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

}
