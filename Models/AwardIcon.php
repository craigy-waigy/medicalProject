<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AwardIcon extends Model
{
    use LogsActivity;

    protected $guarded = [];

    protected static $logUnguarded = true;

    protected $hidden = [
        'pivot'
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
            $event = 'добавлено';

        elseif ($eventName == 'updated')
            $event = 'обновлено';

        elseif ($eventName == 'deleted')
            $event = 'удалено';
        else
            $event = $eventName;

        return "Изображение к странице \"О проекте\" было {$event}";
    }
}
