<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketStatus extends Model
{
    const INCOMING = 1; //входящий тикет
    const CLOSED = 2;   //обработанный тикет
}
