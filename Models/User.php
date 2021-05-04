<?php

namespace App\Models;

use App\Exceptions\ApiProblemException;
use App\Models\CRM\LeadUser;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use App\Models\Role;
use App\Models\ObjectPlace;
use App\Models\City;
use App\Models\Region;
use App\Models\Country;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes, LogsActivity;

    protected $permissionsList;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'father_name',
        'last_name',
        'city',
        'role_id'
    ];

    protected static $logFillable = true;

    /**
     * Роль администратора
     */
    const ROLE_ADMIN = 1;

    /**
     * Роль пользователя
     */
    const ROLE_USER = 2;

    /**
     * Роль владельца объекта/санатория
     */
    const ROLE_OBJECT_OWNER = 3;

    /**
     * Роль партнера проекта
     */
    const ROLE_PARTNER = 4;

    const SLUG_SANATORIUM_DOCTOR = 'sanatorium_doctor';

    /**
     * Получение ID роли по slug
     *
     * @param string $slug
     * @return mixed
     * @throws ApiProblemException
     */
    public static function getRoleId(string $slug)
    {
        $role = Role::where('slug', $slug)->first();
        if (is_null($role))
            throw new ApiProblemException('Роль не найдена', 422);

        return $role->id;
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'confirm_token',
        'password_reset_token',
    ];

    /**
     * Сохраняем в нижнем регистре email
     *
     * @param $value
     */
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = mb_strtolower($value);
    }

    /**
     * Роль пользователя
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    /**
     * Объект - санаторий
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function object()
    {
        return $this->hasOne('App\Models\ObjectPlace')->where('is_deleted', '=', false);
    }

    /**
     * Город
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function city()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }

    /**
     * Регион
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Models\Region', 'region_id');
    }

    /**
     * Страна
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
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
        return $this->hasOne(SanatoriumDoctor::class);
    }

    /**
     * Менеджеры лидов.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function leadUsers()
    {
        return $this->hasMany(LeadUser::class, 'user_id');
    }

    /**
     * LogsActivity, название события
     *
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created')
            $event = 'создан';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Пользователь: {$this->email} был {$event}";
    }

}
