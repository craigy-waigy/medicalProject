<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectViewingCount extends Model
{
    const UPDATED_AT = null;
    protected $primaryKey = 'object_id';

    public $fillable = [
        'object_id'
    ];
}
