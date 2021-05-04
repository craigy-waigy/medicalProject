<?php

namespace App\Services;

use App\Models\ObjectPlace;
use App\Libraries\Models\PaginatorFormat;
use App\Models\AwardIcon;
use App\Models\ObjectAwardIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AwardService
{
    protected $paginatorFormat;

    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск наград
     *
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param null|string $searchKey
     * @return array
     */
    public function searchAward(?int $page, ?int $rowsPerPage, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);

        $qb = AwardIcon::
        when($searchKey, function ($query, $searchKey){
            if (!is_null($searchKey)){
                $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                $query = $query->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'");

                return $query;
            }
        });

        $total = $qb->count();

        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Добавление награды
     *
     * @param Request $request
     * @return AwardIcon
     */
    public function addAward(Request $request)
    {
        $newAward = new AwardIcon();

        $newAward->title_ru = $request->get('title_ru');
        $newAward->title_en = $request->get('title_en');
        $newAward->description_ru = $request->get('description_ru') ?? null;
        $newAward->description_en = $request->get('description_en') ?? null;
        $newAward->active = $request->get('active') ?? false;
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('award_icons');
            $newAward->image = Storage::url($path);
        }
        $newAward->save();

        return $newAward;
    }

    /**
     * Редактирование награды
     *
     * @param Request $request
     * @param int $awardId
     * @return bool
     */
    public function editAward(Request $request, int $awardId)
    {
        $award = AwardIcon::find($awardId);
        if (!is_null($award)){
            $data = $request->only('title_ru', 'title_en', 'description_ru', 'description_en', 'active');
            foreach ($data as $field=>$value){
                $award->$field = $value;
            }
            if ($request->hasFile('image')){
                Storage::delete('award_icons/' . basename($award->image));
                $path = $request->file('image')->store('award_icons');
                $award->image = Storage::url($path);
            }
            $award->save();

            return true;
        } else return false;
    }

    /**
     * Получение награды
     *
     * @param int $awardId
     * @return mixed
     */
    public function getAward(int $awardId)
    {
        return AwardIcon::find($awardId);
    }

    /**
     * Удаление награды
     *
     * @param int $awardId
     * @return bool
     */
    public function deleteAward(int $awardId)
    {
        $award = AwardIcon::find($awardId);
        if (!is_null($award)){

            Storage::delete('award_icons/' . basename($award->image));
            $award->delete();

            return true;
        } else return false;
    }


    /**
     * Присвоение награды объекту
     *
     * @param int $objectId
     * @param int $awardId
     * @return array
     */
    public function setAward(int $objectId, int $awardId)
    {
        $object = ObjectPlace::find($objectId);
        $award = AwardIcon::find($awardId);
        if (is_null($object)){


            return ['message' => ['award' => ['Объект не найден']], 'status' => 404];
        } elseif (is_null($award)){

            return ['message' => ['award' => ['Награда не найдена']], 'status' => 404];
        } else {
            if ($object->awardIcons()->find($awardId) == null){
                $object->awardIcons()->attach($award);
            }

            return ['message' => ['award' => ['Награда присвоена']], 'status' => 200];
        }
    }

    /**
     * Отзыв награды
     *
     * @param int $objectId
     * @param int $awardId
     * @return array
     */
    public function revokeAward(int $objectId, int $awardId)
    {
        $object = ObjectPlace::find($objectId);
        $award = AwardIcon::find($awardId);
        if (is_null($object)){

            return ['message' => ['award' => ['Объект не найден']], 'status' => 404];
        } elseif (is_null($award)){

            return ['message' => ['award' => ['Награда не найдена']], 'status' => 404];
        } else {

            ObjectAwardIcon::where([
                ['object_id', $objectId],
                ['award_icon_id', $awardId],
            ])->delete();

            return ['message' => ['award' => ['Награда отозвана']], 'status' => 200];
        }
    }
}
