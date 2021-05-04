<?php

namespace App\Http\Controllers\Api\EN;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\MainPageService;

class MainPageController extends Controller
{
    /**
     * @var MainPageService
     */
    protected $mainPageService;

    /**
     * MainPageController constructor.
     */
    public function __construct()
    {
        $this->mainPageService = new MainPageService();
    }

    /**
     * @return array
     */
    public function getMainPage()
    {
        return $this->mainPageService->getMainPageEN();
    }
}
