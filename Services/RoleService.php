<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Permission;
use App\Models\Role;
use FontLib\TrueType\Collection;
use Illuminate\Support\Facades\Auth;

class RoleService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * AboutService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Получение списка
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @return array
     */
    public function search(int $page, int $rowsPerPage, ?array $sorting, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Role::when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $query = $query->orderBy($key, $value);
                }
                return $query;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });
        $qb->when($searchKey, function ($query, $searchKey){
            if ( !is_null($searchKey)) {
                $query = $query->orWhere('name', 'like', "%{$searchKey}%");
                $query = $query->orWhere('description', 'like', "%{$searchKey}%");

                return $query;
            }
        });
        $qb->select(['id', 'name', 'description', 'slug']);

        if(Auth::user()->role->slug == 'kontrakter') {
            //Если у пользователя роль контрактера, то он может назначать роль только объекта или пациента
            $qb->whereIn('slug', ['object', 'patient']);

        } elseif (Auth::user()->role->slug != 'admin'){
            //Пользователи не админы не должны назначать админов
            $qb->whereNotIn('slug', [ 'admin' ]);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение роли
     *
     * @param int $roleId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $roleId)
    {
        $role = Role::find($roleId);
        if (is_null($role))
            throw new ApiProblemException('Роль не найдена', 404);
        $role->hydratePermissions();

        return $role;
    }

    /**
     * Добавление роли
     *
     * @param array $data
     * @return mixed
     * @throws ApiProblemException
     */
    public function add(array $data)
    {
        $role = new Role();
        $role->slug = str_slug($data['name']);
        foreach ($data as $filed => $value){
            if ($filed == 'permissions')
                $this->savePermissions($role, $value);
            else
                $role->$filed = $value;
        }
        $role->save();

        return $this->get($role->id);
    }

    /**
     * Редактирование роли
     *
     * @param array $data
     * @param int $roleId
     * @return mixed
     * @throws ApiProblemException
     */
    public function edit(array $data, int $roleId)
    {
        $role = Role::find($roleId);
        if (is_null($role))
            throw new ApiProblemException('Роль не найдена', 404);
        foreach ($data as $filed => $value){
            if ($filed == 'permissions')
                $this->savePermissions($role, $value);
            else
                $role->$filed = $value;
        }
        $role->save();

        return $this->get($role->id);
    }

    /**
     * Удаление роли
     *
     * @param int $roleId
     * @throws ApiProblemException
     */
    public function delete(int $roleId)
    {
        $role = Role::find($roleId);
        if (is_null($role))
            throw new ApiProblemException('Роль не найдена', 404);
        if ($role->users()->count() > 0)
            throw new ApiProblemException('Роль не может быть удалена пока она назначена пользователям', 422);
        $role->delete();
    }

    /**
     * Сохранение разрешений
     *
     * @param array $permissions
     */
    public function savePermissions( $role, array $permissions)
    {
        $preparedPermissions = [];
        foreach ($permissions as $key => $save){
            if ($save)
                $preparedPermissions[] = $key;
        }
        $role->permissions = $preparedPermissions;
        $role->save();
    }

    /**
     * Получение списка разрешений
     *
     * @return array
     */
    public function getPermissions()
    {
        $permissions = Permission::select(['name','description','slug'])->orderBy('sort_order')->get();

        return $permissions;
    }
}
