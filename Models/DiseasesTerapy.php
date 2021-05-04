<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class DiseasesTerapy extends Model
{
    use LogsActivity;

    protected $table = 'diseases_therapy';
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

        return "Связь заболевание: \"{$this->disease->name_ru}\" и метод лечения: \"{$this->therapy->name_ru}\" была {$event}";
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
     * Метод лечения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}
