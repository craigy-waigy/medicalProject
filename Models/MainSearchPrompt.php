<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Elastic\Elasticsearch;

class MainSearchPrompt extends Model
{
    protected $casts = [
        'tags' => 'array'
    ];

   public $guarded = [];
}
