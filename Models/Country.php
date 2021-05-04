<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use App\Models\Region;
use App\Models\City;
use App\Models\User;
use App\Elastic\Elasticsearch;
use Spatie\Activitylog\Traits\LogsActivity;

class Country extends Model
{
    use LogsActivity;

    public $casts = [
        'is_visible' => 'boolean',
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
            $event = 'добавлена';

        elseif ($eventName == 'updated')
            $event = 'обновлена';

        elseif ($eventName == 'deleted')
            $event = 'удалена';
        else
            $event = $eventName;

        return "Страна \"{$this->name_ru}\" была {$event}";
    }

    /**
     * Объекты - санатории
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function objects()
    {
        return $this->hasMany(ObjectPlace::class);
    }

    /**
     * Регионы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function regions()
    {
        return $this->hasMany('App\Models\Region', 'country_id');
    }

    /**
     * Регионы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cities()
    {
        return $this->hasMany('App\Models\City','country_id');
    }

    /**
     * Пользователи
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User', 'country_id');
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class, 'country_id')
            ->select(['country_id','for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
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
            $count = FavoriteGeography::where('user_id', $userId)->where('country_id', $this->id)->count();

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
                'index' => 'countries-ru',
                'type' => 'countries',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_ru),
//                    'description' => strip_tags($model->description_ru),
                    'tags' => $model->tags_ru,
                ],
            ]);
            $elasticsearch->index([
                'index' => 'countries-en',
                'type' => 'countries',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_en),
//                    'description' => strip_tags($model->description_en),
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
                'index' => 'countries-ru'
            ]);
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'countries-en'
            ]);
        });
    }
}
