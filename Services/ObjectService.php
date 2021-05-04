<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\AwardIcon;
use App\Models\MedicalProfile;
use App\Models\ModerationObject;
use App\Models\ModerationObjectMedicalProfile;
use App\Models\ModerationObjectsService;
use App\Models\ModerationObjectTherapy;
use App\Models\ModerationStatus;
use App\Models\Mood;
use App\Models\ObjectAwardIcon;
use App\Models\ObjectMood;
use App\Models\ObjectPlace;
use App\Models\ObjectFoodAndSport;
use App\Models\ObjectInfrastructure;
use App\Models\ObjectMedicalInformation;
use App\Models\ObjectMedicalProfile;
use App\Models\ObjectRoom;
use App\Models\ObjectRoomImage;
use App\Models\ObjectTherapy;
use App\Models\Service;
use App\Models\Therapy;
use App\Traits\ImageTrait;
use App\Traits\WorksheetFileTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ObjectService extends Model
{
    use ImageTrait;
    use WorksheetFileTrait;
    use SoftDeletes;

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
        $this->medicalProfileService = new MedicalProfileService();
    }

    /**
     * Получение и поиск объектов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @return array
     */
    public function getAll(int $page, int $rowsPerPage,?array $sorting, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $filter = [];
        $filter[] = ['is_deleted', false];

        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $queryBuilder = ObjectPlace::where($filter)
            ->when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $orderBy = $query->orderBy($key, $value);
                    }
                    return $orderBy;
                } else {
                    return $query->orderBy('id', 'asc');
                }

            })
            ->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey) ){
                    $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'");
                    return $query;
                }
            });

        $total = $queryBuilder->count();
        $items = $queryBuilder->skip($skip)->take($rowsPerPage)
            ->select(['id', 'country_id', 'region_id', 'city_id', 'title_ru', 'alias', 'is_visibly',
                'created_at', 'modified_at', 'has_worksheet', 'expensive', 'heating_rating', 'full_rating', 'viewing_count', 'has_default_diseases_only'])
            ->withCount('showcaseRooms')
            ->with(
                'country:id,name_ru',
                'region:id,name_ru',
                'city:id,name_ru'
            )
            ->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Создание нового объекта
     *
     * @param array $object
     * @return ObjectPlace
     * @throws ApiProblemException
     */
    public function createObject(array $object)
    {
        $newObject = new ObjectPlace();
        $newObject->title_ru = $object['title_ru'] ?? null;
        $newObject->title_en = $object['title_en'] ?? null;
        $newObject->is_visibly = $object['is_visibly'] ?? false;
        $newObject->country_id = $object['country_id'] ?? null;
        $newObject->region_id = $object['region_id'] ?? null;
        $newObject->city_id = $object['city_id'] ?? null;
        $newObject->lat = $object['lat'] ?? null;
        $newObject->lon = $object['lon'] ?? null;
        $newObject->address = $object['address'] ?? null;
        $newObject->zip = $object['zip'] ?? null;
        $newObject->description_ru = $object['description_ru'] ?? null;
        $newObject->description_en = $object['description_en'] ?? null;
        $newObject->documents_ru = $object['documents_ru'] ?? null;
        $newObject->documents_en = $object['documents_en'] ?? null;
        $newObject->visa_information_ru = $object['visa_information_ru'] ?? null;
        $newObject->visa_information_en = $object['visa_information_en'] ?? null;
        $newObject->contraindications_ru = $object['contraindications_ru'] ?? null;
        $newObject->contraindications_en = $object['contraindications_en'] ?? null;
        $newObject->capabilities = $object['capabilities'] ?? null;
        $newObject->street_view_link = $object['street_view_link'] ?? null;
        $newObject->save();

        DB::table('object_medical_informations')->insert(['object_id' => $newObject->id]);
        DB::table('object_food_and_sports')->insert(['object_id' => $newObject->id]);
        DB::table('object_infrastructures')->insert(['object_id' => $newObject->id]);
        DB::table('object_rooms')->insert(['object_id' => $newObject->id]);

        $this->AddAllMedicalProfilesAndInitByDefaultDiseases($newObject->id);

        return $this->getObject($newObject->id);
    }

    /**
     *  Добавим объекту все активные медпрофили и заполним дефолтными заболеваниями
     *
     * @param int {
     * @throws ApiProblemException
     */
    public function AddAllMedicalProfilesAndInitByDefaultDiseases(int $objectId)
    {
        $object = ObjectPlace::find($objectId);
        if ( is_null($object) )
            throw new ApiProblemException('Объект не найден', 404);

        if ($object->medicalProfiles()->count() > 0){
            ObjectMedicalProfile::where('object_id', $objectId)->delete();
        }

        $medicalProfiles = MedicalProfile::where('active', true)->get();

        foreach ($medicalProfiles as $medicalProfile) {
            $object->medicalProfiles()->attach($medicalProfile);
        }

        $this->medicalProfileService->resetAllMedicalProfilesToDefaultDiseases($objectId);

    }

    /**
     *  Обновление информации по объекту
     *
     * @param array $data
     * @param int $id
     * @throws ApiProblemException
     */
    public function updateObject(array $data, int $id)
    {
        $object = ObjectPlace::find($id);

        if ( is_null($object) )
            throw new ApiProblemException('Объект не найден', 404);

        foreach($data as $key => $value) {

            if ($key == 'services'){

                if ($object->services()->count() > 0){
                    ObjectService::where('object_id', $id)->delete();
                }
                foreach ($data['services'] as $serviceId){
                    $object->services()->attach(Service::find($serviceId));
                }

            } elseif ($key == 'medical_profiles'){

                if ($object->medicalProfiles()->count() > 0){
                    ObjectMedicalProfile::where('object_id', $id)->delete();
                }
                foreach ($data['medical_profiles'] as $medicalProfileId){
                    $object->medicalProfiles()->attach(MedicalProfile::find($medicalProfileId));
                    $this->medicalProfileService->checkMedicalProfileHasDefaultDiseasesOnly($medicalProfileId, $id);
                }

            } elseif ($key == 'therapies'){

                if ($object->therapies()->count() > 0){
                    ObjectTherapy::where('object_id', $id)->delete();
                }
                foreach ($data['therapies'] as $therapyId){
                    $object->therapies()->attach(Therapy::find($therapyId));
                }

            } elseif ($key == 'award_icons'){

                ObjectAwardIcon::where('object_id', $id)->delete();
                foreach ($data['award_icons'] as $awardId){

                    $object->awardIcons()->attach(AwardIcon::find($awardId));
                }

            } elseif ($key == 'moods'){

                ObjectMood::where('object_id', $id)->delete();
                foreach ($data['moods'] as $moodId){

                    $object->moods()->attach(Mood::find($moodId));
                }

            } elseif ( is_array($value) ){
                $object->$key = $value; //Не трогать, так надо

            } elseif ( is_array( json_decode($value, true) ) ){
                $object->$key = json_decode($value);
            }else {

                $object->$key = $value;
            }
        }

        $this->medicalProfileService->checkObjectHasDefaultDiseasesOnly($id);

        $object->save();
    }

    /**
     * Обновление информации по медицине
     *
     * @param int $objectId
     * @param array $data
     */
    public function updateMedicalInformation(int $objectId, array $data)
    {

        ObjectMedicalInformation::updateOrCreate(['object_id' => $objectId], $data);
    }

    /**
     * Обновление данных по питанию и спорту
     *
     * @param int $objectId
     * @param array $data
     */
    public function updateFoodSport(int $objectId, array $data)
    {
        ObjectFoodAndSport::updateOrCreate(['object_id' => $objectId], $data);
    }

    /**
     * Обновление данных по номерам
     *
     * @param int $objectId
     * @param array $data
     */
    public function updateRoom(int $objectId, array $data)
    {
        $objectRoom = ObjectRoom::where('object_id', $objectId)->first();
        if (is_null($objectRoom)) $objectRoom = new ObjectRoom();
        foreach ($data as $field => $value){
            $objectRoom->$field = $value;
        }

        $objectRoom->save();
    }

    /**
     * Обновление данных по инфраструктуре
     *
     * @param int $objectId
     * @param array $data
     */
    public function updateInfrastructure(int $objectId, array $data)
    {
        ObjectInfrastructure::updateOrCreate(['object_id' => $objectId], $data);
    }

    /**
     * Получение объекта
     *
     * @param int $id
     * @param bool $fromAccount
     * @return mixed
     * @throws ApiProblemException
     */
    public function getObject(int $id, bool $fromAccount = false)
    {
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['id', $id];

        $object = ObjectPlace::where($filter)->with(
            'images', 'services', 'country:id,name_ru,name_en', 'region:id,name_ru,name_en', 'city:id,name_ru,name_en',
            'infrastructure', 'foodAndSport', 'seo', 'awardIcons',
            'therapies:therapy.id,therapy.name_ru,therapy.name_en',
            'moods:moods.id,moods.name_ru,moods.name_en,moods.alias,moods.image,moods.crop_image',
            'rooms')
            ->with(['medicalProfiles' => function($query) {
                $query->orderBy('id', 'ASC');
            }])
            ->first();
        if (is_null($object))
            throw new ApiProblemException('Объект - санаторий не найден', 404);

        $object->medical_information = $object->getMedicalInformation();
        if ($fromAccount){
            $object->hydrateModeration();
        }

        return $this->addDefaultDiseasesAttribute($object->toArray());
    }

    /**
     * Добавление признака содержат ли медпрофили объекта дефолтные/недефолтные заболевания
     *
     * @param integer $objectId
     * @return array
     */
    public function addDefaultDiseasesAttribute($object)
    {
        if (is_array($object["medical_profiles"])) {
            $ids = [];
            foreach ($object["medical_profiles"] as $medicalProfile){
                $ids[] = $medicalProfile["id"];
            }

            $objectMedicalProfiles = ObjectMedicalProfile::where('object_id', $object['id'])
                ->whereIn('medical_profile_id', $ids)
                ->get()
                ->keyBy('medical_profile_id')
                ->toArray();

            $objectHasDefaultDiseases = true;

            foreach ($object["medical_profiles"] as $medicalProfileId => $medicalProfile) {
                $object["medical_profiles"][$medicalProfileId]['has_default_diseases_only'] = $objectMedicalProfiles[$medicalProfile['id']]["has_default_diseases_only"];
                if (!$object["medical_profiles"][$medicalProfileId]['has_default_diseases_only']) {
                    $objectHasDefaultDiseases = false;
                }

                $object["medical_profiles"][$medicalProfileId]['diseases_count'] = $objectMedicalProfiles[$medicalProfile['id']]["diseases_count"];
                $object["medical_profiles"][$medicalProfileId]['not_default_diseases_count'] = $objectMedicalProfiles[$medicalProfile['id']]["not_default_diseases_count"];
            }

            $object["has_default_diseases_only"] = $objectHasDefaultDiseases;
        }

        return $object;
    }

    /**
     * Удаление объекта
     *
     * @param int $id
     * @throws ApiProblemException
     */
    public function softDelete(int $id)
    {
        $object = ObjectPlace::find($id);
        if (is_null($object))
            throw new ApiProblemException('Объект не найден', 404);
        $object->delete();
    }

    /**
     * Отправка данных на модерацию
     *
     * @param array $data
     * @param int $objectId
     * @return bool
     */
    public function setToModerateData(array $data, int $objectId)
    {
        $moderateData = [];
        $object = ObjectPlace::find($objectId);

        foreach ($data as $key=>$value){
            $createdAt = (new \DateTime('now'))->format('Y-m-d h:i:s');

            if (    $key == 'description_ru' || $key == 'description_en'){
                if (!is_null($value)) $moderateData['description_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['description_time'] = $createdAt;
            }
            if ($key == 'documents_ru' || $key == 'documents_en'){
                if (!is_null($value)) $moderateData['documents_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['documents_time'] = $createdAt;
            }
            if ($key == 'contraindications_ru' || $key == 'contraindications_en'){
                if (!is_null($value)) $moderateData['contraindications_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['contraindications_time'] = $createdAt;
            }
            if ($key == 'stars'){
                if (!is_null($value)) $moderateData['stars_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['stars_time'] = $createdAt;
            }
            if ($key == 'payment_description_ru' || $key == 'payment_description_en'){

                if (!is_null($value)) $moderateData['payment_description_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['payment_description_time'] = $createdAt;
            }
            if ($key == 'services'){
                if (!is_null($value)) $moderateData['services_status_id'] = ModerationStatus::ON_MODERATE;

                ModerationObjectsService::where('object_id', $object->id)->delete();
                foreach ($value as $serviceId){

                    $object->moderationServices()->attach(Service::find($serviceId));
                }
                $moderateData['services_time'] = $createdAt;
            }
            if ($key == 'medical_profiles'){
                if (!is_null($value)) $moderateData['medical_profile_status_id'] = ModerationStatus::ON_MODERATE;
                ModerationObjectMedicalProfile::where('object_id', $object->id)->delete();

                foreach ($value as $medicalProfileId){

                    $object->moderationMedicalProfile()->attach(MedicalProfile::find($medicalProfileId));
                }
                $moderateData['medical_profile_time'] = $createdAt;
            }
            if ($key == 'therapies'){
                if (!is_null($value)) $moderateData['therapy_status_id'] = ModerationStatus::ON_MODERATE;

                ModerationObjectTherapy::where('object_id', $object->id)->delete();
                foreach ($value as $therapyId){

                    $object->moderationTherapies()->attach(Therapy::find($therapyId));
                }
                $moderateData['therapy_time'] = $createdAt;
            }
            if ($key == 'contacts'){
                if (!is_null($value)) $moderateData['contacts_status_id'] = ModerationStatus::ON_MODERATE;
                $moderateData[$key] = $value;
                $moderateData['contacts_time'] = $createdAt;
            }
        }

        if (!empty($moderateData)){
            ModerationObject::updateOrCreate(['object_id' => $object->id], $moderateData);

            return true;
        } else {

            return false;
        }
    }

    /**
     * Общая информация
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function common(int $objectId)
    {
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['id', $objectId];

        $object = ObjectPlace::where($filter)->first();
        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);

        $object->hydrateModeration();

        return $object;
    }

    /**
     * Получение информации по медицине
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function medical(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)->first();

        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);

        $object->hydrateModeration();

        $medical = $object->getMedicalInformation();
        $medical->therapies = $object->therapiesPublic;
        $medical->medical_profiles = $object->medicalProfilesPublic;
        $medical->moderation_object = $object->moderation_object;

        return $medical;
    }

    /**
     * Получение информации по инфраструктуре
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function infrastructure(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)->first();

        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);
        $object->hydrateModeration();

        $infrastructure = $object->infrastructure;
        $infrastructure->moderation_object = $object->moderation_object;

        return $infrastructure;
    }

    /**
     * Получение кухни
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function sportFood(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)->first();

        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);
        $object->hydrateModeration();

        $sportFood = $object->foodAndSport;
        $sportFood->moderation_object = $object->moderation_object;
        $sportFood->services = $object->services;

        return $sportFood;
    }

    /**
     * Получение описания номеров
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function room(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)->first();

        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);
        $object->hydrateModeration();

        $rooms = $object->rooms;
        $rooms->room_images = $object->roomImages;
        $rooms->moderation_object = $object->moderation_object;

        return $rooms;
    }

    /**
     * Получение активных услуг
     *
     * @param int $objectId
     * @return array
     * @throws ApiProblemException
     */
    public function filterCondition(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)->first();

        if (is_null($object))
            throw new ApiProblemException('Объект-санаторий не найден', 404);
        $object->hydrateModeration();

        $services = Service::select(['id', 'name_ru', 'name_en', 'service_category_id'])->get();

        return ['services' => $services, 'moderation_object' => $object->moderation_object];
    }

    /**
     * Соохранение изображения номера
     *
     * @param Request $request
     * @param int $objectId
     * @return ObjectRoomImage
     */
    public function saveRoomImages(Request $request, int $objectId)
    {
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('room_images');

            $newRoomImage = new ObjectRoomImage();
            $newRoomImage->object_id = $objectId;
            $newRoomImage->image = Storage::url($path);
            $newRoomImage->description = $request->get('description') ?? null;

            $this->optimizeImage($newRoomImage->image, 'room_images');

            $newRoomImage->save();

            return $newRoomImage;
        }
    }

    /**
     * Обновление описания планировки объекта
     *
     * @param array $data
     * @param int $objectId
     * @param int $roomImageId
     * @return mixed
     * @throws ApiProblemException
     */
    public function updateRoomImage(array $data,int $objectId, int $roomImageId)
    {
        $roomImage = ObjectRoomImage::where('object_id', $objectId)->where('id',  $roomImageId)->first();
        if (is_null($roomImage)) throw new ApiProblemException('Планировка объекта не найдена', 404);

        foreach ($data as $field=>$value){
            $roomImage->$field = $value;
        }
        $roomImage->save();

        return $roomImage;
    }

    /**
     * Удаление зображения номера
     *
     * @param int $imageId
     * @param int $objectId
     * @return bool
     */
    public function deleteRoomImage(int $imageId, int $objectId)
    {
        $roomImage = ObjectRoomImage::where([
            ['object_id', $objectId],
            ['id', $imageId],
        ])->first();

        if ( !is_null($roomImage)){
            Storage::delete('room_images/' . basename($roomImage->image));
            $roomImage->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Формирование анкеты
     *
     * @param int $objectId
     * @param string $type
     * @param bool $attachment
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws ApiProblemException
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     * @throws \PhpOffice\PhpWord\Exception\Exception
     */
    public function getWorksheet(int $objectId, string  $type = 'doc', bool $attachment = true)
    {
        return $this->getWorksheetData($objectId, $type, $attachment);
    }
}
