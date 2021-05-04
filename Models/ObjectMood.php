<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectMood extends Model
{
    use LogsActivity;

    protected $guarded = [];

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

        return "ObjectMood \"{$this->mood_id}\" \"{$this->object_id}\"  был {$event}";
    }

    /**
     * Объект - Mood
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function mood()
    {
        return $this->belongsTo(Mood::class, 'mood_id');
    }

    /**
     * Объект - санаторий
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function object()
    {
        return $this->belongsTo(ObjectPlace::class, 'object_id');
    }
}
