<?php

namespace App\Models;

use \Spatie\Activitylog\Models\Activity as SpatieActivity;

class Activity extends SpatieActivity
{
    /**
     * Пользователь вносивший изменения
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(ViewUser::class, 'causer_id');
    }
}
