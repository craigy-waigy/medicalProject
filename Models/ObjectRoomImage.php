<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectRoomImage extends Model
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

        return "Изображение планировки номера для объекта \"{$this->objectRoom->object->title_ru}\" было {$event}";
    }

    /**
     * Планировка номера
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function objectRoom()
    {
        return $this->belongsTo(ObjectRoom::class);
    }
}
