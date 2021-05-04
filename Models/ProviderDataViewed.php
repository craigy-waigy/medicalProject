<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ProviderDataViewed extends Model
{
    use LogsActivity;

    public $table = 'provider_data_viewed';
    protected $guarded = [];
    public $casts = [
        'viewed_fields' => 'json',
    ];


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

        return "ProviderDataViewed c provider_id = \"{$this->provider_id}\" и object_id = \"{$this->object_id}\"  был {$event}";
    }

}
