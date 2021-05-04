<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\About;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;
use Illuminate\Support\Facades\Storage;

class AboutService
{
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * AboutService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Создание раздела
     *
     * @param Request $request
     * @return About
     */
    public function add(Request $request)
    {
        $about =  new About();
        if ($request->hasFile('image')){
            Storage::delete('about/' . basename($about->image));
            $path = $request->file('image')->store('about');
            $about->image = Storage::url($path);
        }
        $data = $request
            ->only('title_ru', 'title_en', 'about_ru', 'about_en', 'is_published', 'publish_date', 'parent', 'default_page');
        foreach ($data as $field=>$value){
            if ($field == 'default_page' && $value == true){
                About::whereNotNull('id')->update([ 'default_page' => false]);
                $about->default_page = true;
            } else {
                $about->$field = $value;
            }
        }
        $about->save();

        return $about;
    }

    /**
     * Редактирование
     *
     * @param Request $request
     * @param int $aboutId
     * @return mixed
     * @throws ApiProblemException
     */
    public function edit(Request $request, int $aboutId)
    {
        $about =  About::where('id', $aboutId)->first();
        if (is_null($about)) throw new ApiProblemException('Раздел не найден'< 404);

        if ($request->hasFile('image')){
            Storage::delete('about/' . basename($about->image));
            $path = $request->file('image')->store('about');
            $about->image = Storage::url($path);
        }
        $data = $request
            ->only('title_ru', 'title_en', 'about_ru', 'about_en', 'is_published', 'publish_date', 'parent', 'default_page');
        foreach ($data as $field=>$value){
            if ($field == 'default_page' && $value == true){
                About::where('id', '<>', $aboutId)->update([ 'default_page' => false]);
                $about->default_page = true;
            } else {
                $about->$field = $value;
            }
        }
        $about->save();

        About::whereNotNull('id')->update([
            'has_children' => false
        ]);
        About::whereRaw("id IN(SELECT parent FROM about)")->update([ //Ставим статусы - has_children у кого они есть
            'has_children' => true
        ]);

        return $about;
    }

    /**
     * Получение списка разделов
     *
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function search(?int $page, ?int $rowsPerPage, ?string $searchKey, ?array $sorting = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);

        $qb = About::when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $query = $query->orderBy($key, $value);
                }
                return $query;
            } else {
                return $query->orderBy('updated_at', 'desc');
            }
        });
        if (!is_null($locale)){
            $qb = About::where('is_published', true)->whereNotNull('alias');
            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                            return $query;
                        }
                    })->select(['id', 'parent', 'title_ru as title', 'about_ru as about', 'alias', 'publish_date',
                        'default_page', 'has_children']);
                    $qb->with('aboutParent:id,title_ru as title');
                    break;
                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                            return $query;
                        }
                    })->select(['id', 'parent', 'title_en as title', 'about_en as about', 'alias', 'publish_date',
                        'default_page', 'has_children']);
                    $qb->with('aboutParent:id,title_en as title');
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локать', 422);
            }
            $qb = $this->getAboutLocaleFilter($qb, $locale);
        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            });
            $qb->with('aboutParent:id,title_ru');
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    public function getTree(string $locale)
    {
        $abouts = About::where('is_published', true)->whereNotNull('alias');
        switch ($locale){
            case 'ru' :
                $abouts->select(['id', 'parent', 'title_ru as title', 'alias', 'default_page', 'has_children']);
                break;

            case 'en' :
                $abouts->select(['id', 'parent', 'title_en as title', 'alias', 'default_page', 'has_children']);
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }
        $abouts = $this->getAboutLocaleFilter($abouts, $locale);

        return $abouts->get();
    }

    /**
     * Получение раздела
     *
     * @param int|null $aboutId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(?int $aboutId, ?string $locale = null, ?string $alias = null)
    {
        if (is_null($locale)){
            $about =  About::where('id', $aboutId)->with('seo');
        } else {
            $about = About::where('alias', $alias)->where('is_published', true);
            switch ($locale){
                case 'ru' :
                    $about->select(['id', 'parent', 'title_ru as title', 'about_ru as about', 'alias', 'publish_date',
                        'default_page', 'has_children']);
                    $about->with(
                        'seo:id,about_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords'
                    );
                    break;

                case 'en' :
                    $about->select(['id', 'parent', 'title_en as title', 'about_en as about', 'alias', 'publish_date',
                        'default_page', 'has_children']);
                    $about->with(
                        'seo:id,about_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $about = $this->getAboutLocaleFilter($about, $locale);
        }
        $about = $about->first();
        if (is_null($about)) throw new ApiProblemException('Раздел не найден', 404);

        return $about;
    }

    /**
     * Получение дефолтной страницы
     *
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getDefault(?string $locale = 'ru')
    {
        $about = About::where('default_page', true)->where('is_published', true);
        switch ($locale){
            case 'ru' :
                $about->select(['id', 'parent', 'title_ru as title', 'about_ru as about', 'alias', 'publish_date',
                    'default_page', 'has_children']);
                $about->with(
                    'seo:id,about_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords'
                );
                break;

            case 'en' :
                $about->select(['id', 'parent', 'title_en as title', 'about_en as about', 'alias', 'publish_date',
                    'default_page', 'has_children']);
                $about->with(
                    'seo:id,about_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords'
                );
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }

        $about = $this->getAboutLocaleFilter($about, $locale);
        $about = $about->first();

        if (is_null($about))
            throw new ApiProblemException('Раздел не найден', 404);

        return $about;
    }

    /**
     * Удаление раздела
     *
     * @param int $aboutId
     * @throws ApiProblemException
     */
    public function delete(int $aboutId)
    {
        $about =  About::where('id', $aboutId)->with('seo')->first();
        if (is_null($about)) throw new ApiProblemException('Раздел не найден', 404);

        $about->seo()->delete();
        foreach ($about->files as $file){
            $file->delete();
        }
        foreach ($about->files as $file){
            Storage::delete('files/' . basename($file->file));
            $file->delete();
        }
        $about->delete();
    }
}
