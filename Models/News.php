<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SeoInformation;
use Spatie\Activitylog\Traits\LogsActivity;

class News extends Model
{
    use LogsActivity;

    public $casts = [
      'scope'  => 'json'
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

        return "Новость \"{$this->title_ru}\" была {$event}";
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne('App\Models\SeoInformation', 'news_id')
            ->select(['news_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Файлы
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function files()
    {
        return $this->hasMany(StorageFile::class);
    }

    /**
     * Файлы в хранилище
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storageFiles()
    {
        return $this->hasMany(FileStorage::class);
    }

    /**
     * Изображения новости
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(NewsImage::class);
    }
}
