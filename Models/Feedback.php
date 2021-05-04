<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;

class Feedback extends Model
{
    use SoftDeletes, LogsActivity;

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
            $event = 'добавлен';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Отзыв объекта: \"{$this->object->title_ru}\" был {$event}";
    } //TODO: не логируются изменения

    /**
     * Изображения прикрепленные к отзыву
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function images()
    {
        return $this->hasMany(FeedbackImage::class);
    }

    /**
     * Поездка (подтвержденная бронь)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id');
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
     * Ответ санатория
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sanatoriumAnswer()
    {
        return $this->hasOne(FeedbackSanatoriumAnswer::class, 'feedback_id');
    }
}
