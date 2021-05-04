<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class About extends Model
{
    use LogsActivity;

    public $timestamps = null;

    public $table = 'about';

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

        return "Страница О проекте: \"{$this->title_ru}\" была {$event}";
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class);
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
     * Родительский каталог
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function aboutParent()
    {
        return $this->hasOne(About::class, 'id', 'parent');
    }

}
