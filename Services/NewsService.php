<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Exceptions\NotFoundException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\StorageFile;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsService
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
     * Поиск по новостям
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $scope
     * @param bool $fromAccount
     * @param array|null $sorting
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $scope, bool $fromAccount = false,
                           ?array $sorting = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);

        $qb = News::
            when($scope, function ($query, $scope) use ($fromAccount){
                if ( !is_null($scope)) {

                    if (is_array($scope)) json_encode($scope);
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

        if (!is_null($locale)){
            $nowDate = (new \DateTime('now'))->format('Y-m-d');
            $qb->where('is_visible', true)->where('published_at', '<=', $nowDate)->whereNotNull('alias');
            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select(['id',  'title_ru as title', 'short_description_ru as description',
                        'link', 'image', 'alias', 'published_at']);
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_en) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select(['id', 'title_en as title', 'short_description_en as description',
                        'link', 'image', 'alias', 'published_at']);
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $qb = $this->getNewsLocaleFilter($qb, $locale);
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
     * Создание новости
     *
     * @param Request $request
     * @return News
     */
    public function createNews(Request $request)
    {
        $news = new News();
        if ($request->hasFile('image'))
        {
           $path = $request->file('image')->store('news');
           $news->image = Storage::url($path);
           $news->link = env('APP_URL') . Storage::url($path);
            $this->optimizeImage($news->image, 'news');
        }
        $news->title_ru = $request->get('title_ru');
        $news->title_en = $request->get('title_en');
        $news->description_ru = $request->get('description_ru') ?? '';
        $news->description_en = $request->get('description_en') ?? '';
        $news->short_description_ru = $request->get('short_description_ru') ?? '';
        $news->short_description_en = $request->get('short_description_en') ?? '';

        $news->is_visible = $request->get('is_visible') ?? false;


        $scope = $request->get('scope') ?? '';
        if (!is_array($scope)) $scope = json_decode($scope, true);
        $news->scope = $scope;

        $news->save();

        return $news;
    }

    /**
     * Обновление новости
     *
     * @param Request $request
     * @param int $newsId
     * @return mixed
     * @throws ApiProblemException
     */
    public function updateNews(Request $request, int $newsId)
    {
        $news = News::find($newsId);
        if (is_null($news)) throw new ApiProblemException('Новость не найдена', 404);

        if ($request->hasFile('image'))
        {
            Storage::delete('news/' . basename($news->image));
            $path = $request->file('image')->store('news');
            $news->image = Storage::url($path);
            $news->link = env('APP_URL') . Storage::url($path);
            $this->optimizeImage($news->image, 'news');
        }
        $data = $request->only('title_ru', 'title_en',
            'description_ru', 'description_en', 'is_visible', 'short_description_ru',
            'short_description_en', 'published_at');
        foreach ($data as $field=>$value){
            $news->$field = $value;
        }
        $scope = $request->get('scope') ?? '[]';
        if (!is_array($scope)) $scope = json_decode($scope, true);
        $news->scope = $scope;
        $news->save();

        return $news;
    }

    /**
     * Добавление изображения
     *
     * @param Request $request
     * @param int $newsId
     * @return int|mixed
     */
    public function addImage(Request $request, int $newsId)
    {
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('news');
            $newsImage = new NewsImage();
            $newsImage->news_id = $newsId;
            $newsImage->image = Storage::url($path);
            $newsImage->url = env('APP_URL') . Storage::url($path);
            $newsImage->thumb = env('APP_URL') . Storage::url($path);
            $newsImage->link = env('APP_URL') . Storage::url($path);
            $newsImage->description = $request->get('description') ?? null;
            $newsImage->tag = $request->get('tag') ?? null;

            $this->optimizeImage($newsImage->image, 'news');

            $newsImage->save();

            return $newsImage;
        } else {

            return null;
        }
    }

    /**
     * Удаление изображения
     *
     * @param int $newsImageId
     * @return bool
     */
    public function deleteImage(int $newsImageId)
    {
        $newsImage = NewsImage::find($newsImageId);
        if (!is_null($newsImage)){
            Storage::delete('news/' . basename($newsImage->image));
            $newsImage->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Измененить избражение
     *
     * @param Request $request
     * @param int $newsId
     * @return bool
     */
    public function changeImage(Request $request, int $newsId)
    {
        $news = News::find($newsId);
        if ( !is_null($news) ){
            Storage::delete('news/' . basename($news->image));
            $path = $request->file('image')->store('news');
            $news->image = Storage::url($path);
            $news->link = env('APP_URL') . Storage::url($path);

            $this->optimizeImage($news->image, 'news');

            $news->save();
            $changed = true;

        } else {
            $changed = false;
        }

        return $changed;
    }

    /**
     * Удаление новости
     *
     * @param int $newsId
     * @return bool
     */
    public function deleteNews(int $newsId)
    {
        $newsImages = NewsImage::where('news_id', $newsId)->get();
        if (!is_null($newsImages)){
            foreach ($newsImages as $newsImage){
                Storage::delete('news/' . basename($newsImage->image));
                $newsImage->delete();
            }

        }
        $news = News::find($newsId);
        foreach ($news->files as $file){
            Storage::delete('files/' . basename($file->file));
            $file->delete();
        }
        foreach ($news->storageFiles as $file){
            Storage::delete('files/' . basename($file->file));
            $file->delete();
        }
        if (!is_null($news)){
            Storage::delete('news/' . basename($news->image));
            $news->seo()->delete();
            $news->delete();
            $deleted = true;
        } else {

            $deleted = false;
        }

        return $deleted;
    }

    /**
     * Получение новости
     *
     * @param int|null $newsId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getNews(?int $newsId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            $nowDate = (new \DateTime('now'))->format('Y-m-d');
            $news = News::where('alias', $alias)->where('is_visible', true)->where('published_at', '<=', $nowDate);
            switch ($locale){
                case 'ru' :
                    $news->select([
                        'id',  'title_ru as title', 'description_ru as description',
                        'link', 'image', 'alias', 'published_at'
                    ]);
                    $news->with(
                        'seo:id,news_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'images:id,news_id,image,description'
                    );
                    break;

                case 'en' :
                   $news->select([
                       'id', 'title_en as title', 'description_en as description',
                       'link', 'image', 'alias', 'published_at'
                   ]);
                   $news->with(
                       'seo:id,news_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                       'images:id,news_id,image,description'
                   );
                   break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $news = $this->getNewsLocaleFilter($news, $locale);
        } else {
            $news = News::where('id', $newsId);
            $news->with('seo', 'images');
        }
        $news = $news->first();
        if (is_null($news)) throw new ApiProblemException('Новость не найдена', 404);

        return $news;
    }

    /**
     * Получение публичной новости
     *
     * @param string $locale
     * @param string $alias
     * @return object
     * @throws ApiProblemException
     */
    public function getPublicNews(string $locale, string $alias)
    {
        $news = $this->getNews(null, $locale, $alias);
        $previous = $this->getPreviousNews($locale, $alias);
        $next = $this->getNextNews($locale, $alias);
        $neighbors = [
                'next' => $next,
                'previous' => $previous
        ];
        $news->neighbors = $neighbors;

        return $news;
    }

    /**
     * Предыдущая по публикации новость
     *
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getPreviousNews(?string $locale = null, ?string $alias = null)
    {
        $nowDate = (new \DateTime('now'))->format('Y-m-d');
        $news = News::where('alias', $alias)->where('is_visible', true)->where('published_at', '<=', $nowDate)->first();
        if (is_null($news)) throw new ApiProblemException('Новость не найдена', 404);
        $previousNews = News::where('is_visible', true)
            //->where('id', '<>', $news->id)
            ->whereNotNull('alias')
            ->where('published_at', '<', $news->published_at)
            ->orderBy('published_at', 'desc')
            ->first(['alias']);
        if (is_null($previousNews)) return null;

        return $this->getNews(null, $locale, $previousNews->alias);
    }

    /**
     * Следующая по публикации новость
     *
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getNextNews(?string $locale = null, ?string $alias = null)
    {
        $nowDate = (new \DateTime('now'))->format('Y-m-d');
        $news = News::where('alias', $alias)->where('is_visible', true)->where('published_at', '<=', $nowDate)->first();
        if (is_null($news)) throw new ApiProblemException('Новость не найдена', 404);

        $nextNews = News::where('is_visible', true)
            ->whereNotNull('alias')
            //->where('id', '<>', $news->id)
            ->where('published_at', '>', $news->published_at)
            ->where('published_at', '<', $nowDate)
            ->orderBy('published_at', 'asc')
            ->first(['alias']);
        if (is_null($nextNews)) return null;

        return $this->getNews(null, $locale, $nextNews->alias);
    }

    /**
     * Получение всех избражений новости
     *
     * @param int $newsId
     * @return mixed
     */
    public function getImages(int $newsId)
    {
        return NewsImage::where('news_id', $newsId)->get();
    }

    /**
     * Добавление файла
     *
     * @param Request $request
     * @param int $newsId
     * @return StorageFile|null
     */
    public function addFile(Request $request, int $newsId)
    {
        if ($request->hasFile('file')){
            $path = $request->file('file')->store('files');
            $file = new StorageFile();
            $file->news_id = $newsId;
            $file->file = Storage::url($path);
            $file->url = env('APP_URL') . Storage::url($path);
            $file->link = env('APP_URL') . Storage::url($path);
            $file->description = $request->get('description');
            $file->save();

            return $file;
        } else return null;
    }

    /**
     * Получение файлов
     *
     * @param int $newsId
     * @return mixed
     */
    public function getFiles(int $newsId)
    {
        return News::find($newsId)->files;
    }

    /**
     * Удаление файла
     *
     * @param int $fileId
     * @throws NotFoundException
     */
    public function deleteFile(int $fileId)
    {
        $file = StorageFile::find($fileId);
        if (is_null($file)) throw new NotFoundException();
        Storage::delete('files/' . basename($file->file));
        $file->delete();
    }
}
