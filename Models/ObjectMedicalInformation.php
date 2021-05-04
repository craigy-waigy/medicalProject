<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectMedicalInformation extends Model
{
    use LogsActivity;

    public $table = 'object_medical_informations';

    public $casts = [
      'climatic_factors' => 'json',
      'water' => 'json',
      'pump_room' => 'json',
      'healing_mud' => 'json',
      'certified_personal' => 'json',
      'operation_in_object' => 'json',
      'drinking_water_plumbing' => 'json',
      'effective_months' => 'json',
      'voucher_without_accommodation' => 'boolean',
      'exist_reanimation' => 'boolean',
    ];

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
            $event = 'добавлены';

        elseif ($eventName == 'updated')
            $event = 'обновлены';

        elseif ($eventName == 'deleted')
            $event = 'удалены';
        else
            $event = $eventName;

        return "Данные по медицине объекта \"{$this->object->title_ru}\" были {$event}";
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
