<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViewUser extends Model
{
    use SoftDeletes;
    use \Illuminate\Notifications\Notifiable;

    /**
     * Роль пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Объект - санаторий
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function object()
    {
        return $this->hasOne(ObjectPlace::class )->where('is_deleted', '=', false);
    }

    /**
     * Город
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    /**
     * Регион
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Страна
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Партнер
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function partner()
    {
        return $this->hasOne(Partner::class);
    }

    /**
     * Врач санатория
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function sanatoriumDoctor()
    {
        return $this->hasOne(SanatoriumDoctor::class, 'user_id');
    }

}
