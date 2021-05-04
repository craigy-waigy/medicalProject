<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class ChatRecipient extends Model
{
    public $table = 'crm_chat_recipients';

    public function chat()
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }
}
