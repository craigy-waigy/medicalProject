<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Therapy;
use Spatie\Activitylog\Traits\LogsActivity;

class TherapyImages extends Model
{
    use LogsActivity;

    public $timestamps = null;

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

        return "Изображение для метода лечения \"{$this->therapy->name_ru}\" было {$event}";
    }

    /**
     * Метод лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function therapy()
    {
        return $this->belongsTo('App\Models\Therapy', 'therapy_id');
    }
}
