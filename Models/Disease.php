<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Therapy;
use App\Models\MedicalProfile;
use App\Elastic\Elasticsearch;
use Spatie\Activitylog\Traits\LogsActivity;

class Disease extends Model
{
    use LogsActivity;

    protected $table = 'diseases';

    public $timestamps = null;

    protected $hidden = [ 'pivot' ];

    protected $casts = [
        'tags_ru' => 'json',
        'tags_en' => 'json',
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
            $event = 'добавлено';

        elseif ($eventName == 'updated')
            $event = 'обновлено';

        elseif ($eventName == 'deleted')
            $event = 'удалено';
        else
            $event = $eventName;

        return "Заболевание \"{$this->name_ru}\" было {$event}";
    }

    /**
     * Родителькое заболевание
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function parentInfo()
    {
        return $this->hasOne(Disease::class, 'id', 'parent');
    }

    /**
     * Методы лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function therapies()
    {
        return $this->belongsToMany('App\Models\Therapy', 'diseases_therapy',
            'disease_id', 'therapy_id');
    }

    /**
     * Публичные методы лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function therapiesPublic()
    {
        return $this->belongsToMany('App\Models\Therapy', 'diseases_therapy',
            'disease_id', 'therapy_id')
            ->where('therapy.active', '=', true);
    }

    /**
     * Медицинские профили лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicalProfiles()
    {
        return $this->belongsToMany('App\Models\MedicalProfile', 'disease_medical_profile',
            'disease_id', 'medical_profile_id');
    }

    /**
     * Публичные профили лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function medicalProfilesPublic()
    {
        return $this->belongsToMany('App\Models\MedicalProfile', 'disease_medical_profile',
            'disease_id', 'medical_profile_id')
            ->where('medical_profiles.active', '=', true);
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class, 'disease_id')
            ->select(['disease_id','for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Файлы в хранилище
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(FileStorage::class);
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
            $count = FavoriteDisease::where('user_id', $userId)->where('disease_id', $this->id)->count();

            if ($count > 0) $this->is_favorite = true;
            else $this->is_favorite = false;

        } else {
            $this->is_favorite = null;
        }

        return $this;
    }

    protected static function boot()
    {
        parent::boot();

        /**
         * Обновляем индекс если поменяли запись
         */
        static::saved(function ($model){
            $elasticsearch = new Elasticsearch;
            $elasticsearch->index([
                'index' => 'diseases-ru',
                'type' => 'disease',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_en),
                    'description' => strip_tags($model->desc_ru),
                    'tags' => $model->tags_ru,
                ],
            ]);
            $elasticsearch->index([
                'index' => 'diseases-en',
                'type' => 'disease',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_en),
                    'description' => strip_tags($model->desc_en),
                    'tags' => $model->tags_en,
                ],
            ]);
        });

        /**
         * Удаляем из индекса при удалении записи
         */
        static::deleted(function ($model){
            $elasticsearch = new Elasticsearch;
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'diseases-ru'
            ]);
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'diseases-en'
            ]);
        });
    }
}
