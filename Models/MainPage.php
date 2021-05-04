<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class MainPage extends Model
{
    use LogsActivity;

    public $timestamps = null;
    public $table = 'main_page';

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

        return "Главная страница была {$event}";
    }
}
