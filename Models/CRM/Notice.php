<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    public $table = 'crm_notices';

    public const TYPE_LEAD_MESSAGE = 'lead_message';
}
