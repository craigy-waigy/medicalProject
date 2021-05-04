<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\NewsScope;

class Role extends Model
{

    protected $fillable = [
        'name',
        'slug',
        'is_editable'
    ];

    protected $casts = [
        'permissions' => 'json'
    ];

    protected $guarded = [];
    protected static $logUnguarded = true;

    /**
     * LogsActivity, название события
     *
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created')
            $event = 'добавлена';

        elseif ($eventName == 'updated')
            $event = 'обновлена';

        elseif ($eventName == 'deleted')
            $event = 'удалена';
        else
            $event = $eventName;

        return "Роль \"{$this->name}\" была {$event}";
    }

    /**
     * Пользователи
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('App\Models\User');
    }

    /**
     * Область видимости
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function scope()
    {
        return $this->hasOne('App\Models\NewsScope')->select(['name', 'slug']);
    }

    /**
     * Заполнение разрешений пользователя
     */
    public function hydratePermissions()
    {
        $permissions = Permission::orderBy('sort_order')->get();
        $role = Role::where('id', $this->id)->first();

        $hydrationPermissions = [];
        foreach ($permissions as $permission){
            $hydrationPermissions[$permission->slug] = in_array($permission->slug, $role->permissions);
        }
        $this->permissions = (object)$hydrationPermissions;

    }
}
