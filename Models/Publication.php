<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Publication extends Model
{
    use LogsActivity;

    protected $guarded = [];
    protected static $logUnguarded = true;

    /**
     * LogsActivity, название события
     *
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created')
            $event = 'добавлена';

        elseif ($eventName == 'updated')
            $event = 'обновлена';

        elseif ($eventName == 'deleted')
            $event = 'удалена';
        else
            $event = $eventName;

        return "Публикация партнера \"{$this->title_ru}\" была {$event}";
    }

    /**
     * Партнер
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
    /**
     * Галлерея публикации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(PublicationGallery::class);
    }

    /**
     * Галлерея на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function onModerateImages()
    {
        return $this->hasMany(PublicationGallery::class)->where('moderation_status_id', '=',
            ModerationStatus::ON_MODERATE);
    }

    /**
     * Отмодерированная галлерея
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicImages()
    {
        return $this->hasMany(PublicationGallery::class)->whereIn('moderation_status_id', [
            ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE]);
    }

    /**
     * Тип публикации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(PublicationType::class, 'publication_type_id');
    }

    /**
     * Профили лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicalProfiles()
    {
        return $this->belongsToMany(MedicalProfile::class, 'publication_medical_profiles',
            'publication_id', 'medical_profile_id');
    }

    /**
     * Профили лечения на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function onModerateMedicalProfiles()
    {
        return $this->belongsToMany(MedicalProfile::class, 'moderation_publication_medical_profiles',
            'publication_id', 'medical_profile_id')->select([
                'medical_profiles.id',
                'medical_profiles.name_ru',
        ]);
    }

    /**
     * Методы лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function therapies()
    {
        return $this->belongsToMany(Therapy::class, 'publication_therapies',
            'publication_id', 'therapy_id');
    }

    /**
     * Методы лечения на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function onModerateTherapies()
    {
        return $this->belongsToMany(Therapy::class, 'moderation_publication_therapies',
            'publication_id', 'therapy_id')->select([
            'therapy.id',
            'therapy.name_ru',
        ]);
    }

    /**
     * Заболевания
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diseases()
    {
        return $this->belongsToMany(Disease::class, 'publication_diseases',
            'publication_id', 'disease_id');
    }

    /**
     * Заболевания на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function onModerateDiseases()
    {
        return $this->belongsToMany(Disease::class, 'moderation_publication_diseases',
            'publication_id', 'disease_id')->select([
            'diseases.id',
            'diseases.name_ru',
        ]);
    }

    /**
     * Объекты - санатории
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function objects()
    {
        return $this->belongsToMany(ObjectPlace::class, 'publication_objects',
            'publication_id', 'object_id');
    }

    /**
     * Объекты - санатории на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function onModerateObjects()
    {
        return $this->belongsToMany(ObjectPlace::class, 'moderation_publication_objects',
            'publication_id', 'object_id')->select([
            'objects.id',
            'objects.title_ru',
        ]);
    }

    /**
     * Данные модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function moderationPublication()
    {
        return $this->hasOne(ModerationPublication::class);
    }

    /**
     * География на модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function geography()
    {
        return $this->hasOne(PublicationGeography::class);
    }

    /**
     * Сео информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class)->select(['publication_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
            'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Файловое хранилище
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(FileStorage::class);
    }

    /**
     * Подготовка формата модерации
     */
    public function hydrateModeration()
    {
        $publication = Publication::find($this->id);
        $moderation = $publication->moderationPublication;
        if (!is_null($moderation)){
            $moderationObject = [
                'description_ru' => [
                    'status_id' => $moderation->description_status_id,
                    'value' => $moderation->description_ru,
                    'message' => $moderation->description_message,
                ],
                'description_en' => [
                    'status_id' => $moderation->description_status_id,
                    'value' => $moderation->description_en,
                    'message' => $moderation->description_message,
                ],
                'medical_profiles' => [
                    'status_id' => $moderation->medical_profiles_status_id,
                    'value' => count($publication->onModerateMedicalProfiles) > 0 ? $publication->onModerateMedicalProfiles : null,
                    'message' => $moderation->medical_profiles_message,
                ],
                'therapies' => [
                    'status_id' => $moderation->therapies_status_id,
                    'value' => count($publication->onModerateTherapies) > 0 ? $publication->onModerateTherapies : null,
                    'message' => $moderation->therapies_message,
                ],
                'diseases' => [
                    'status_id' => $moderation->diseases_status_id,
                    'value' => count($publication->onModerateDiseases) > 0 ? $publication->onModerateDiseases : null,
                    'message' => $moderation->diseases_message,
                ],
                'objects' => [
                    'status_id' => $moderation->objects_status_id,
                    'value' => count($publication->onModerateObjects) > 0 ? $publication->onModerateObjects : null,
                    'message' => $moderation->objects_message,
                ],
                'geography' => [
                    'status_id' => $moderation->geography_status_id,
                    'value' => [
                        'country' => Country::select(['id', 'name_ru'])->where('id', $moderation->country_id)->first(),
                        'region' => Region::select(['id', 'country_id', 'name_ru'])->where('id', $moderation->region_id)->with('country:id,name_ru')->first(),
                        'city' => City::select(['id', 'country_id', 'region_id', 'name_ru'])->where('id', $moderation->city_id)->with('country:id,name_ru', 'region:id,country_id,name_ru')->first(),
                    ],
                    'message' => $moderation->geography_message,
                ],

            ];
        } else {
            $moderationObject = null;
        }

        $this->moderation = $moderationObject;
    }

    /**
     * Подготовка изображений с модерацией
     *
     * @param null|string $locale
     */
    public function prepareModerationImage(?string $locale = null)
    {
        $images = PublicationGallery::where('publication_id', $this->id)->orderBy('sorting_rule', 'asc')->select([
            'id',
            'publication_id',
            'image',
            'description',
            'sorting_rule',
            'is_main',
            'moderation_status_id',
            'moderation_message',
        ]);
        if (!is_null($locale))
            $images->whereIn('moderation_status_id', [ModerationStatus::NO_MODERATE, ModerationStatus::MODERATE_OK]);
        $images = $images->get();

        $resultImages = [];
        foreach ($images as $image){
            $resultImages[] = $image->prepareFormatResponseImage($image);
        }

        $this->images = $resultImages;
    }

    /**
     * Документы публикации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicationFiles()
    {
        return $this->hasMany(PublicationFile::class);
    }
}
