<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class MainBanner extends Model
{
    use LogsActivity;

    public $casts = [
      'banner_resolutions' => 'json'
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

        return "Главный баннер был {$event}";
    }
}
