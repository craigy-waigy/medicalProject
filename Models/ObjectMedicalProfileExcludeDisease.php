<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class ObjectMedicalProfileExcludeDisease extends Model
{
    use LogsActivity;

    public $timestamps = null;

    protected $guarded = [];
    protected static $logUnguarded = true;

}
