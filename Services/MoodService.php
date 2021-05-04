<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\Mood;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class MoodService
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
     * Добавление Mood-тегов
     *
     * @param Request $request
     * @return Mood
     */
    public function create(Request $request)
    {
        $mood = new Mood();

        if ($request->hasFile('image')){
            $path = $request->file('image')->store('moods');
            $cropPath = $request->file('image')->store('moods_crop');
            $mood->image = Storage::url($path);
            $mood->crop_image = Storage::url($cropPath);

            $absCropPath = storage_path('app' . DIRECTORY_SEPARATOR . 'moods_crop') . DIRECTORY_SEPARATOR  . basename($cropPath);
            Image::make($absCropPath)->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($absCropPath);
        }

        $data = $request->only('name_ru', 'name_en', 'alias');
        foreach ($data as $field=>$value){
            if (!is_null($value))
                $mood->$field = $value;
        }

        $mood->save();

        return $mood;
    }

    /**
     * Редактирование Mood-тега
     *
     * @param Request $request
     * @param int $moodId
     * @return mixed
     * @throws ApiProblemException
     */
    public function update(Request $request, int $moodId)
    {
        $mood = Mood::find($moodId);
        if (is_null($mood)) throw new ApiProblemException('Mood-тег не найден', 404);

        if ($request->hasFile('image')){
            Storage::delete('moods/' . basename($mood->image));
            Storage::delete('moods_crop/' . basename($mood->crop_image));
            $path = $request->file('image')->store('moods');
            $cropPath = $request->file('image')->store('moods_crop');
            $mood->image = Storage::url($path);
            $mood->crop_image = Storage::url($cropPath);

            $absCropPath = storage_path('app' . DIRECTORY_SEPARATOR . 'moods_crop') . DIRECTORY_SEPARATOR  . basename($cropPath);
            Image::make($absCropPath)->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
            })->save($absCropPath);
        }

        $data = $request->only('name_ru', 'name_en', 'alias');
        foreach ($data as $field=>$value){
            if (!is_null($value))
                $mood->$field = $value;
        }

        $mood->save();

        return $mood;
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
                })->select(['id', 'name_ru', 'name_en', 'alias', 'image', 'crop_image']);
                break;

            case 'en' :
                $qb->when($searchKey, function ($query, $searchKey){
                    if (!is_null($searchKey)){
                        $query = $query->whereRaw("lower(name_en) LIKE '%{$searchKey}%'");

                        return $query;
                    }
                })->select(['id', 'name_ru', 'name_en', 'alias', 'image', 'crop_image']);
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль', 422);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение mood-тега
     *
     * @param int $moodId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $moodId)
    {
        $mood = Mood::find($moodId);
        if (is_null($mood)) throw new ApiProblemException('Mood-тег не найден', 404);

        return $mood;
    }

    /**
     * Удаление mood-тега
     *
     * @param int $moodId
     * @throws ApiProblemException
     */
    public function delete(int $moodId)
    {
        $mood = Mood::find($moodId);
        if (is_null($mood)) throw new ApiProblemException('Mood-тег не найден', 404);

        Storage::delete('moods/' . basename($mood->image));
        Storage::delete('moods_crop/' . basename($mood->crop_image));

        return $mood->delete();
    }

    /**
     * Удаление изображения mood-тега
     *
     * @param int $moodId
     * @throws ApiProblemException
     */
    public function deleteImage(int $moodId)
    {
        $mood = Mood::find($moodId);
        if (is_null($mood)) throw new ApiProblemException('Mood-тег не найден', 404);

        if ($mood->image || $mood->crop_image) {
            Storage::delete('moods/' . basename($mood->image));
            Storage::delete('moods_crop/' . basename($mood->crop_image));
            $mood->image = null;
            $mood->crop_image = null;
        }

        return $mood->save();
    }

}
