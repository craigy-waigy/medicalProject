<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectInfrastructure extends Model
{
    use LogsActivity;

    public $table = 'object_infrastructures';

    public $casts = [
        'season_period' => 'json',
        'months_peak' => 'json',
        'months_lows' => 'json',
        'contingent' => 'json',
        'territory' => 'json',
        'reservoir' => 'json',
        'pools' => 'json',
        'parking' => 'json',
        'markets' => 'json',
        'pharmacies' => 'json',
        'effective_months' => 'json',
        'working_by_season' => 'boolean',
        'has_electro_cars' => 'boolean',
        'infrastructure_for_disabilities' => 'json',
        'elevators' => 'json',
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

        return "Данные по ифраструктуре объекта \"{$this->object->title_ru}\" были {$event}";
    }

    /**
     * Объект санаторий
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function object()
    {
        return $this->hasOne(ObjectPlace::class, 'object_id');
    }
}
