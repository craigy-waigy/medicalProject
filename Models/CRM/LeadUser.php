<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LeadUser extends Model
{
    public $table = 'crm_lead_user';

    public function lead(){
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
