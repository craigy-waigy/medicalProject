<?php

namespace App\Services;

use App\Exceptions\BannerActivateException;
use App\Exceptions\MainPageMetaException;
use App\Exceptions\NotFoundException;
use App\Models\MainBanner;
use DemeterChain\Main;
use Illuminate\Http\Request;
use App\Models\MainPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MainPageService
{
    /**
     * Получение данных главной страницы
     *
     * @return array
     */
    public function getMainPage()
    {
        $objects = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)->count();

        $regions = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)
            ->distinct('region_id')->count('region_id');

        $therapies = DB::table('object_therapies')
            ->join('objects', 'objects.id', '=', 'object_therapies.object_id')
            ->where('objects.is_deleted', '=', false)
            ->where('objects.is_visibly', '=', true)
            ->count();
        $content = DB::table('main_page')->select([
            'meta', 'content_ru', 'content_en'
        ])->whereIn('meta', [
            'how_it_work',
            'video_link',
            'it_is_useful',
            'it_is_reliable',
            'it_is_profitable',
        ])->get();
        $contentResponse = [];
        foreach ($content as $value){
            $contentResponse[$value->meta] = [ 'content_ru' => $value->content_ru, 'content_en' => $value->content_en];
        }
        $rightBanners = DB::table('main_page')->whereIn('meta', ['right_banner'])->orderBy('sorting')->get();
        $rightBannerLinks = DB::table('main_page')->select([
            'meta', 'content_ru', 'content_en'
        ])->whereIn('meta', ['right_banner_link'])->get();

        $mainBanners = MainBanner::all();

        return [
            'main_banners' => $mainBanners,
            'right_banners' => ['banners' => $rightBanners, 'links' => $rightBannerLinks],
            'content' => (object)$contentResponse,
            'statistic' => [
                'objects' => $objects,
                'regions' => $regions,
                'therapies' => $therapies,
            ]
        ];
    }

    /**
     * Обновление данный главной страницы
     *
     * @param string $meta
     * @param string $contentRu
     * @param string $contentEn
     * @return array
     */
    public function updateMainPage(string $meta, ?string $contentRu, ?string $contentEn)
    {
        $mainPage = MainPage::where('meta', $meta)->first();
        if (is_null($mainPage)){
            $mainPage = new MainPage();
            $mainPage->meta = $meta;
        }
        $mainPage->content_ru = $contentRu;
        $mainPage->content_en = $contentEn;
        $mainPage->save();

        return $this->getMainPage();
    }

    /**
     * Добавление баннера
     *
     * @param string|null $meta
     * @param Request $request
     * @return MainBanner
     * @throws MainPageMetaException
     */
    public function addBanner(Request $request, string $meta = null)
    {
        $mainBanner = new MainBanner();
        $banner_resolutions = [
            'desktop' => ['content_ru' => null, 'content_en' => null],
            'tablet' => ['content_ru' => null, 'content_en' => null],
            'mobile' => ['content_ru' => null, 'content_en' => null],
        ];
        switch ($meta){
            case 'main_banner_desktop':
                if ($request->hasFile('content_ru')){
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['desktop']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['desktop']['content_en'] = Storage::url($path);
                }
                break;

            case 'main_banner_tablet':
                if ($request->hasFile('content_ru')){
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['tablet']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['tablet']['content_en'] = Storage::url($path);
                }
                break;

            case 'main_banner_mobile':
                if ($request->hasFile('content_ru')){
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['mobile']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['mobile']['content_en'] = Storage::url($path);
                }
                break;

            case null: break;

            default : throw new MainPageMetaException();
        }
        $data = $request->only('description_ru', 'description_en', 'sorting');
        foreach ($data as $field=>$value) {
            $mainBanner->$field = $value;
        }
        $mainBanner->banner_resolutions = $banner_resolutions;
        $mainBanner->save();

        return $mainBanner;
    }

    /**
     * Обновить баннер
     *
     * @param int $bannerId
     * @param Request $request
     * @return mixed
     * @throws NotFoundException
     */
    public function updateBanner(Request $request, int $bannerId)
    {
        $mainBanner = MainBanner::where([
            ['id', $bannerId],
        ])->first();
        if (is_null($mainBanner)) throw new NotFoundException();

        $banner_resolutions = $mainBanner->banner_resolutions;

        $meta = $request->get('meta');
        switch ($meta){
            case 'main_banner_desktop':
                if ($request->hasFile('content_ru')){
                    $path = 'main_page/' . basename($banner_resolutions['desktop']['content_ru']);
                    Storage::delete($path);
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['desktop']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = 'main_page/' . basename($banner_resolutions['desktop']['content_en']);
                    Storage::delete($path);
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['desktop']['content_en'] = Storage::url($path);
                }
                break;

            case 'main_banner_tablet':
                if ($request->hasFile('content_ru')){
                    $path = 'main_page/' . basename($banner_resolutions['tablet']['content_ru']);
                    Storage::delete($path);
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['tablet']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = 'main_page/' . basename($banner_resolutions['tablet']['content_en']);
                    Storage::delete($path);
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['tablet']['content_en'] = Storage::url($path);
                }
                break;

            case 'main_banner_mobile':
                if ($request->hasFile('content_ru')){
                    $path = 'main_page/' . basename($banner_resolutions['mobile']['content_ru']);
                    Storage::delete($path);
                    $path = $request->file('content_ru')->store('main_page');
                    $banner_resolutions['mobile']['content_ru'] = Storage::url($path);
                }
                if ($request->hasFile('content_en')){
                    $path = 'main_page/' . basename($banner_resolutions['mobile']['content_en']);
                    Storage::delete($path);
                    $path = $request->file('content_en')->store('main_page');
                    $banner_resolutions['mobile']['content_en'] = Storage::url($path);
                }
                break;

            case null: break;

            default : throw new MainPageMetaException();
        }
        $data = $request->only('description_ru', 'description_en', 'sorting');
        foreach ($data as $field=>$value){
            $mainBanner->$field = $value;
        }
        if (!is_null($meta)) $mainBanner->banner_resolutions = $banner_resolutions;
        $mainBanner->save();

        $active = $request->get('active');
        if (!is_null($active)){
            if (!$mainBanner->active && $active){
                $this->checkReadyForActivate($bannerId);
                $mainBanner->active = true;
                $mainBanner->save();
            } else {
                $mainBanner->active = false;
                $mainBanner->save();
            }
        }

        return $mainBanner;
    }

    /**
     * Сортировка баннеров
     *
     * @param array $banners
     * @return array
     */
    public function sortingBanner( array $banners)
    {
        foreach ($banners as $sorting=>$id){
            MainBanner::where('id', $id)->update([
                'sorting' => $sorting
            ]);
        }

        return $this->getMainPage();
    }

    /**
     * Получение русской версии
     *
     * @return array
     */
    public function getMainPageRU()
    {
        $objects = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)->count();

        $regions = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)
            ->distinct('region_id')->count('region_id');

        $therapies = DB::table('therapy')
            ->where('active', '=', true)
            ->whereRaw("id in(
                SELECT therapy_id FROM object_therapies WHERE object_id in(
                    SELECT id FROM objects WHERE is_visibly = TRUE
                )
            )")->count();
        $content = DB::table('main_page')->select([
            'meta', 'content_ru as content'
        ])->whereIn('meta', [
            'how_it_work',
            'video_link',
            'it_is_useful',
            'it_is_reliable',
            'it_is_profitable',
        ])->get();
        $contentResponse = [];
        foreach ($content as $value){
            $contentResponse[$value->meta] =  $value->content;
        }

        $rightBanners = DB::table('main_page')->select([
          'id', 'meta', 'content_ru as content', 'description_ru as description', 'sorting',
        ])->whereIn('meta', ['right_banner'])->orderBy('sorting')->get();
        $rightBannerLinks = DB::table('main_page')->select([
            'meta', 'content_ru as content'
        ])->whereIn('meta', ['right_banner_link'])->get();

       $mainBanners = MainBanner::where('active', true)
           ->select(['id', 'description_ru as description', 'sorting', 'banner_resolutions'])->orderBy('sorting')
           ->get();
       $resultMainBanners = [];
       foreach ($mainBanners as $banner){
           $resolutions = $banner->banner_resolutions;
           unset($banner->banner_resolutions);
           $newResolution = [];
           foreach ($resolutions as $resolution=>$value){
               $newResolution[$resolution] = $value['content_ru'];
           }
           $banner->banner_resolutions = $newResolution;
           $resultMainBanners[] = $banner;
       }

        $seo = DB::table('seo_information')->select(['h1_ru as h1', 'title_ru as title', 'url',
            'meta_description_ru as meta_description', 'meta_keywords_ru as meta_keywords'])
            ->where('for', '=', 'main-page')->first();

        return [
            'main_banners' => $resultMainBanners,
            'right_banners' => ['banners' => $rightBanners, 'links' => $rightBannerLinks],
            'content' => (object)$contentResponse,
            'statistic' => [
                'objects' => $objects,
                'regions' => $regions,
                'therapies' => $therapies,
            ],
            'seo' => $seo
        ];
    }

    /**
     * Получение английской версии
     *
     * @return array
     */
    public function getMainPageEN()
    {
        $objects = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)->count();

        $regions = DB::table('objects')
            ->where('is_deleted', '=', false)
            ->where('is_visibly', '=', true)
            ->distinct('region_id')->count('region_id');

        $therapies = DB::table('therapy')
            ->where('active', '=', true)
            ->whereRaw("id in(
                SELECT therapy_id FROM object_therapies WHERE object_id in(
                    SELECT id FROM objects WHERE is_visibly = TRUE
                )
            )")->count();
        $content = DB::table('main_page')->select([
            'meta', 'content_en as content'
        ])->whereIn('meta', [
            'how_it_work',
            'video_link',
            'it_is_useful',
            'it_is_reliable',
            'it_is_profitable',
        ])->get();
        $contentResponse = [];
        foreach ($content as $value){
            $contentResponse[$value->meta] =  $value->content;
        }

        $rightBanners = DB::table('main_page')->select([
            'id', 'meta', 'content_en as content', 'description_en as description', 'sorting',
        ])->whereIn('meta', ['right_banner'])->orderBy('sorting')->get();
        $rightBannerLinks = DB::table('main_page')->select([
            'meta', 'content_en as content '
        ])->whereIn('meta', ['right_banner_link'])->get();

        $mainBanners = MainBanner::where('active', true)
            ->select(['id', 'description_en as description', 'sorting', 'banner_resolutions'])->orderBy('sorting')
            ->get();
        $resultMainBanners = [];
        foreach ($mainBanners as $banner){
            $resolutions = $banner->banner_resolutions;
            unset($banner->banner_resolutions);
            $newResolution = [];
            foreach ($resolutions as $resolution=>$value){
                $newResolution[$resolution] = $value['content_en'];
            }
            $banner->banner_resolutions = $newResolution;
            $resultMainBanners[] = $banner;
        }

        $seo = DB::table('seo_information')->select(['h1_en as h1', 'title_en as title', 'url',
        'meta_description_en as meta_description', 'meta_keywords_en as meta_keywords'])
            ->where('for', '=', 'main-page')->first();

        return [
            'main_banners' => $resultMainBanners,
            'right_banners' => ['banners' => $rightBanners, 'links' => $rightBannerLinks],
            'content' => (object)$contentResponse,
            'statistic' => [
                'objects' => $objects,
                'regions' => $regions,
                'therapies' => $therapies,
            ],
            'seo' => $seo
        ];
    }

    /**
     * Удаление баннера
     *
     * @param int $bannerId
     * @throws NotFoundException
     */
    public function deleteBanner(int $bannerId)
    {
        $mainBanner = MainBanner::where([
            ['id', $bannerId],
        ])->first();
        if (is_null($mainBanner)) throw new NotFoundException();

        $banner_resolutions = $mainBanner->banner_resolutions;
        foreach ($banner_resolutions as $banner_resolution){
            $path = 'main_page/' . basename($banner_resolution['content_ru']);
            Storage::delete($path);
            $path = 'main_page/' . basename($banner_resolution['content_en']);
            Storage::delete($path);
        }
        $mainBanner->delete();
    }

    /**
     * Проверка активации баннера
     *
     * @param int $bannerId
     * @throws BannerActivateException
     */
    public function checkReadyForActivate(int $bannerId)
    {
        $mainBanner = MainBanner::where([
            ['id', $bannerId],
        ])->first();
        $banner_resolutions = $mainBanner->banner_resolutions;
        foreach ($banner_resolutions as $resolution=>$banner_resolution){
             if (empty($banner_resolution['content_ru']) || is_null($banner_resolution['content_ru'])){
                 $message = 'не определен баннер ru для разрешения: ' . $resolution;
                 throw new BannerActivateException($message);
             }
            if (empty($banner_resolution['content_en']) || is_null($banner_resolution['content_en'])){
                $message = 'не определен баннер en для разрешения: ' . $resolution;
                throw new BannerActivateException($message);
            }
        }
        if (is_null($mainBanner->description_ru)) throw new BannerActivateException('Пустое описание на русском');
        if (is_null($mainBanner->description_en)) throw new BannerActivateException('Пустое описание на английском');
    }
}
