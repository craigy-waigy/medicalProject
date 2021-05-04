<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReservationStatus extends Model
{
    public $timestamps = null;

    const INCOMING = 1;  //Входящий запрос от пользователя
    const CONFIRMED = 2; //Подтвержденная бронь, состоявшаяся поездка
}
