<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Mood;
use App\Models\ObjectMood;
use App\Models\ObjectPlace;
use Illuminate\Http\Request;

class ObjectMoodService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ObjectMoodService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Добавление mood-тега
     *
     * @param Request $request
     * @return mixed
     * @throws ApiProblemException
     */
    public function add(Request $request)
    {
        $objectId = $request->get('object_id');
        $moodId = $request->get('mood_id');

        $object = ObjectPlace::find($objectId);
        if (!$object) {
            throw new ApiProblemException('Объект не найден', 422);
        }

        $mood = Mood::find($moodId);
        if (!$mood) {
            throw new ApiProblemException('Mood-тег не найден', 422);
        }

        $filter = [];
        $filter[] = ['mood_id', $moodId];
        $filter[] = ['object_id', $objectId];

        $objectMood = ObjectMood::where($filter)->first();

        if ($objectMood) {
            throw new ApiProblemException('Данный mood-тег уже добавлен этому объекту', 422);
        }

        $newObjectMood = new ObjectMood();
        $newObjectMood->object_id = $objectId;
        $newObjectMood->mood_id = $moodId;

        return $newObjectMood->save();
    }

    /**
     * Удаление mood-тега
     *
     * @param Request $request
     * @throws ApiProblemException
     * @return bool
     */
    public function delete(Request $request)
    {
        $objectId = $request->get('object_id');
        $moodId = $request->get('mood_id');

        $object = ObjectPlace::find($objectId);
        if (!$object) {
            throw new ApiProblemException('Объект не найден', 422);
        }

        $mood = Mood::find($moodId);
        if (!$mood) {
            throw new ApiProblemException('Mood-тег не найден', 422);
        }


        $filter = [];
        $filter[] = ['mood_id', $moodId];
        $filter[] = ['object_id', $objectId];

        $objectMood = ObjectMood::where($filter)->first();

        if ($objectMood) {
            return $objectMood->delete();
        } else {
            return false;
        }
    }

    /**
     * Поиск Mood-тегов по id объекта
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @return array
     */
    public function getMoods(int $page, int $rowsPerPage, int $objectId, ?array $sorting = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $qb = ObjectMood::where('object_id', $objectId)
            ->select('mood_id');

        $total = $qb->count();

        $ids = $qb->get()->pluck('mood_id');
        $moods = Mood::when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $orderBy = $query->orderBy($key, $value);
                }
                return $orderBy;
            } else {
                return $query->orderBy('id', 'desc');
            }
        })
            ->whereIn('id', $ids);

        $items = $moods->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

}
