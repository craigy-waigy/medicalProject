<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class FileStorage extends Model
{
    use LogsActivity;

    const UPDATED_AT = null;
    const FOR_MAIN_PAGE = 'main-page';
    const FOR_MAIN_NEWS = 'main-news-page';
    const FOR_LIST_NEWS = 'list-news-page';
    const FOR_NEWS = 'news-page';
    const FOR_OBJECT_PAGE = 'object-page';
    const FOR_DISEASE = 'disease-page';
    const FOR_MEDICAL_PROFILE = 'medical-profile-page';
    const FOR_THERAPY = 'therapy-page';
    const FOR_COUNTRY = 'country-page';
    const FOR_REGION = 'region-page';
    const FOR_CITY = 'city-page';
    const FOR_SEARCH_OBJECT_PAGE = 'search-object-page';
    const FOR_SEARCH_DISEASE = 'search-disease-page';
    const FOR_SEARCH_MEDICAL_PROFILE = 'search-medical-profile-page';
    const FOR_SEARCH_THERAPY = 'search-therapy-page';
    const FOR_SEARCH_GEO = 'search-geo-page';
    const FOR_FEEDBACK = 'feedback-page';
    const FOR_PARTNERS = 'partners-page';
    const FOR_OFFER = 'offer';
    const FOR_ABOUT = 'about';
    const FOR_PARTNER = 'partner';
    const FOR_PUBLICATION = 'publication';
    const FOR_RECOMMENDATION_CITY = 'recommendation_city';
    const FOR_RECOMMENDATION_REGION = 'recommendation_region';
    const FOR_RECOMMENDATION_COUNTRY = 'recommendation_country';

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

        return "Файл в хранилище был {$event}";
    }
}
