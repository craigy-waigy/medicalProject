<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SanatoriumDoctorChat extends Model
{
    use SoftDeletes;

    public $table = 'sanatorium_doctor_chat';

    /**
     * Пациент
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    /**
     * Врач санатория
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function messages()
    {
        return $this->hasMany(SanatoriumDoctorMessage::class);
    }
}
