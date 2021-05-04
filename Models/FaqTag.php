<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class FaqTag extends Model
{
    use LogsActivity;

    public $timestamps = false;

    protected $guarded = [];
    protected static $logUnguarded = true;

}
