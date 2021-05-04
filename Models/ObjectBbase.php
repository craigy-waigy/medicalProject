<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectBbase extends Model
{
    use LogsActivity;

    public $table = 'object_bbase';
    public $timestamps = false;
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

        return "Объект - бронебазы \"{$this->object->title_ru}\" было {$event}";
    }

    /**
     * Санатории
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function object()
    {
        return $this->belongsTo('App\Models\ObjectPlace', 'object_id');
    }
}
