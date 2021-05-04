<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ShowcaseRoom;
use App\Models\ShowcaseRoomImage;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ShowcaseRoomService
{
    use ImageTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ShowcaseRoomService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Создание витрины
     *
     * @param array $data
     * @param int $objectId
     * @param Request $request
     * @return ShowcaseRoom
     */
    public function createRoom(array $data, int $objectId, Request $request)
    {
        $newRoom = new ShowcaseRoom();
        $newRoom->object_id = $objectId;
        $newRoom->title_ru = $data['title_ru'];
        $newRoom->title_en = $data['title_en'];
        $newRoom->description_ru = $data['description_ru'] ?? '';
        $newRoom->description_en = $data['description_en'] ?? '';
        $newRoom->capacity = $data['capacity'] ?? 0;
        $newRoom->capacity_min = $data['capacity_min'] ?? 0;
        $newRoom->capacity_max = $data['capacity_max'] ?? 0;
        $newRoom->square = $data['square'] ?? null;
        $newRoom->interior_ru = $data['interior_ru'] ?? '';
        $newRoom->interior_en = $data['interior_en'] ?? '';
        $newRoom->active = $data['active'] ?? false;
        $newRoom->price = $data['price'] ?? null;

        if ($request->hasFile('image')){
            $path = $request->file('image')->store('showcase_room');
            $newRoom->image = Storage::url($path);
            $this->optimizeImage($newRoom->image, 'showcase_room', 450, 450);
        }

        $newRoom->save();

        return $newRoom;
    }

    /**
     * Обновление витрины
     *
     * @param array $data
     * @param int $roomId
     * @param Request $request
     * @return mixed
     * @throws ApiProblemException
     */
    public function updateRoom(array $data, int $roomId, Request $request)
    {
        $room = ShowcaseRoom::find($roomId);
        if (is_null($room))
            throw new ApiProblemException('Витрина не найдена', 404);

        foreach ($data as $field=>$value){
            $room->$field = $value;
        }

        if ($request->hasFile('image')){

            Storage::delete('showcase_room/' . basename($room->image));

            $path = $request->file('image')->store('showcase_room');
            $room->image = Storage::url($path);
            $this->optimizeImage($room->image, 'showcase_room', 450, 450);
        }
        $room->save();

        return $room;
    }

    /**
     * Удаление витрины
     *
     * @param int $roomId
     * @throws ApiProblemException
     */
    public function deleteRoom(int $roomId)
    {
        $room = ShowcaseRoom::find($roomId);

        if (is_null($room))
            throw new ApiProblemException('Витрина не найдена', 404);

        foreach ($room->images as $image){
            Storage::delete('showcase_room/' . basename($image->image));
            $image->delete();
        }
        Storage::delete('showcase_room/' . basename($room->image));
        $room->delete();
    }

    /**
     * Добавление изображения номера
     *
     * @param Request $request
     * @param int $roomId
     * @return ShowcaseRoomImage
     */
    public function addImage(Request $request, int $roomId)
    {
        $newRoomImage = new ShowcaseRoomImage();
        $newRoomImage->showcase_room_id = $roomId;

        if ($request->hasFile('image')){
            $path = $request->file('image')->store('showcase_room');
            $newRoomImage->image = Storage::url($path);
            $newRoomImage->description = $request->get('description') ?? null;

            $this->optimizeImage($newRoomImage->image, 'showcase_room', 1440, 810);

            $newRoomImage->thumbs = $this->generateThumbs($newRoomImage->image, 'showcase_room', 450, 450);
        }

        $newRoomImage->save();

        return $newRoomImage;
    }

    /**
     * Удаление изображения номера
     *
     * @param int $roomId
     * @param int $imageId
     * @return bool
     */
    public function deleteImage(int $roomId, int $imageId)
    {
        $showcaseImage = ShowcaseRoomImage::where([
            ['id', $imageId],
            ['showcase_room_id', $roomId],
        ])->first();

        if  (!is_null($showcaseImage)){

            Storage::delete('showcase_room/' . basename($showcaseImage->image));
            Storage::delete('showcase_room/' . basename($showcaseImage->thumbs));
            $showcaseImage->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Получение номера
     *
     * @param int $roomId
     * @return mixed
     */
    public function getRoom(int $roomId)
    {
        return ShowcaseRoom::find($roomId);
    }

    /**
     * Поиск номеров
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param array $filter
     * @return array
     */
    public function searchRoom(int $page, int $rowsPerPage, ?string $searchKey, array $filter, ?array $sorting = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = ShowcaseRoom::when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $orderBy = $query->orderBy($key, $value);
                }
                return $orderBy;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });
        $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey)) {

                    $query = $query->orWhere('title_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('title_en', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('description_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('description_en', 'like', "%{$searchKey}%");

                    return $query;
                }
        });
        if (!empty($filter['object_id'])){
            $qb->where('object_id', $filter['object_id']);
        }
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение списка изображений
     *
     * @param int $roomId
     * @return mixed
     */
    public function getImages(int $roomId)
    {
        return ShowcaseRoomImage::where('showcase_room_id', $roomId)->orderBy('sorting_rule', 'asc')->get();
    }

    /**
     * Сортировка изображений
     *
     * @param array $imageIds
     * @param $roomId
     */
    public function sorting(array $imageIds, $roomId)
    {
        $sortingRule = 0;
        foreach ($imageIds as $imageId){

            ShowcaseRoomImage::where([
                ['id', $imageId],
                ['showcase_room_id', $roomId],

            ])->update([
                'sorting_rule' => $sortingRule
            ]);
            $sortingRule++;
        }
    }
}
