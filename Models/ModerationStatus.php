<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationStatus extends Model
{
    public $timestamps = null;
    public const NO_MODERATE = 1; //Контент не предоставлен на модерацию
    public const ON_MODERATE = 2; //Контент находится на модерации
    public const MODERATE_OK = 3; //Контент одобрен модератором
    public const MODERATE_REJECT = 4; //Контент отклонен модератором
}
