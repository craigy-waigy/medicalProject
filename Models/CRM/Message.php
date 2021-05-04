<?php

namespace App\Models\CRM;

use App\Models\User;
use App\Models\ViewUser;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $table = 'crm_messages';

    public $casts = [
        'new' => 'boolean'
    ];

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function user()
    {
        return $this->belongsTo(ViewUser::class, 'sender_id');
    }
}
