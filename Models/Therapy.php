<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Disease;
use App\Models\TherapyImages;
use App\Elastic\Elasticsearch;
use Spatie\Activitylog\Traits\LogsActivity;

class Therapy extends Model
{
    use LogsActivity;

    protected $table = 'therapy';

    public $timestamps = null;

    protected $hidden = ['pivot'];

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
            $event = 'добавлен';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Метод лечения \"{$this->name_ru}\" был {$event}";
    }

    /**
     * Заболевания
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diseases()
    {
        return $this->belongsToMany('App\Models\Disease',
            'diseases_therapy', 'therapy_id', 'disease_id');
    }

    /**
     * Публичные заболевания
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function diseasesPublic()
    {
        return $this->belongsToMany('App\Models\Disease',
            'diseases_therapy', 'therapy_id', 'disease_id')
            ->where('diseases.active', '=', true);
    }

    public function images()
    {
        return $this->hasMany('App\Models\TherapyImages', 'therapy_id');
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class, 'therapy_id')
            ->select(['therapy_id','for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
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
            $count = FavoriteTherapy::where('user_id', $userId)->where('therapy_id', $this->id)->count();

            if ($count > 0) $this->is_favorite = true;
            else $this->is_favorite = false;

        } else {
            $this->is_favorite = null;
        }

        return $this;
    }

    /**
     * Объекты
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function objects()
    {
        return $this->belongsToMany(ObjectPlace::class, 'object_therapies',
            'therapy_id', 'object_id');
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
                'index' => 'therapies-ru',
                'type' => 'therapy',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_ru),
                    'description' => strip_tags($model->desc_ru),
                    'tags' => $model->tags_ru,
                ],
            ]);
            $elasticsearch->index([
                'index' => 'therapies-en',
                'type' => 'therapy',
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
                'index' => 'therapies-ru'
            ]);
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'therapies-en'
            ]);
        });
    }
}
