<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class PatientMessage extends Model
{
    public const UPDATED_AT = null;

    /**
     * Пользователь отправивщий сообщение
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ViewUser::class);
    }

    /**
     * Добавляем часовой пояс к сообщениям
     *
     * @param $value
     * @return string
     */
    public function getCreatedAtAttribute( $value )
    {
        $timezone = app('config')['app']['timezone'];
       return  (new Carbon($value, $timezone))->format('Y-m-d\TH:i:sP');
    }
}
