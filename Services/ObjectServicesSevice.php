<?php

namespace App\Services;


use App\Libraries\Models\PaginatorFormat;
use App\Models\Service;

class ObjectServicesSevice
{
    protected $paginatorFormat;

    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    public function search($page, $rowsPerPage, $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);

        $qb = Service::when($searchKey, function ($query, $searchKey){
            if ( !is_null($searchKey) ){
                $searchCond = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                $searchCond = $query->orWhereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                return $searchCond;
            }
        })->select(['id', 'name_ru', 'name_en', 'service_category_id']);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('id', 'asc')->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

}