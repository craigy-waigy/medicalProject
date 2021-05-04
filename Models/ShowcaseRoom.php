<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use App\Models\ShowcaseRoomImage;
use Spatie\Activitylog\Traits\LogsActivity;

class ShowcaseRoom extends Model
{
    use LogsActivity;

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
            $event = 'добавлена';

        elseif ($eventName == 'updated')
            $event = 'обновлена';

        elseif ($eventName == 'deleted')
            $event = 'удалена';
        else
            $event = $eventName;

        return "Витрина номера объекта \"{$this->object->title_ru}\" была {$event}";
    }

    /**
     * Объект
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function object()
    {
        return $this->belongsTo('App\Models\ObjectPlace');
    }

    /**
     * Витрина номеров
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany('App\Models\ShowcaseRoomImage', 'showcase_room_id')
            ->orderBy('sorting_rule', 'asc');
    }
}
