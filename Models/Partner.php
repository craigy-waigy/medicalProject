<?php

namespace App\Models;

use App\Exceptions\ApiProblemException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Partner extends Model
{
    use SoftDeletes, LogsActivity;

    public $casts = [
      'telephones' => 'json'
    ];

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
            $event = 'добавлен';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Партнер \"{$this->organisation_short_name_ru}\" был {$event}";
    }

    /**
     * Пользователь. Владелец аккаунта партнера
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Тип партнера
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(PartnerType::class, 'partner_type_id');
    }

    /**
     * Отмодерированная галлерея
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicImages()
    {
        return $this->hasMany(PartnerGallery::class)
            ->whereIn('moderation_status_id', [ ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE ]);
    }

    public function images()
    {
        return $this->hasMany(PartnerGallery::class);
    }

    /**
     * Отмодерированные публикации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function moderatedPublication()
    {
        $now = (new \DateTime('now'))->format('Y-m-d h:i:s');
        return $this->hasMany(Publication::class)
            ->whereIn('moderation_status_id', [ ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE])
            ->where('publications.published_at', '<=', $now);
    }

    /**
     * Публикации портнера
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Сео информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class)->select(['partner_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
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
     * Документы партнера
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function partnerFiles()
    {
        return $this->hasMany(PartnerFile::class);
    }

    /**
     * Подготовка изображений с модерацией
     *
     * @param null|string $locale
     */
    public function prepareModerationImage(?string $locale = null)
    {
        $images = PartnerGallery::where('partner_id', $this->id)->orderBy('sorting_rule', 'asc')->select([
            'id',
            'partner_id',
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
     * Заполнение публикаций по типам
     *
     * @param null|string $locale
     * @throws ApiProblemException
     */
    public function hydrateTypePublications(?string $locale)
    {
        $now = (new \DateTime('now'))->format('Y-m-d h:i:s');
        $publicationTypes = PublicationType::all();
        $countTypes = [];
        foreach ($publicationTypes as $publicationType){
            if (is_null($locale)){
                $countTypes[] = [
                  'publication_type_id' => $publicationType->id,
                  'type' => $publicationType->name_ru,
                  'alias' => $publicationType->alias,
                  'count' => Publication::where('partner_id', $this->id)->where('publication_type_id', $publicationType->id)
                    ->count()
                ];
            } else {
                switch ($locale){
                    case 'ru' :
                        $countTypes[] = [
                            'publication_type_id' => $publicationType->id,
                            'type' => $publicationType->name_ru,
                            'alias' => $publicationType->alias,
                            'count' => Publication::where('active', true)->where('partner_id', $this->id)->where('published_at', '<=', $now)
                                ->where('publication_type_id', $publicationType->id)
                                ->whereIn('moderation_status_id', [ ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE ])
                                ->count()
                        ];
                        break;

                    case 'en' :
                        $countTypes[] = [
                            'publication_type_id' => $publicationType->id,
                            'type' => $publicationType->name_en,
                            'alias' => $publicationType->alias,
                            'count' => Publication::where('active', true)->where('partner_id', $this->id)->where('published_at', '<=', $now)
                                ->where('partner_id', $this->id)->where('publication_type_id', $publicationType->id)
                                ->whereIn('moderation_status_id', [ ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE ])
                                ->count()
                        ];
                        break;

                    default :
                        throw new ApiProblemException('Не поддерживаемая локаль', 422);
                }
            }
            $this->publication_types = $countTypes;
        }
    }

    /**
     * Данные модерации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function moderationPartner()
    {
        return $this->hasOne(ModerationPartner::class);
    }

    /**
     * Подготовка формата модерации
     */
    public function hydrateModeration()
    {
        $publication = Partner::find($this->id);
        $moderation = $publication->moderationPartner;
        if (!is_null($moderation)){
            $moderationObject = [
                'manager_name_ru' => [
                    'status_id' => $moderation->manager_name_status_id,
                    'value' => $moderation->manager_name_ru,
                    'message' => $moderation->manager_name_message,
                ],
                'manager_name_en' => [
                    'status_id' => $moderation->manager_name_status_id,
                    'value' => $moderation->manager_name_en,
                    'message' => $moderation->manager_name_message,
                ],
                'organisation_short_name_ru' => [
                    'status_id' => $moderation->organisation_short_name_status_id,
                    'value' => $moderation->organisation_short_name_ru,
                    'message' => $moderation->organisation_short_name_message,
                ],
                'organisation_short_name_en' => [
                    'status_id' => $moderation->organisation_short_name_status_id,
                    'value' => $moderation->organisation_short_name_en,
                    'message' => $moderation->organisation_short_name_message,
                ],
                'organisation_full_name_ru' => [
                    'status_id' => $moderation->organisation_full_name_status_id,
                    'value' => $moderation->organisation_full_name_ru,
                    'message' => $moderation->organisation_full_name_message,
                ],
                'organisation_full_name_en' => [
                    'status_id' => $moderation->organisation_full_name_status_id,
                    'value' => $moderation->organisation_full_name_en,
                    'message' => $moderation->organisation_full_name_message,
                ],
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
                'address_ru' => [
                    'status_id' => $moderation->address_status_id,
                    'value' => $moderation->address_ru,
                    'message' => $moderation->address_message,
                ],
                'address_en' => [
                    'status_id' => $moderation->address_status_id,
                    'value' => $moderation->address_en,
                    'message' => $moderation->address_message,
                ],
                'telephones' => [
                    'status_id' => $moderation->telephones_status_id,
                    'value' => $moderation->telephones,
                    'message' => $moderation->telephones_message,
                ],
                'email' => [
                    'status_id' => $moderation->email_status_id,
                    'value' => $moderation->email,
                    'message' => $moderation->email_message,
                ],
                'mail_address_ru' => [
                    'status_id' => $moderation->mail_address_status_id,
                    'value' => $moderation->mail_address_ru,
                    'message' => $moderation->mail_address_message,
                ],
                'mail_address_en' => [
                    'status_id' => $moderation->mail_address_status_id,
                    'value' => $moderation->mail_address_en,
                    'message' => $moderation->mail_address_message,
                ],
            ];
        } else {
            $moderationObject = null;
        }

        $this->moderation = $moderationObject;
    }

}
