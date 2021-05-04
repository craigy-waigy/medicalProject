<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ModerationObject;
use App\Models\ModerationObjectMedicalProfile;
use App\Models\ModerationObjectsService;
use App\Models\ModerationObjectTherapy;
use App\Models\ModerationStatus;
use App\Models\ObjectPlace;
use App\Models\ObjectMedicalProfile;
use App\Models\ObjectService;
use App\Models\ObjectTherapy;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use Illuminate\Support\Facades\DB;
use App\Services\MedicalProfileService;

class ObjectModerationService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * @var MedicalProfileService
     */
    protected $medicalProfileService;

    /**
     * ObjectModerationService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
        $this->medicalProfileService = new MedicalProfileService();
    }

    public function approveDescription(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;
        if ( !is_null($data->description_ru)) $object->description_ru = $data->description_ru;
        if ( !is_null($data->description_en)) $object->description_en = $data->description_en;
        $object->save();

        $data->description_ru = null;
        $data->description_en = null;
        $data->description_message = null;
        $data->description_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->description_status_id = ModerationStatus::MODERATE_OK;
        $data->save();
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Описание") );
        }
    }

    public function rejectDescription(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->description_message = $message;
        $data->description_status_id = ModerationStatus::MODERATE_REJECT;
        $data->description_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();

        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Описание", $message) );
        }
    }

    public function approveStars(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;
        if ( !is_null($data->stars)) $object->stars = $data->stars;
        $object->save();

        $data->stars = null;
        $data->stars_message = null;
        $data->stars_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->stars_status_id = ModerationStatus::MODERATE_OK;
        $data->save();
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Звездность") );
        }
    }

    public function rejectStars(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->stars_message = $message;
        $data->stars_status_id = ModerationStatus::MODERATE_REJECT;
        $data->stars_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Звездность", $message) );
        }
    }

    public function approvePaymentDescription(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;
        if ( !is_null($data->payment_description_ru)) $object->payment_description_ru = $data->payment_description_ru;
        if ( !is_null($data->payment_description_en)) $object->payment_description_en = $data->payment_description_en;
        $object->save();

        $data->payment_description_ru = null;
        $data->payment_description_en = null;
        $data->payment_description_message = null;
        $data->payment_description_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->payment_description_status_id = ModerationStatus::MODERATE_OK;
        $data->save();
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Условия оплаты") );
        }
    }

    public function rejectPaymentDescription(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->payment_description_message = $message;
        $data->payment_description_status_id = ModerationStatus::MODERATE_REJECT;
        $data->payment_description_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Условия оплаты", $message) );
        }
    }

    public function approveDocuments(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;
        if ( !is_null($data->documents_ru)) $object->documents_ru = $data->documents_ru;
        if ( !is_null($data->documents_en)) $object->documents_en = $data->documents_en;
        $object->save();

        $data->documents_ru = null;
        $data->documents_en = null;
        $data->documents_message = null;
        $data->documents_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->documents_status_id = ModerationStatus::MODERATE_OK;
        $data->save();
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Необходимые документы") );
        }
    }

    public function rejectDocuments(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->documents_message = $message;
        $data->documents_status_id = ModerationStatus::MODERATE_REJECT;
        $data->documents_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Необходимые документы", $message) );
        }
    }

    public function approveContraindications(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;
        if ( !is_null($data->contraindications_ru)) $object->contraindications_ru = $data->contraindications_ru;
        if ( !is_null($data->contraindications_en)) $object->contraindications_en = $data->contraindications_en;
        $object->save();

        $data->contraindications_ru = null;
        $data->contraindications_en = null;
        $data->contraindications_message = null;
        $data->contraindications_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->contraindications_status_id = ModerationStatus::MODERATE_OK;
        $data->save();
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Противопоказания") );
        }
    }

    public function rejectContraindications(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->contraindications_message = $message;
        $data->contraindications_status_id = ModerationStatus::MODERATE_REJECT;
        $data->contraindications_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Противопоказания", $message) );
        }
    }

    public function approveServices(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);
        $object = $data->object;

        DB::transaction(function () use ($objectId, $object, $data){
            ObjectService::where('object_id', $objectId)->delete();
            foreach ($object->moderationServices as $objectService){
                $object->services()->attach($objectService);
            }
            $data->services_status_id = ModerationStatus::MODERATE_OK;
            $data->services_message = null;
            $data->services_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
            $data->save();

            ModerationObjectsService::where('object_id', $objectId)->delete();
        });
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Услуги") );
        }
    }

    public function rejectServices(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->services_message = $message;
        $data->services_status_id = ModerationStatus::MODERATE_REJECT;
        $data->services_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Услуги", $message) );
        }
    }

    public function approveMedicalProfile(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);
        $object = $data->object;

        DB::transaction(function () use ($objectId, $object, $data){
            ObjectMedicalProfile::where('object_id', $objectId)->delete();
            foreach ($object->moderationMedicalProfile as $objectMedicalProfile){

               $object->medicalProfiles()->attach($objectMedicalProfile);

               //Проинициализируем данные о дефолтных/недефолтных заболеваниях
               $this->medicalProfileService->checkMedicalProfileHasDefaultDiseasesOnly($objectMedicalProfile['id'], $objectId);
               $this->medicalProfileService->checkObjectHasDefaultDiseasesOnly($objectId);
            }

            $data->medical_profile_status_id = ModerationStatus::MODERATE_OK;
            $data->medical_profile_message = null;
            $data->medical_profile_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
            $data->save();

            ModerationObjectMedicalProfile::where('object_id', $objectId)->delete();
        });
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Медицинские профили") );
        }
    }

    public function rejectMedicalProfile(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->medical_profile_message = $message;
        $data->medical_profile_status_id = ModerationStatus::MODERATE_REJECT;
        $data->medical_profile_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Медицинские профили", $message) );
        }
    }

    public function approveTherapy(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;

        DB::transaction(function () use ($objectId, $object, $data){
            ObjectTherapy::where('object_id', $objectId)->delete();
            foreach ($object->moderationTherapies as $objectTherapy){
                $object->therapies()->attach($objectTherapy);
            }
            $data->therapy_status_id = ModerationStatus::MODERATE_OK;
            $data->therapy_message = null;
            $data->therapy_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
            $data->save();

            ModerationObjectTherapy::where('object_id', $objectId)->delete();
        });
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Методы лечения") );
        }
    }

    public function rejectTherapy(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->therapy_message = $message;
        $data->therapy_status_id = ModerationStatus::MODERATE_REJECT;
        $data->therapy_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Методы лечения", $message) );
        }
    }

    public function approveContacts(int $objectId)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $object = $data->object;

        DB::transaction(function () use ($objectId, $object, $data){
            $object->contacts = $data->contacts;
            $object->save();

            $data->contacts_status_id = ModerationStatus::MODERATE_OK;
            $data->contacts_message = null;
            $data->contacts_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
            $data->save();

            ModerationObjectTherapy::where('object_id', $objectId)->delete();
        });
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Контакты") );
        }
    }

    public function rejectContacts(int $objectId, string $message)
    {
        $data = ModerationObject::where('object_id', $objectId)->first();
        if (is_null($data))
            throw new ApiProblemException('Данные для модерации не найдены', 404);

        $data->contacts_message = $message;
        $data->contacts_status_id = ModerationStatus::MODERATE_REJECT;
        $data->contacts_time = (new \DateTime('now'))->format('Y-m-d h:i:d');
        $data->save();
        $object = $data->object;
        if ( !is_null($object->user) ){
            $user = $object->user;
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Контакты", $message) );
        }
    }

    public function getForApproval(int $page, int $rowsPerPage,?array $sorting, ?string $searchKey)
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
                        $query = $query->orderBy($key, $value);
                    }
                    return $query;
                } else {
                    return $query->orderBy('id', 'asc');
                }

            })
            ->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey) ){
                    $query = $query->where('title_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('title_en', 'like', "%{$searchKey}%");
                    return $query;
                }
            });

        $queryBuilder->whereHas('moderationObject', function($q){
            $q->where('description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('stars_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('payment_description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('documents_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('contraindications_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('services_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('medical_profile_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('therapy_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('contacts_status_id', ModerationStatus::ON_MODERATE);
        });
        $moderationStatus = ModerationStatus::ON_MODERATE;
        $queryBuilder->orWhereRaw("id in( SELECT object_id FROM object_images WHERE moderation_status = {$moderationStatus})");


        $total = $queryBuilder->count();
        $items = $queryBuilder->skip($skip)->take($rowsPerPage)
            ->select(['id', 'country_id', 'region_id', 'city_id', 'title_ru', 'alias', 'is_visibly',
                'created_at', 'modified_at'])
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
     * Получение объекта для модерации
     *
     * @param int $objectId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getObjectForApproval(int $objectId)
    {
        $object = ObjectPlace::where('id', $objectId)
            ->with(
            'services',
            'medicalProfiles',
            'therapies'
        )->first();

        if (is_null($object)) throw new ApiProblemException('Объект не найден', 404);

        $object->hydrateModeration();

        return $object;
    }
}
