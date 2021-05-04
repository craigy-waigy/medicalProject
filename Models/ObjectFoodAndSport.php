<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectFoodAndSport extends Model
{
    use LogsActivity;

    public $table = 'object_food_and_sports';

    public $casts = [
      'mini_bar' => 'json',
      'foods' => 'json',
      'room_service' => 'json',
      'sport_services' => 'json',
      'other_services' => 'json',
      'ethernet_availability' => 'json',
      'wifi_places' => 'json',
      'general_services' => 'json',
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

        return "Данные объекта \"{$this->object->title_ru}\" по питанию и спорту были {$event}";
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
