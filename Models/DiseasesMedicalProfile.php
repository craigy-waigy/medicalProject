<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class DiseasesMedicalProfile extends Model
{
    use LogsActivity;

    protected $table = 'disease_medical_profile';
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
            $event = 'добавлена';

        elseif ($eventName == 'updated')
            $event = 'обновлена';

        elseif ($eventName == 'deleted')
            $event = 'удалена';
        else
            $event = $eventName;

        return "Связь заболевание: \"{$this->disease->name_ru}\" и мед. профиль: \"{$this->medicalProfile->name_ru}\" была {$event}";
    }

    /**
     * Заболевание
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disease()
    {
        return $this->belongsTo(Disease::class);
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
