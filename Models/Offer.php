<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SeoInformation;
use Spatie\Activitylog\Traits\LogsActivity;

class Offer extends Model
{
    use LogsActivity;

    public $casts = [
        'scope' => 'json',
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

        return "Спецпредложение \"{$this->title_ru}\" было {$event}";
    }

    /**
     * SEO информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne('App\Models\SeoInformation', 'offer_id')
            ->select(['offer_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
                'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }

    /**
     * Изображения спец предложения
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(OfferImage::class);
    }
}
