<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Service;
use Spatie\Activitylog\Traits\LogsActivity;

class ServiceCategory extends Model
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

        return "Категория услуг \"{$this->name_ru}\" была {$event}";
    }

    /**
     * Услуги
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany('App\Models\Service', 'service_category_id');
    }

    /**
     * Активные услуги
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function servicesPublic()
    {
        return $this->hasMany('App\Models\Service', 'service_category_id')
            ->where('active', true);
    }
}
