<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Disease;
use App\Models\MedicalProfile;
use App\Models\ModerationPublication;
use App\Models\ModerationStatus;
use App\Models\ObjectPlace;
use App\Models\Publication;
use App\Models\PublicationDisease;
use App\Models\PublicationGeography;
use App\Models\PublicationMedicalProfile;
use App\Models\PublicationObject;
use App\Models\PublicationTherapy;
use App\Models\Therapy;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use Illuminate\Support\Facades\DB;

class PublicationModerationService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ObjectModerationService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Апрув описания
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveDescription(int $publicationId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){
            if ( !is_null($moderation->description_ru)) $publication->description_ru = $moderation->description_ru;
            if ( !is_null($moderation->description_en)) $publication->description_en = $moderation->description_en;
            $publication->save();

            $moderation->description_ru = null;
            $moderation->description_en = null;
            $moderation->description_message = null;
            $moderation->description_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });
        $user = $publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Описание публикации") );
        }
    }

    /**
     * Отклонение описания
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectDescription(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->description_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->description_message = $message;
        $moderation->save();
        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Описание публикации", $message) );
        }
    }

    /**
     * Апрув мед. профилей
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveMedicalProfiles(int $publicationId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){

            PublicationMedicalProfile::where('publication_id', $publication->id)->delete();

            foreach ($publication->onModerateMedicalProfiles as $itemId){
                $publication->medicalProfiles()->attach($itemId);
                $publication->onModerateMedicalProfiles()->detach($itemId);
            }

            $moderation->medical_profiles_message = null;
            $moderation->medical_profiles_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });
        $user = $publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Мед. профили") );
        }
    }

    /**
     * Отклонения мед. профилей
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectMedicalProfiles(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->medical_profiles_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->medical_profiles_message = $message;
        $moderation->save();

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Мед. профили", $message) );
        }
    }

    /**
     * Апрув мет. лечения
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveTherapies(int $publicationId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){

            PublicationTherapy::where('publication_id', $publication->id)->delete();

            foreach ($publication->onModerateTherapies as $itemId){
                $publication->therapies()->attach($itemId);
                $publication->onModerateTherapies()->detach($itemId);
            }

            $moderation->therapies_message = null;
            $moderation->therapies_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });
        $user = $publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Методы лечения") );
        }
    }

    /**
     * Отклонение мет. лечения
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectTherapies(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->therapies_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->therapies_message = $message;
        $moderation->save();

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Методы лечения", $message) );
        }
    }

    /**
     * Апрув заболеваний
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveDiseases(int $publicationId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){

            PublicationDisease::where('publication_id', $publication->id)->delete();

            foreach ($publication->onModerateDiseases as $itemId){
                $publication->diseases()->attach($itemId);
                $publication->onModerateDiseases()->detach($itemId);
            }

            $moderation->diseases_message = null;
            $moderation->diseases_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });
        $user = $publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Заболевания") );
        }
    }

    /**
     * Оклонение заболеваний
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectDiseases(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->diseases_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->diseases_message = $message;
        $moderation->save();

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Заболевания", $message) );
        }
    }

    /**
     * Апрув географии
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveGeography(int $publicationId)
    {
        $publication = PublicationGeography::where('publication_id', $publicationId)->first();
        if (is_null($publication)) {
            $publication = new PublicationGeography();
            $publication->publication_id = $publicationId;
        }

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){
            $publication->country_id = $moderation->country_id;
            $publication->region_id = $moderation->region_id;
            $publication->city_id = $moderation->city_id;
            $publication->save();

            $moderation->country_id = null;
            $moderation->region_id = null;
            $moderation->city_id = null;
            $moderation->geography_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "География") );
        }
    }

    /**
     * Отклонение географии
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectGeography(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->geography_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->geography_message = $message;
        $moderation->save();

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "География", $message) );
        }
    }

    /**
     * Апрув объектов
     *
     * @param int $publicationId
     * @throws ApiProblemException
     */
    public function approveObjects(int $publicationId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        DB::transaction(function () use ($publication, $moderation){

            PublicationObject::where('publication_id', $publication->id)->delete();

            foreach ($publication->onModerateObjects as $itemId){
                $publication->objects()->attach($itemId);
                $publication->onModerateObjects()->detach($itemId);
            }

            $moderation->objects_message = null;
            $moderation->objects_status_id = ModerationStatus::MODERATE_OK;
            $moderation->save();
        });

        $user = $publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Объекты / санатории") );
        }
    }

    /**
     * Отклонение объектов
     *
     * @param int $publicationId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectObjects(int $publicationId, string $message)
    {
        $moderation = ModerationPublication::where('publication_id', $publicationId)->first();
        if (is_null($moderation)) throw new ApiProblemException('Данные для модерации отсутствуют', 404);

        $moderation->objects_status_id = ModerationStatus::MODERATE_REJECT;
        $moderation->objects_message = $message;
        $moderation->save();

        $user = $moderation->publication->partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Объекты / санатории", $message) );
        }
    }

    /**
     * Получение списка публикаций для модерации
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @return array
     */
    public function getForApproval(int $page, int $rowsPerPage,?array $sorting, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = mb_strtolower($searchKey);

        $qb = Publication::when($sorting, function ($query, $sorting){
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
                if ( !is_null($searchKey) ){
                    $query = $query->where('title_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('title_en', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('author_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('author_en', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('description_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('description_en', 'like', "%{$searchKey}%");

                    return $query;
                }
        });
        $qb->whereHas('moderationPublication', function($q){
            $q->where('description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('medical_profiles_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('therapies_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('diseases_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('objects_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('geography_status_id', ModerationStatus::ON_MODERATE);
        });
        $moderationStatus = ModerationStatus::ON_MODERATE;
        $qb->orWhereRaw("id in( SELECT publication_id FROM publication_galleries WHERE moderation_status_id = {$moderationStatus})");

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->with(
            'type:id,name_ru',
            'partner:id,partner_type_id,organisation_short_name_ru',
            'partner.type:id,image,name_ru'
        )->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение публикации для модерации
     *
     * @param int $publicationId
     * @return int
     * @throws ApiProblemException
     */
    public function getPublications(int $publicationId)
    {
        $publication = Publication::where('id', $publicationId)
            ->with(
                'type:id,name_ru,alias',
                'partner:id,partner_type_id,organisation_short_name_ru,logo,alias',
                'partner.type:id,name_ru as name,alias',
                'seo',

                'objects:objects.id,objects.title_ru,objects.alias',
                'medicalProfiles:medical_profiles.id,medical_profiles.name_ru,medical_profiles.alias',
                'therapies:therapy.id,therapy.name_ru,therapy.alias',
                'diseases:diseases.id,diseases.name_ru,diseases.alias',

                'geography.country:countries.id,name_ru,alias,is_visible',
                'geography.region:regions.id,country_id,name_ru,alias,is_visible',
                'geography.region.country:countries.id,name_ru,alias,is_visible',
                'geography.city.region:regions.id,country_id,name_ru,alias,is_visible',
                'geography.city.country:countries.id,name_ru,alias,is_visible',
                'geography.city:cities.id,country_id,region_id,name_ru,alias,is_visible'
            )
            ->first();
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $publication->hydrateModeration();

        return $publication;
    }

    /**
     * Сохранение данных на модерацию.
     *
     * @param array $dataToModeration
     * @param int $publicationId
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function saveData(array $dataToModeration, int $publicationId, int $partnerId)
    {
        $publication = Publication::where('id', $publicationId)->where('partner_id', $partnerId)->first();
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $moderationPublication = ModerationPublication::where('publication_id', $publication->id)->first();
        if (is_null($moderationPublication)) {
            $moderationPublication = new ModerationPublication();
            $moderationPublication->publication_id = $publication->id;
        }
        foreach ($dataToModeration as $field=>$value){
            if (!is_null($value)){
                switch ($field){
                    case 'description_ru' :
                        $moderationPublication->description_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->description_message = null;
                        $moderationPublication->description_ru = $value;
                        break;

                    case 'description_en' :
                        $moderationPublication->description_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->description_message = null;
                        $moderationPublication->description_en = $value;
                        break;

                    case 'medical_profiles' :
                        $moderationPublication->medical_profiles_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->medical_profiles_message = null;
                        DB::table('moderation_publication_medical_profiles')
                            ->where('publication_id', $publicationId)->delete();
                        foreach ($value as $id){
                            $item = MedicalProfile::find($id);
                            if (is_null($item))
                                throw new ApiProblemException("Мед профиль с ID=$id не найден", 404);
                            $publication->onModerateMedicalProfiles()->attach($item);
                        }
                        break;

                    case 'therapies' :
                        $moderationPublication->therapies_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->therapies_message = null;
                        DB::table('moderation_publication_therapies')
                            ->where('publication_id', $publicationId)->delete();
                        foreach ($value as $id){
                            $item = Therapy::find($id);
                            if (is_null($item))
                                throw new ApiProblemException("Метод лечения с ID=$id не найден", 404);
                            $publication->onModerateTherapies()->attach($item);
                        }
                        break;

                    case 'diseases' :
                        $moderationPublication->diseases_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->diseases_message = null;
                        DB::table('moderation_publication_diseases')
                            ->where('publication_id', $publicationId)->delete();
                        foreach ($value as $id){
                            $item = Disease::find($id);
                            if (is_null($item))
                                throw new ApiProblemException("Заболевание с ID=$id не найдено", 404);
                            $publication->onModerateDiseases()->attach($item);
                        }
                        break;

                    case 'objects' :
                        $moderationPublication->objects_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->objects_message = null;
                        DB::table('moderation_publication_objects')
                            ->where('publication_id', $publicationId)->delete();
                        foreach ($value as $id){
                            $item = ObjectPlace::find($id);
                            if (is_null($item))
                                throw new ApiProblemException("Объект - санаторий с ID=$id не найден", 404);
                            $publication->onModerateObjects()->attach($item);
                        }
                        break;

                    case 'country_id' :
                        $moderationPublication->geography_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->geography_message = null;
                        $moderationPublication->country_id = $value;
                        break;

                    case 'region_id' :
                        $moderationPublication->geography_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->geography_message = null;
                        $moderationPublication->region_id = $value;
                        break;

                    case 'city_id' :
                        $moderationPublication->geography_status_id = ModerationStatus::ON_MODERATE;
                        $moderationPublication->geography_message = null;
                        $moderationPublication->city_id = $value;
                        break;

                    default :
                        throw new ApiProblemException("Параметр $field не определен для модерации на сервере", 422);
                }
            }
        }
        $moderationPublication->save();
    }
}
