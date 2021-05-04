<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\ProviderShowcaseRoom;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;

class ProviderShowcaseRoomService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ProviderShowcaseRoomService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Добавление ProviderShowcaseRoom связи
     *
     * @param Request $request
     * @return ProviderShowcaseRoom
     */
    public function create(Request $request)
    {
        $pscr = new ProviderShowcaseRoom();


        $data = $request->only('showcase_room_id', 'provider_id', 'provider_room_id');
        foreach ($data as $field=>$value){
            if (!is_null($value))
                $pscr->$field = $value;
        }

        $pscr->save();

        return $pscr;
    }

    /**
     * Редактирование ProviderShowcaseRoom связи
     *
     * @param Request $request
     * @param int $id
     * @return mixed
     * @throws ApiProblemException
     */
    public function update(Request $request, int $id)
    {
        $pscr = ProviderShowcaseRoom::find($id);

        if (is_null($pscr)) throw new ApiProblemException('Связь не найдена', 404);

        $data = $request->only('showcase_room_id', 'provider_id', 'provider_room_id');
        foreach ($data as $field=>$value){
            if (!is_null($value))
                $pscr->$field = $value;
        }

        $pscr->save();

        return $pscr;
    }

    /**
     * Получение ProviderShowcaseRoom связи
     *
     * @param int $showcaseRoomId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $showcaseRoomId)
    {
        $pscr = ProviderShowcaseRoom::where('showcase_room_id', $showcaseRoomId)->get();

        if (is_null($pscr)) throw new ApiProblemException('Связь не найдена', 404);

        return $pscr;
    }

    /**
     * Удаление ProviderShowcaseRoom связи
     *
     * @param int $id
     * @return mixed
     * @throws ApiProblemException
     */
    public function delete(int $id)
    {
        $pscr = ProviderShowcaseRoom::find($id);
        if (is_null($pscr)) throw new ApiProblemException('Mood-тег не найден', 404);

        return $pscr->delete();
    }

}
