<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectMedicalProfile extends Model
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

        return "Связь заболевание: \"{$this->object->title_ru}\" и мед. профиль: \"{$this->medicalProfile->name_ru}\" была {$event}";
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
     * Мед. профиль
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicalProfile()
    {
        return $this->belongsTo(MedicalProfile::class);
    }
}
