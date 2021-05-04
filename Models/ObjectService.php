<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ServiceCategory;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectService extends Model
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

        return "Связь объект: \"{$this->object->title_ru}\" и услуга: \"{$this->service->name_ru}\" была {$event}";
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

    /**
     * Услуга
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Категории
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Models\ServiceCategory', 'id');
    }
}
