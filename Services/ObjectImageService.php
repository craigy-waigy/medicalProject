<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\ModerationStatus;
use App\Models\ObjectImage;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use App\Traits\ImageTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ObjectImageService
{
    use ImageTrait;

    /**
     * Добавление изображения
     *
     * @param $objectId
     * @param $fromAdmin
     * @param Request $request
     * @return mixed
     * @throws ApiProblemException
     */
    public function create(Request $request, ?int $objectId = null, bool $fromAdmin = false )
    {
        $description = $request->get('description') ?? null;
        if (is_null($objectId)){
            $objectId = (int)$request->get('object_id');
        }

         if ($request->hasFile('image')){
             $path = $request->file('image')->store('object_gallery');

             if ($fromAdmin){
                 $moderationStatus = ModerationStatus::MODERATE_OK;
             } else {
                 $moderationStatus = ModerationStatus::ON_MODERATE;
             }
             $newImage = new ObjectImage();
             $newImage->object_id = $objectId;
             $newImage->image = Storage::url($path ?? null);
             $newImage->description = $description;
             $newImage->moderation_status = $moderationStatus;
             $newImage->sorting_rule = null;
             $newImage->is_main = false;
             $newImage->moderator_message = null;

             $this->optimizeImage($newImage->image, 'object_gallery', 1440, 810);

             $newImage->thumbs = $this->generateThumbs($newImage->image, 'object_gallery', 450, 450);
             $newImage->small = $this->generateThumbs($newImage->image, 'object_gallery', 76, 76);

             $newImage->save();

             return $this->getImage($newImage->id);
         } else return null;
    }

    /**
     * Добавление изображения по ссылке
     *
     * @param $objectId
     * @param $fromAdmin
     * @param Request $request
     * @return mixed
     * @throws ApiProblemException
     */
    public function createByLink(Request $request, ?int $objectId = null, bool $fromAdmin = false )
    {
        $description = $request->get('description') ?? null;
        if (is_null($objectId)){
            $objectId = (int)$request->get('object_id');
        }

        if ($request->get('link_to_file')){

            $linkToFile = $request->get('link_to_file');
            $fileContent = file_get_contents($linkToFile);
            $fileName = uniqid() . '_' . basename($linkToFile);
            $path = "object_gallery/$fileName";

            Storage::put($path, $fileContent);

            if ($fromAdmin){
                $moderationStatus = ModerationStatus::MODERATE_OK;
            } else {
                $moderationStatus = ModerationStatus::ON_MODERATE;
            }
            $newImage = new ObjectImage();
            $newImage->object_id = $objectId;
            $newImage->image = Storage::url($path ?? null);
            $newImage->description = $description;
            $newImage->moderation_status = $moderationStatus;
            $newImage->sorting_rule = null;
            $newImage->is_main = false;
            $newImage->moderator_message = null;

            $this->optimizeImage($newImage->image, 'object_gallery', 1440, 810);

            $newImage->thumbs = $this->generateThumbs($newImage->image, 'object_gallery', 450, 450);
            $newImage->small = $this->generateThumbs($newImage->image, 'object_gallery', 76, 76);

            $newImage->save();

            return $this->getImage($newImage->id);
        } else return null;
    }

    /**
     * Обновление данных изображения
     *
     * @param array $data
     * @param int $id
     * @return mixed
     * @throws ApiProblemException
     */
    public function update(array $data, int $id)
    {
        $image = ObjectImage::where([
            ['id', $id],
            ['object_id', $data['object_id']],
        ])->first();
        if (is_null($image)) throw new ApiProblemException('Изображение не найдено', 404);

        foreach ($data as $field=>$value){

            if ($field == 'moderation'){
                if (!is_array($value)) $value = json_decode($value, true);
                $this->moderate($value, $id);
            } else {
                $image->$field = $value;
            }
        }
        $image->save();

        return $this->getImage($image->id);
    }

    /**
     * Удаление изображения
     *
     * @param int $imageId
     * @param int|null $objectId
     * @return bool
     */
    public function delete(int $imageId, ?int $objectId = null)
    {
        $filter = [];
        $filter[] = ['id', $imageId];
        if ($objectId != null) $filter[] = ['object_id', $objectId];

        $objectImage = ObjectImage::where($filter)->first();
        $imageCount = ObjectImage::where($filter)->count();
        if ($imageCount == 0) {
            return false;
        } else {
            Storage::delete('object_gallery/' . basename($objectImage->image));
            ObjectImage::where($filter)->delete();

            return true;
        }
    }

    /**
     * Сортировка изображений
     *
     * @param array $imageIds
     * @param $objectId
     */
    public function sorting(array $imageIds, $objectId)
    {
        $sortingRule = 0;
        foreach ($imageIds as $imageId){

            ObjectImage::where([
                ['id', $imageId],
                ['object_id', $objectId],

            ])->update([
               'sorting_rule' => $sortingRule
            ]);
            $sortingRule++;
        }
    }

    /**
     * Установление главного изображения
     *
     * @param int $objectId
     * @param int $imageId
     */
    public function setMain(int $objectId, int $imageId)
    {
        ObjectImage::where('object_id', $objectId)->update([
            'is_main' => false
        ]);

        ObjectImage::where([
            ['id', $imageId],
            ['object_id', $objectId],
        ])->update([
            'is_main' => true
        ]);
    }

    /**
     * Получение изображений объекта
     *
     * @param int $objectId
     * @return array
     */
    public function getImages(int $objectId)
    {
        $images = DB::table('object_images')->where('object_id', $objectId)
            ->orderBy('sorting_rule', 'asc')
            ->select(['id', 'moderation_status', 'sorting_rule', 'description', 'is_main', 'image', 'moderator_message'])
            ->get();
        $resultImages = [];
        foreach ($images as $image){
            $moderation = [
                'status_id' => $image->moderation_status,
                'message' => $image->moderator_message,
            ];
            unset($image->moderation_status);
            unset($image->moderator_message);
            $image->moderation = (object)$moderation;
            $resultImages[] = $image;
        }

        return $resultImages;
    }

    /**
     * Получение изображения
     *
     * @param int $imageId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getImage(int $imageId)
    {
        $image = ObjectImage::where('id', $imageId)
            ->orderBy('sorting_rule', 'asc')
            ->select(['id', 'moderation_status', 'sorting_rule', 'description', 'is_main', 'image', 'moderator_message'])
            ->first();
        if (is_null($image)) throw new ApiProblemException('Изображение не найено', 404);

        $moderation = [
            'status_id' => $image->moderation_status,
            'message' => $image->moderator_message,
        ];
        unset($image->moderation_status);
        unset($image->moderator_message);
        $image->moderation = (object)$moderation;

        return $image;
    }

    /**
     * Модерация изображений
     *
     * @param array $moderateData
     * @param $imageId
     * @throws ApiProblemException
     */
    public function moderate(array $moderateData, $imageId)
    {
        $image = ObjectImage::find($imageId);
        if (is_null($image))
            throw new ApiProblemException('Изображение не найдено', 404);

        if (isset($moderateData['approve'])){
            if ($moderateData['approve']){
                $image->moderation_status = ModerationStatus::MODERATE_OK;
                $image->moderator_message = null;
                $user = $image->object->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationAcceptNotification( "Изображение публикации") );
                }
            } else {
                if (empty($moderateData['message']))
                    throw new ApiProblemException('Необходим опредоставить сообщение о причине отказа', 422);

                $image->moderation_status = ModerationStatus::MODERATE_REJECT;
                $image->moderator_message = $moderateData['message'];

                $user = $image->object->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationRejectNotification( "Изображение публикации", $moderateData['message']) );
                }

            }
            $image->save();
        } else {
            throw new ApiProblemException('Не верный формат модерации', 422);
        }
    }
}
