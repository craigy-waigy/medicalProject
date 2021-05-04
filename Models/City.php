<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use App\Models\Country;
use App\Models\Region;
use App\Models\User;
use App\Elastic\Elasticsearch;
use Spatie\Activitylog\Traits\LogsActivity;

class City extends Model
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
            $event = 'добавлен';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Город \"{$this->name_ru}\" был {$event}";
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
     * Страна
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country', 'country_id');
    }

    /**
     * Активная страна
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publicCountry()
    {
        return $this->belongsTo('App\Models\Country', 'country_id')
            ->where('is_visible', '=', true);
    }

    /**
     * Регион
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }

    /**
     * Активный регион
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publicRegion()
    {
        return $this->belongsTo('App\Models\Region', 'region_id')
            ->where('is_visible', '=', true);
    }

    public function users()
    {
        return $this->hasMany('App\Models\User', 'city_id');
    }


    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class, 'city_id')
            ->select(['city_id','for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
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
            $count = FavoriteGeography::where('user_id', $userId)->where('city_id', $this->id)->count();

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
                'index' => 'cities-ru',
                'type' => 'cities',
                'id' => $model->id,
                'body' => [
                    'name' => strip_tags($model->name_ru),
//                    'description' => strip_tags($model->description_ru),
                    'tags' => $model->tags_ru,
                ],
            ]);
            $elasticsearch->index([
                'index' => 'cities-en',
                'type' => 'cities',
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
                'index' => 'cities-ru'
            ]);
            $elasticsearch->delete([
                'id' => $model->id,
                'index' => 'cities-en'
            ]);
        });
    }
}
