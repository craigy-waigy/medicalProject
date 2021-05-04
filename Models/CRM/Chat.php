<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    public $table = 'crm_chats';

    public function lead()
    {
        return $this->hasOne(Lead::class, 'chat_id');
    }
}
