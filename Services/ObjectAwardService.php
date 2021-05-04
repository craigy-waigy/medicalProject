<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ObjectAward;
use App\Models\ObjectPlace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ObjectAwardService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ObjectService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Сохранение награды
     *
     * @param Request $request
     * @param int $objectId
     * @return mixed
     */
    public function create(Request $request, int $objectId)
    {
        $description = $request->get('description') ?? null;

        if ($request->hasFile('image')){
            $path = $request->file('image')->store('awards');

            $newAward = new ObjectAward();
            $newAward->object_id = $objectId;
            $newAward->image = Storage::url($path);
            $newAward->description = $description;
            $newAward->save();

            return $newAward;
        }
    }

    /**
     * Изменение описания награды
     *
     * @param array $data
     * @param int $awardId
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function update(array $data, int $awardId, int $objectId)
    {
        $award = ObjectAward::where([
            ['id', $awardId],
            ['object_id', $objectId],
        ])->first();

        if (is_null($award)) throw new ApiProblemException('Награда не найдена', 404);
        foreach ($data as $field => $value){
            $award->$field = $value;
        }
        $award->save();

        return $award;
    }

    /**
     * Получение всех наград
     *
     * @param int $objectId
     * @return mixed
     */
    public function get(int $objectId)
    {
        return ObjectAward::where('object_id', $objectId)
            ->select(['id', 'image', 'description'])
            ->get();
    }

    /**
     *  Удаление награды
     *
     * @param int $awardId
     * @param int $objectId
     * @param bool $fromAdmin
     * @throws ApiProblemException
     */
    public function delete(int $awardId,int $objectId, bool $fromAdmin = false)
    {
        $awardImage = ObjectAward::where([
            ['id', $awardId],
            ['object_id', $objectId]
        ])->first();
        if (is_null($awardImage)) throw new ApiProblemException('Награда не найдена', 404);

        Storage::delete('awards/' . basename($awardImage->image));
        $awardImage->delete();

    }

    /**
     *
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @return array
     */
    public function getNewObjectAwards(int $page, int $rowsPerPage, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $awards = ObjectAward::where('is_new', true);
        if (!is_null($searchKey)){
            $searchKey = mb_strtolower($searchKey);
            $objects = ObjectPlace::
            whereRaw("lower(title_ru) LIKE '%{$searchKey}%'")
            ->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'")
            ->get();
            $objectIds = [];
            foreach ($objects as $object){
                $objectIds[] = $object->id;
            }
            $awards->whereIn('object_id', $objectIds);
        }

        $total = $awards->count();
        $items = $awards->skip($skip)->take($rowsPerPage)
            ->with('object:id,title_ru')
            ->orderBy('updated_at', 'desc')
            ->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     *
     *
     * @param int $objectAwardId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getObjectAward(int $objectAwardId)
    {
        $award = ObjectAward::where('id', $objectAwardId)->with('object:id,title_ru')->first();
        if (is_null($award)) throw new ApiProblemException('Награда не найдена', 404);

        return $award;
    }
}
