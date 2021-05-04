<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ModerationPartner extends Model
{
    use LogsActivity;

    protected $casts = [
      'telephones' => 'json'
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
            $event = 'добавлены';

        elseif ($eventName == 'updated')
            $event = 'обновлены';

        elseif ($eventName == 'deleted')
            $event = 'удалены';
        else
            $event = $eventName;

        return "Данные по модерации партнёра были {$event}";
    }
}
