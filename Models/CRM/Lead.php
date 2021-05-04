<?php

namespace App\Models\CRM;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public $table = 'crm_leads';

    public const TYPE_QUESTION = 'question';
    public const TYPE_ORDER = 'order';
    public const TYPE_DOCTOR = 'doctor';
    public const TYPE_PARTNER = 'partnership';

    public const STATUS_IN_WORK = 0; //Не обработан

    public function leadUser(){
        return $this->hasOne(LeadUser::class, 'lead_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
