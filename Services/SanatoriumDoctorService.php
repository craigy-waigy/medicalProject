<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ObjectPlace;
use App\Models\SanatoriumDoctor;
use App\Models\User;
use App\Models\ViewUser;
use App\Notifications\NewSanatoriumDoctor;

class SanatoriumDoctorService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * SanatoriumDoctorService constructor.
     * @param PaginatorFormat $paginatorFormat
     */
    public function __construct(PaginatorFormat $paginatorFormat)
    {
        $this->paginatorFormat = $paginatorFormat;
    }

    /**
     * Добавление врача в санаторий
     *
     * @param int $objectId
     * @param array $data
     * @return mixed
     * @throws ApiProblemException
     */
    public function addDoctor(int $objectId, array $data)
    {
        $object = ObjectPlace::find($objectId);
        if (is_null($object))
            throw new ApiProblemException('Объект не найден', 404);
        $user = User::where('email', $data['email'])->first();
        if (is_null($user)){
            $user = new User;
            $user->email = $data['email'];
            $user->name = $data['name'];

            $user->role_id = User::getRoleId(User::SLUG_SANATORIUM_DOCTOR);
            $user->confirm_token = str_random(50);

            $password = str_random(8);
            $user->password = bcrypt($password);
            $user->self_registered = false;

            $user->name = $data['name'];
            $user->father_name = $data['father_name'] ?? null;
            $user->last_name = $data['last_name'] ?? null;
            $user->other_email = $data['other_email'] ?? null;

            $user->country_id = $object->country_id;
            $user->region_id = $object->region_id;
            $user->city_id = $object->city_id;

            $user->email_confirmed = false;

            $user->save();

        } else {
            throw new ApiProblemException('Пользователь с указанными данными уже существует', 422);
        }

        if (!empty($data['specializations_ru']) && !is_array($data['specializations_ru']))
            $data['specializations_ru'] = json_decode($data['specializations_ru'], true);

        if (!empty($data['specializations_en']) && !is_array($data['specializations_en']))
            $data['specializations_en'] = json_decode($data['specializations_en'], true);

        if (!empty($data['languages']) && !is_array($data['languages']))
            $data['languages'] = json_decode($data['languages'], true);

        if (is_null($user->sanatoriumDoctor)){
            $user->sanatoriumDoctor = new SanatoriumDoctor;
        }
        $user->sanatoriumDoctor->user_id = $user->id;
        $user->sanatoriumDoctor->object_id = $object->id;
        $user->sanatoriumDoctor->specializations_ru = $data['specializations_ru'] ?? [];
        $user->sanatoriumDoctor->specializations_en = $data['specializations_en'] ?? [];
        $user->sanatoriumDoctor->languages = $data['languages'] ?? [];
        $user->sanatoriumDoctor->save();

        $user->notify( new NewSanatoriumDoctor($user, $object->user, $password ?? null) );

        return $this->getDoctor($user->id, $object->id);
    }

    /**
     * Редактирование врача санатория
     *
     * @param int $userId
     * @param int $objectId
     * @param array $data
     * @return mixed
     * @throws ApiProblemException
     */
    public function editDoctor(int $userId, int $objectId, array $data)
    {
        $object = ObjectPlace::find($objectId);
        if (is_null($object))
            throw new ApiProblemException('Объект не найден', 404);

        $user = User::where('id', $userId)->first();
        if (is_null($user))
            throw new ApiProblemException('Пользователь не найден', 404);

        foreach ($data as $filed => $value){
            if ( in_array($filed, ['name', 'father_name', 'last_name', 'other_email']) ){
                $user->$filed = $value;
            }
        }
        $user->save();

        if (!empty($data['specializations_ru']) && !is_array($data['specializations_ru']))
            $data['specializations_ru'] = json_decode($data['specializations_ru'], true);

        if (!empty($data['specializations_en']) && !is_array($data['specializations_en']))
            $data['specializations_en'] = json_decode($data['specializations_en'], true);

        if (!empty($data['languages']) && !is_array($data['languages']))
            $data['languages'] = json_decode($data['languages'], true);

        foreach ($data as $filed => $value){
            if ( in_array($filed, ['specializations_ru', 'specializations_en', 'languages', 'other_email', 'online']) ){
                if ($filed == 'online' && $value){
                    activity()->disableLogging();
                    $user->sanatoriumDoctor->online = true;
                    $user->sanatoriumDoctor->online_expiries = (new \DateTime('now')) //Лимит в течении кот. должен подтверждать флаг online
                        ->modify('+5 minutes')
                        ->format('Y-m-d H:i:s');
                } elseif ($filed == 'online' && !$value){
                    $user->sanatoriumDoctor->online = false;
                    $user->sanatoriumDoctor->online_expiries = null;
                } else {
                    $user->sanatoriumDoctor->$filed = $value;
                }
            }
        }
        $user->sanatoriumDoctor->save();

        return $this->getDoctor($user->id, $object->id);
    }

    /**
     * Удаление врача санатория
     *
     * @param int $userId
     * @param int $objectId
     * @throws ApiProblemException
     */
    public function removeDoctor(int $userId, int $objectId)
    {
        $user = User::where('id', $userId)->first();
        if (is_null($user))
            throw new ApiProblemException('Пользователь не найден', 404);

        if (is_null($user->sanatoriumDoctor))
            throw new ApiProblemException('Врач не существует', 404);

        if ($user->sanatoriumDoctor->object_id !== $objectId)
            throw new ApiProblemException('Врач не принадлежит указанному санаторию', 404);

        $user->sanatoriumDoctor()->delete();
        $user->delete();
    }

    /**
     * Получение списка врачей в санатории
     *
     * @param int $objectId
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @param array|null $params
     * @return array
     */
    public function listDoctors(int $objectId, int $page, int $rowsPerPage, ?array $sorting = null,
                                ?string $searchKey = null, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = ViewUser::whereHas('sanatoriumDoctor', function ( $q ) use( $objectId ) {
            $q->where('object_id', $objectId);
        })
            ->with('sanatoriumDoctor')
            ->when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {
                    foreach ($sorting as $key => $value) {
                        if ( in_array($key, ['name', 'father_name', 'last_name', 'other_email', 'fullname', 'email']) ){
                            $query = $query->orderBy($key, $value);
                        }
                    }
                    return $query;
                } else {
                    return $query->orderBy('updated_at', 'desc');
                }
            })
            ->when($searchKey, function ($query, $searchKey){
                if (!is_null($searchKey)){
                    $query->whereRaw("lower(fullname) LIKE '%{$searchKey}%' OR lower(email) LIKE '%{$searchKey}%'");
                }

                return $query;
            })
            ->select([
                'id',
                'email',
                'name',
                'father_name',
                'last_name',
                'fullname',
                'avatar_url as avatar',
        ]);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение врача санатория
     *
     * @param int $userId
     * @param int|null $objectId
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getDoctor(int $userId, ?int $objectId = null, ?string $locale = null )
    {
        $user = ViewUser::where('id', $userId)
            ->select([
                'id',
                'email',
                'name',
                'father_name',
                'last_name',
                'fullname',
                'avatar_url as avatar',
            ]);

        if (!is_null($locale))
            switch ($locale){
                case 'ru' :
                    $user->with(['sanatoriumDoctor' => function($q){
                        $q->select('id', 'user_id', 'object_id', 'specializations_ru as specializations', 'languages', 'online')
                            ->with(['object' => function($q){
                                $q->select(['id', 'title_ru as title', 'country_id', 'region_id', 'city_id'])
                                    ->with('country:id,name_ru as name')
                                    ->with('region:id,name_ru as name')
                                    ->with('city:id,name_ru as name')
                                ;
                            }])
                        ;
                    }]);
                    break;

                case 'en' :
                    $user->with(['sanatoriumDoctor' => function($q){
                        $q->select('id', 'user_id', 'object_id', 'specializations_en as specializations', 'languages', 'online')
                            ->with(['object' => function($q){
                                $q->select(['id', 'title_en as title', 'country_id', 'region_id', 'city_id'])
                                    ->with('country:id,name_en as name')
                                    ->with('region:id,name_en as name')
                                    ->with('city:id,name_en as name')
                                ;
                            }])
                        ;
                    }]);
                    break;

                default :
                    throw new ApiProblemException('Неподдерживаемая локаль', 422);
            }
        else {
            $user->with(['sanatoriumDoctor' => function($q){
                $q->with(['object' => function($q){
                        $q->select(['id', 'title_ru as title', 'country_id', 'region_id', 'city_id'])
                            ->with('country:id,name_ru as name')
                            ->with('region:id,name_ru as name')
                            ->with('city:id,name_ru as name')
                        ;
                    }])
                ;
            }]);
        }

        $user = $user->first();

        if (is_null($user))
            throw new ApiProblemException('Пользователь не найден', 404);

        if (is_null($user->sanatoriumDoctor))
            throw new ApiProblemException('Врач не существует', 404);

        if ($user->sanatoriumDoctor->object_id !== $objectId && !is_null($objectId))
            throw new ApiProblemException('Врач не принадлежит указанному санаторию', 404);

        return $user;
    }
}
