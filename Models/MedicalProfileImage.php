<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MedicalProfile;
use Spatie\Activitylog\Traits\LogsActivity;

class MedicalProfileImage extends Model
{
    use LogsActivity;

    public $timestamps  = null;

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

        return "Изображение для мед. профиля \"{$this->medicalProfile->name_ru}\" было {$event}";
    }

    /**
     *
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function medicalProfile()
    {
        return $this->belongsTo('App\Models\MedicalProfile', 'medical_profile_id');
    }

}
