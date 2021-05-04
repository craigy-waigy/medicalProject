<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackSanatoriumAnswer extends Model
{
    /**
     * Отзыв на который этот ответ)
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function feedback()
    {
        return $this->belongsTo(Feedback::class, 'feedback_id');
    }
}
