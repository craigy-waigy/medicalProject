<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class PublicationGallery extends Model
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
            $event = 'добавлено';

        elseif ($eventName == 'updated')
            $event = 'обновлено';

        elseif ($eventName == 'deleted')
            $event = 'удалено';
        else
            $event = $eventName;

        return "Изображение в галлереи публикации партнера \"{$this->publication->title_ru}\" {$event}";
    }

    /**
     * Публикация
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
    /**
     * Подготовка формата ответа для изображения
     *
     * @param $image
     * @return mixed
     */
    public function prepareFormatResponseImage($image)
    {
        $moderation = [
            'status_id' => $image->moderation_status_id,
            'message' => $image->moderation_message,
        ];
        unset($image->moderation_status_id);
        unset($image->moderation_message);
        $image->moderation = (object)$moderation;

        return $image;
    }
}
