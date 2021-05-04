<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ObjectPlace;
use App\Models\ModerationStatus;
use Spatie\Activitylog\Traits\LogsActivity;

class ModerationObject extends Model
{
    use LogsActivity;

    protected $casts = [
        'services' => 'json',
        'contacts' => 'json',
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

        return "Данные по модерации объекта были {$event}";
    }

    /**
     * Объект
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function object()
    {
        return $this->belongsTo('App\Models\ObjectPlace', 'object_id');
    }

    /**
     * Статус описания
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function descriptionStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'description_status_id');
    }

    /**
     * Статус звездности
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function starsStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'stars_status_id');
    }

    /**
     * Статус описания оплаты
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentDescriptionStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'payment_description_status_id');
    }

    /**
     * Статус списка документов
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentsStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'documents_status_id');
    }

    /**
     * Статус противопоказаний
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contraindicationsStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'contraindications_status_id');
    }

    /**
     * Статус услуг
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function servicesStatus()
    {
        return $this->belongsTo('App\Models\ModerationStatus', 'services_status_id');
    }
}
