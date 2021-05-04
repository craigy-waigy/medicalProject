<?php

namespace App\Models;

use App\Exceptions\ApiProblemException;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use App\Models\Region;
use App\Models\City;
use App\Models\User;
use App\Models\ObjectImage;
use App\Models\ModerationObject;
use App\Models\ObjectAward;
use App\Models\Service;
use App\Models\MedicalProfile;
use App\Models\Therapy;
use App\Models\ObjectMedicalInformation;
use App\Models\ObjectInfrastructure;
use App\Models\ObjectFoodAndSport;
use App\Models\ObjectRoom;
use App\Models\ShowcaseRoom;
use App\Models\AwardIcon;
use App\Models\SeoInformation;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Elastic\Elasticsearch;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectPlace extends Model
{
    use SoftDeletes, LogsActivity;

    public $table = 'objects';
    protected $casts = [
        'capabilities' => 'json',
        'services' => 'json',
        'contacts' => 'json',
        'commission' => 'json',
        'other_taxes' => 'json',
        'climatic_factors' => 'json',
        'water' => 'json',
        'healing_mud' => 'json',
        'certified_personal' => 'json',
        'operation_in_object' => 'json',
        'drinking_water_plumbing' => 'json',
        'season_period' => 'json',
        'months_peak' => 'json',
        'months_lows' => 'json',
        'effective_months' => 'json',
        'contingent' => 'json',
        'territory' => 'json',
        'reservoir' => 'json',
        'pools' => 'json',
        'parking' => 'json',
        'markets' => 'json',
        'pharmacies' => 'json',
        'restroom_square' => 'json',
        'welcome_kit' => 'json',
        'mini_bar' => 'json',
        'is_visibly' => 'boolean',
        'is_deleted' => 'boolean',
        'bankomats' => 'json',
        'discount_cards' => 'json',
        'partnership_programs' => 'json',
        'early_check_in' => 'json',
        'late_check_out' => 'json',
        'tags_ru' => 'json',
        'tags_en' => 'json',
    ];
    protected $hidden = [ 'pivot' ];

    protected $guarded = [];
    protected static $logUnguarded = true;


    public const UPDATED_AT = 'modified_at';

    public function user()
    {
        return $this->belongsTo(ViewUser::class, 'user_id')->where('is_deleted', '=', false);
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }

    public function images()
    {
        return $this->hasMany('App\Models\ObjectImage', 'object_id', 'id')->orderBy('sorting_rule', 'asc');
    }

    public function moderatedImages()
    {
        return $this->hasMany('App\Models\ObjectImage', 'object_id', 'id')
            ->whereIn('moderation_status', [ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE])
            ->orderBy('sorting_rule', 'asc')
            ;
    }

    /**
     *  Главное изображение объекта
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function image()
    {
        return $this->hasOne('App\Models\ObjectImage', 'object_id', 'id')
            ->whereIn('moderation_status', [ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE])
            ->orderBy('sorting_rule', 'asc')
            ;
    }

    /**
     * Данные для модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function moderationObject()
    {
        return $this->hasOne('App\Models\ModerationObject', 'object_id');
    }

    /**
     * Награды обекта для модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function awards()
    {
        return $this->hasMany('App\Models\ObjectAward', 'object_id');
    }

    /**
     * Услуги
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function services()
    {
        return $this->belongsToMany('App\Models\Service', 'object_services',
            'object_id', 'service_id')->select([
            'services.id', 'services.name_ru', 'services.name_en'
        ]);
    }

    /**
     * Услуги на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderationServices()
    {
        return $this->belongsToMany('App\Models\Service', 'moderation_objects_services',
            'object_id', 'service_id')->select([
                'services.id', 'services.name_ru', 'services.name_en'
        ]);
    }

    /**
     * Медицинские профили
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicalProfiles()
    {
        return $this->belongsToMany('App\Models\MedicalProfile', 'object_medical_profiles',
            'object_id', 'medical_profile_id')->select([
                'medical_profiles.id', 'medical_profiles.name_ru', 'medical_profiles.name_en'
        ]);
    }

    /**
     * Медицинские профили, публичные
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicalProfilesPublic()
    {
        return $this->belongsToMany('App\Models\MedicalProfile', 'object_medical_profiles',
            'object_id', 'medical_profile_id')
            ->where('medical_profiles.active', '=', true)->select([
                'medical_profiles.id', 'medical_profiles.name_ru', 'medical_profiles.name_en'
            ]);
    }

    /**
     * Медицинские профили на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderationMedicalProfile()
    {
        return $this->belongsToMany('App\Models\MedicalProfile', 'moderation_object_medical_profiles',
            'object_id', 'medical_profile_id')->select([
                'medical_profiles.id',  'medical_profiles.name_ru', 'medical_profiles.name_en'
        ]);
    }

    /**
     * Профили лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function therapies()
    {
        return $this->belongsToMany('App\Models\Therapy', 'object_therapies', 'object_id',
            'therapy_id')->select([
                'therapy.id', 'therapy.name_ru', 'therapy.name_en'
        ]);
    }

    /**
     * Профили лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function therapiesPublic()
    {
        return $this->belongsToMany('App\Models\Therapy', 'object_therapies', 'object_id',
            'therapy_id')->select([
            'therapy.id', 'therapy.name_ru', 'therapy.name_en'
        ])->where('therapy.active', '=', true);
    }

    /**
     * Профили лечения на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moderationTherapies()
    {
        return $this->belongsToMany('App\Models\Therapy', 'moderation_object_therapies', 'object_id',
            'therapy_id')->select([
                'therapy.id', 'therapy.name_ru', 'therapy.name_en'
        ]);
    }

    /**
     * Медицинская информация по объекту
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function medicalInformation()
    {
        return $this->hasOne('App\Models\ObjectMedicalInformation', 'object_id');
    }

    /**
     * Инфраструктура
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function infrastructure()
    {
        return $this->hasOne('App\Models\ObjectInfrastructure', 'object_id');
    }

    /**
     * Питание и спорт
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function foodAndSport()
    {
        return $this->hasOne('App\Models\ObjectFoodAndSport', 'object_id');
    }

    /**
     * Номера
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rooms()
    {
        return $this->hasOne('App\Models\ObjectRoom', 'object_id');
    }

    /**
     * Объект в бронебазе
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bbase()
    {
        return $this->hasOne('App\Models\ObjectBbase', 'object_id');
    }

    public function roomImages()
    {
        return $this->hasMany(ObjectRoomImage::class, 'object_id');
    }

    /**
     * Витрина номеров
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function showcaseRooms()
    {
        return $this->hasMany('App\Models\ShowcaseRoom', 'object_id');
    }

    /**
     * Иконки награды (отмодерированные награды)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function awardIcons()
    {
        return $this->belongsToMany('App\Models\AwardIcon', 'object_award_icons', 'object_id',
            'award_icon_id');
    }

    /**
     * Иконки награды (отмодерированные награды) публичные
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function awardIconsPublic()
    {
        return $this->belongsToMany('App\Models\AwardIcon', 'object_award_icons', 'object_id',
            'award_icon_id')->where('award_icons.active', '=', true);
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class, 'object_id')
            ->select(['object_id','for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Счетчик просмотров
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function viewingCount()
    {
        return $this->hasMany(ObjectViewingCount::class, 'object_id');
    }

    /**
     * отзывы и рейтинг
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'object_id');
    }

    /**
     * Mood-теги
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function moods()
    {
        return $this->belongsToMany('App\Models\Mood', 'object_moods',
            'object_id', 'mood_id')->select([
            'mood.id', 'mood.name_ru', 'mood.name_en', 'mood.alias', 'mood.image', 'mood.crop_image'
        ]);
    }

    /**
     * Проверка - визбранном
     *
     * @return $this
     */
    public function isFavorite()
    {
        if (auth('api')->check()){
            $userId = auth('api')->user()->id;
            $count = FavoriteObject::where('user_id', $userId)->where('object_id', $this->id)->count();

            if ($count > 0) $this->is_favorite = true;
            else $this->is_favorite = false;

        } else {
            $this->is_favorite = null;
        }

        return $this;
    }

    /**
     * Подготовка формата модерации
     */
    public function hydrateModeration()
    {
        $moderation = ModerationObject::where('object_id', $this->id)->first();
        if (!is_null($moderation)){
            $object = ObjectPlace::find($this->id);
            $moderationObject = [
                'description_ru' => [
                    'status_id' => $moderation->description_status_id,
                    'value' => $moderation->description_ru,
                    'message' => $moderation->description_message,
                    'time' => $moderation->description_time,
                ],
                'description_en' => [
                    'status_id' => $moderation->description_status_id,
                    'value' => $moderation->description_en,
                    'message' => $moderation->description_message,
                    'time' => $moderation->description_time,
                ],
                'stars' => [
                    'status_id' => $moderation->stars_status_id,
                    'value' => $moderation->stars,
                    'message' => $moderation->stars_message,
                    'time' => $moderation->stars_time,
                ],
                'payment_description_ru' => [
                    'status_id' => $moderation->payment_description_status_id,
                    'value' => $moderation->payment_description_ru,
                    'message' => $moderation->payment_description_message,
                    'time' => $moderation->payment_description_time,
                ],
                'payment_description_en' => [
                    'status_id' => $moderation->payment_description_status_id,
                    'value' => $moderation->payment_description_en,
                    'message' => $moderation->payment_description_message,
                    'time' => $moderation->payment_description_time,
                ],
                'documents_ru' => [
                    'status_id' => $moderation->documents_status_id,
                    'value' => $moderation->documents_ru,
                    'message' => $moderation->documents_message,
                    'time' => $moderation->payment_description_time,
                ],
                'documents_en' => [
                    'status_id' => $moderation->documents_status_id,
                    'value' => $moderation->documents_en,
                    'message' => $moderation->documents_message,
                    'time' => $moderation->documents_time,
                ],
                'contraindications_ru' => [
                    'status_id' => $moderation->contraindications_status_id,
                    'value' => $moderation->contraindications_ru,
                    'message' => $moderation->contraindications_message,
                    'time' => $moderation->contraindications_time,
                ],
                'contraindications_en' => [
                    'status_id' => $moderation->contraindications_status_id,
                    'value' => $moderation->contraindications_en,
                    'message' => $moderation->contraindications_message,
                    'time' => $moderation->contraindications_time,
                ],
                'services' => [
                    'status_id' => $moderation->services_status_id,
                    'value' => count($object->moderationServices) > 0 ? $object->moderationServices: null,
                    'message' => $moderation->services_message,
                    'time' => $moderation->services_time,
                ],
                'medical_profiles' => [
                    'status_id' => $moderation->medical_profile_status_id,
                    'value' => count($object->moderationMedicalProfile) > 0 ? $object->moderationMedicalProfile : null,
                    'message' => $moderation->medical_profile_message,
                    'time' => $moderation->medical_profile_time,
                ],
                'therapies' => [
                    'status_id' => $moderation->therapy_status_id,
                    'value' => count($object->moderationTherapies) > 0 ? $object->moderationTherapies : null,
                    'message' => $moderation->therapy_message,
                    'time' => $moderation->therapy_time,
                ],
                'contacts' => [
                    'status_id' => $moderation->contacts_status_id,
                    'value' =>
                        $object->moderationObject->contacts !== null ? $object->moderationObject->contacts : null,
                    'message' => $moderation->contacts_message,
                    'time' => $moderation->contacts_time,
                ],
            ];
            $this->moderation_object = $moderationObject;
        }
        else {
            $this->moderation_object = null;
        }
    }

    /**
     * Получение информации по медицине
     *
     * @return null
     */
    public function getMedicalInformation()
    {
        $medicalInformation = $this->medicalInformation;
        if (!is_null($medicalInformation)){
            $medicalInformation->contraindications_ru = $this->contraindications_ru;
            $medicalInformation->contraindications_en = $this->contraindications_en;
        }

        return $medicalInformation;
    }

    /**
     * Врачи санатория
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sanatoriumDoctors()
    {
        return $this->hasMany(SanatoriumDoctor::class, 'object_id');
    }

    /**
     *  Добавления рандомного врача санатория
     *
     * @param $locale
     * @throws ApiProblemException
     */
    public function addRandomDoctor($locale)
    {
        switch ($locale){
            case 'ru' :
                $doctor = SanatoriumDoctor::select([
                    'id', 'user_id', 'object_id', 'online', 'languages', 'specializations_ru as specializations'
                ])->where('object_id', $this->id)->where('online', true)
                    ->with('user')
                    ->get();
                break;

            case 'en' :
                $doctor = SanatoriumDoctor::select([
                    'id', 'user_id', 'object_id', 'online', 'languages', 'specializations_en as specializations'
                ])->where('object_id', $this->id)->where('online', true)
                    ->with('user')
                    ->get();
                break;

            default :
                throw new ApiProblemException('не поддерживаемая локаль', 422);
        }
        if ($doctor->count() == 0){
            $this->sanatorium_doctor = null;
        }
        elseif ($doctor->count() == 1){
            $this->sanatorium_doctor = $doctor[0];
        }
        else{
            $idexOfDoctor = random_int(0, $doctor->count() - 1);
            $this->sanatorium_doctor = $doctor[ $idexOfDoctor ];
        }
    }


    protected static function boot()
    {
        parent::boot();

        /**
         * Обновляем индекс если поменяли запись
         */
        static::saved(function ($model){
            $medicalProfilesRu = [];
            $medicalProfilesEn = [];
            foreach ($model->medicalProfilesPublic as $item){
                $medicalProfilesRu[] = $item->name_ru;
                $medicalProfilesEn[] = $item->name_en;
            }
            $therapiesRu = [];
            $therapiesEn = [];
            foreach ($model->therapiesPublic as $item){
                $therapiesRu[] = $item->name_ru;
                $therapiesEn[] = $item->name_en;
            }
            $elasticsearch = new Elasticsearch;
            $elasticsearch->index([
                'index' => 'objects-ru',
                'type' => 'object',
                'id' => $model->id,
                'body' => [
                    'title' => strip_tags($model->title_ru),
                    'description' => strip_tags($model->description_ru),
                    'tags' => $model->tags_ru,
                    'medical_profiles' => $medicalProfilesRu,
                    'therapies' => $therapiesRu,
                    'geography' => [
                        $model->country->name_ru ?? '',
                        $model->region->name_ru ?? '',
                        $model->city->name_ru ?? '',
                    ],
                ],
            ]);
            $elasticsearch->index([
                'index' => 'objects-en',
                'type' => 'object',
                'id' => $model->id,
                'body' => [
                    'title' => strip_tags($model->title_en),
                    'description' => strip_tags($model->description_en),
                    'tags' => $model->tags_en,
                    'medical_profiles' => $medicalProfilesEn,
                    'therapies' => $therapiesRu,
                    'geography' => [
                        $model->country->name_en ?? '',
                        $model->region->name_en ?? '',
                        $model->city->name_en ?? '',
                    ]
                ],
            ]);
        });

        /**
         * Удаляем из индекса при удалении записи
         */
        static::deleted(function ($model){
            $model->is_deleted = true;
            $model->save();
            $elasticsearch = new Elasticsearch;
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'objects-ru'
            ]);
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'objects-en'
            ]);
        });
    }

    /**
     * LogsActivity, название события
     *
     * @param string $eventName
     * @return string
     */
        public function getDescriptionForEvent(string $eventName): string
        {
            if ($eventName == 'created')
                $event = 'создан';

            elseif ($eventName == 'updated')
                $event = 'обновлен';

            elseif ($eventName == 'deleted')
                $event = 'удален';
            else
                $event = $eventName;

            return "Объект: \"{$this->title_ru}\" был {$event}";
        }
}
