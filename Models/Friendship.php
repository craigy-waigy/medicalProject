<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Friendship extends Model
{
    public $table = 'friendship';

    protected $hidden = [
        'token'
    ];

    /**
     * Инициатор запроса на дружбу
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function initiatorUser()
    {
        return $this->belongsTo(User::class, 'initiator_user_id');
    }

    /**
     * Получатель приглашения на дружбу
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function recipientUser()
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * Сообщения
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(PatientMessage::class);
    }
}
