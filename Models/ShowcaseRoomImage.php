<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ShowcaseRoom;
use Spatie\Activitylog\Traits\LogsActivity;

class ShowcaseRoomImage extends Model
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

        return "Изображение для витрины номера объекта \"{$this->showcase->object->title_ru}\" было {$event}";
    }

    /**
     * Витрина номера
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function showcase()
    {
        return $this->belongsTo('App\Models\ShowcaseRoom', 'showcase_room_id');
    }
}
