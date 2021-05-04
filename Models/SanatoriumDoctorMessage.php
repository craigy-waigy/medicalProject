<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanatoriumDoctorMessage extends Model
{
    use SoftDeletes;

    public const UPDATED_AT = null;

    /**
     * Автор сообщения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ViewUser::class, 'user_id');
    }

    /**
     * Чат
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function chat()
    {
        return $this->belongsTo(SanatoriumDoctorChat::class);
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
