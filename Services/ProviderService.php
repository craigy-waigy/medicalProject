<?php

namespace App\Services;

use App\Models\Provider;
use App\Libraries\Models\PaginatorFormat;

class ProviderService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ProviderService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск провайдеров
     *
     * @param int $page
     * @param int $rowsPerPage
     * @return array
     */
    public function search(int $page, int $rowsPerPage)
    {
        $skip = ($page - 1) * $rowsPerPage;

        $qb = Provider::whereNotNull('id');

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);


    }

}
