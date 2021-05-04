<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Feedback;
use App\Models\Partner;
use App\Models\PatientMessage;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\Role;
use App\Models\User;
use App\Models\ObjectPlace;
use App\Models\ViewUser;
use App\Notifications\EmailConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class UserService extends Model
{
    protected $paginatorFormat;

    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Получение и поиск пользователя
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param array|null $params
     * @param null|string $searchBy
     * @param null|string $searchKey
     * @return array
     */
    public function getAll(int $page, int $rowsPerPage,?array $sorting,?string $searchBy, ?string $searchKey, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $filter = [];
        $filter[] = ['is_deleted', false];

        $queryBulder = ViewUser::where($filter)
            ->when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $key = implode('_', explode('.', $key));
                        $query = $query->orderBy($key, $value);
                    }
                } else {
                    $query->orderBy('updated_at', 'asc');
                }
                return $query;
            });
        if (!is_null($searchKey)){
            if (is_numeric($searchKey)){
                $queryBulder->where('id', (int)$searchKey);
            } else {
                $searchKey = mb_strtolower($searchKey);
                $queryBulder->whereRaw("(
                            LOWER(fullname) LIKE '%{$searchKey}%' OR
                            LOWER(email) LIKE '%{$searchKey}%' OR
                            LOWER(city_name_ru) LIKE '%{$searchKey}%'
                )");
            }
        }

        if (!empty($params['role_id'])){
            $queryBulder->where('role_id', (int)$params['role_id']);
        }
        $queryBulder->with('role:id,name', 'country:id,name_ru', 'region:id,name_ru', 'city:id,name_ru');

        $total = $queryBulder->count();
        $items = $queryBulder->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Добавление нового юзера
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data)
    {
        $newUser = new User();

        $newUser->name = $data['name'];
        $newUser->email = $data['email'];
        $newUser->password = bcrypt($data['password']);
        $newUser->father_name = $data['father_name'] ?? null;
        $newUser->last_name = $data['last_name'] ?? null;
        $newUser->role_id = $data['role_id'] ?? User::ROLE_USER;
        $newUser->phone = $data['phone'] ?? null;
        $newUser->confirm_token = str_random(50);
        $newUser->other_email = $data['other_email'] ?? null;
        $newUser->city_id = $data['city_id'] ?? null;
        $newUser->region_id = $data['region_id'] ?? null;
        $newUser->country_id = $data['country_id'] ?? null;
        $newUser->save();

        $newUser->notify(new EmailConfirmation( $newUser->confirm_token ));

        return $newUser;
    }

    /**
     * Редактирование пользователя
     *
     * @param array $data
     * @param int $userId
     * @return mixed
     */
    public function updateUser(array $data, int $userId)
    {
        $user = User::find($userId);
        foreach($data as $field=>$value) {
            if ($field == 'password') {
                $user->password = bcrypt($value);
            } elseif ($field == 'email'){

                if ( $user->email !== $value){
                    $user->email = $value;
                    $user->email_confirmed = false;
                    $user->confirm_token = str_random(50);
                }
            } else {
                $user->$field = $value;
            }
        }
        $user->save();

       return $user;
    }

    /**
     * Назначение роли пользователю
     *
     * @param int $userId
     * @param array $data
     * @throws ApiProblemException
     */
    public function setRole(int $userId, array $data)
    {
        $user = User::find($userId);
        if (is_null($user))
            throw new ApiProblemException('Пользователь не найден', 404);

        $this->removeUserFromAllRelations($userId);

        switch ($data['role_id']) {

            case User::ROLE_OBJECT_OWNER:
                $object = ObjectPlace::find($data['object_id']);
                if (is_null($object))
                    throw new ApiProblemException('Объект не найден', 404);

                if (!is_null($object->user)){
                    $object->user->role_id = User::ROLE_USER;
                    $object->user->save();
                }

                $object->user_id = $userId;
                $object->save();

                User::where('id', $userId)->update([
                    'city_id' => $object->city_id,
                    'region_id' => $object->region_id,
                    'country_id' => $object->country_id,
                ]);

                break;

            case User::ROLE_PARTNER :
                $partner = Partner::find($data['partner_id']);
                if (is_null($partner))
                    throw new ApiProblemException('Партнер не найден', 404);

                if (!is_null($partner->user)){
                    $partner->user->role_id = User::ROLE_USER;
                    $partner->user->save();
                }

                $partner->user_id = $userId;
                $partner->save();

                break;

            case User::ROLE_USER:
                break;
        }
        $user->role_id = $data['role_id'];
        $user->save();
    }

    public function removeUserFromAllRelations(int $userId)
    {
        ObjectPlace::where('user_id', $userId)->update([
            'user_id' => null
        ]);
        Partner::where('user_id', $userId)->update([
            'user_id' => null
        ]);
    }

    /**
     * Получение правил валидации для установки роли
     *
     * @param int $roleId
     * @return array
     */
    public function getRules(int $roleId)
    {
        switch ($roleId){
            case User::ROLE_OBJECT_OWNER :
                $rules = ['object_id' => 'required|integer'];
                break;

            case User::ROLE_PARTNER :
                $rules = ['partner_id' => 'required|integer'];
                break;

            default :
                $rules = [];
                break;
        }

        return array_merge([ 'role_id' => 'required|integer' ], $rules);
    }

    /**
     * Удаление пользователя
     *
     * @param int $userId
     */
    public function deleteUser(int $userId)
    {
        $user = User::find($userId);
        $user->is_deleted = true;
        $user->deleted_at = (new \DateTime('now'))->format('Y-m-d h:i:s');
        $user->save();
    }

    /**
     * Получение пользователя
     *
     * @param int $userId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getUser(int $userId)
    {
        $filter = [];
        $filter[] = ['is_deleted', false];
        $filter[] = ['id', $userId];

        $user = User::where($filter)->with(
            'role',
            'country',
            'region',
            'city',
            'object:id,user_id,title_ru,city_id,region_id,country_id',
            'object.city:id,name_ru',
            'object.region:id,name_ru',
            'object.country:id,name_ru',
            'partner.type:id,name_ru,image',
            'partner:id,user_id,partner_type_id,organisation_short_name_ru,logo'
        )->first();
        if (is_null($user)) throw new ApiProblemException('Пользователь не найден', 404);

        return $user;
    }

    /**
     * Сохранение аватарки
     *
     * @param int $userId
     * @param Request $request
     * @return null
     * @throws ApiProblemException
     */
    public function setAvatar(int $userId, Request $request)
    {
        $user = User::find($userId);
        if (is_null($user)) throw new ApiProblemException('Пользователь не найден', 404);

        if ($request->hasFile('avatar')){
            Storage::delete('avatars/' . basename($user->avatar_url));
            $path = $request->file('avatar')->store('avatars');
            $user->avatar_url = Storage::url($path);
            $user->save();

            $x = $request->get('x');
            $y = $request->get('y');
            $height = $request->get('height');
            $width = $request->get('width');

            if (!is_null($x) && !is_null($y) && !is_null($width) && !is_null($height) ) {
                $imagePath = storage_path('app' . DIRECTORY_SEPARATOR . 'avatars') . DIRECTORY_SEPARATOR  . basename($path);
                Image::make($imagePath)->crop($width, $height, $x, $y)->save($imagePath);
            }

            return $user;
        } else return null;
    }

    /**
     * Удаление аватара
     *
     * @param int $userId
     * @throws ApiProblemException
     */
    public function deleteAvatar(int $userId)
    {
        $user = User::find($userId);
        if (is_null($user)) throw new ApiProblemException('Пользователь не найден', 404);
        Storage::delete('avatars/' . basename($user->avatar_url));
        $user->avatar_url = null;
        $user->save();
    }

    /**
     * Получение личного кабинета пользователя
     *
     * @return array
     */
    public function getAccount()
    {
        $userRole = Auth::user()->role_id;
        $userId = Auth::user()->id;

        $profile = User::with('role', 'country', 'region', 'city')
            ->where('id', $userId)
            ->first();

        switch ($userRole)
        {
            case User::ROLE_ADMIN:
                $account = [
                    'profile' => $profile,
                ];
                break;

            case User::ROLE_USER:
                $account = [
                    'profile' => $profile,
                    'patient' => null,
                ];
                break;

            case User::ROLE_OBJECT_OWNER:

                $object = ObjectPlace::where([
                    ['user_id', $userId],
                    ['is_deleted', false],
                ])->with(
                    'moderationObject',
                    'moderationObject.descriptionStatus',
                    'moderationObject.starsStatus',
                    'moderationObject.paymentDescriptionStatus',
                    'moderationObject.documentsStatus',
                    'moderationObject.contraindicationsStatus',
                    'moderationObject.servicesStatus',
                    'services',
                    'moderationServices',
                    'medicalProfiles',
                    'moderationMedicalProfile',
                    'therapies',
                    'moderationTherapies',
                    'country:id,name_ru,name_en',
                    'region:id,name_ru,name_en',
                    'city:id,name_ru,name_en'
                )->first();

                $account = [
                    'profile' => $profile,
                    'object' => $object
                ];
                break;

            case User::ROLE_PARTNER :
                $partner = Partner::where('id', Auth::user()->partner->id)->with('type:id,name_ru as name')->first();
                $partner->prepareModerationImage(null);
                $partner->hydrateTypePublications(null);

                $account = [
                    'profile' => $profile,
                    'partner' => $partner
                ];
                break;

            default :
                $account = [
                    'profile' => $profile,
                    'account' => 'undefined'
                ];
                break;
        }

        return $account;
    }

    /**
     * Смена пароля
     *
     * @param array $data
     * @return bool
     */
    public function changePassword(array $data)
    {
        $user = Auth::user();
        $passChek = Hash::check($data['current_password'], $user->password);
        if ($passChek) {
            $user->password = Hash::make($data['password']);
            $user->save();
        }

        return $passChek;
    }

    /**
     * Получение пользователя при авторизации
     *
     * @param int $userId
     * @return mixed
     */
    public function me(int $userId)
    {
        $user = User::find($userId);
        $user->role->hydratePermissions();

        return $user;
    }

    /**
     * Получение профиля
     *
     * @param int $userId
     * @return User|\Illuminate\Database\Eloquent\Builder|Model|null|object
     * @throws ApiProblemException
     */
    public function getInfo(int $userId)
    {
        $user = User::with('role:id,name,slug', 'country:id,name_ru', 'region:id,name_ru', 'city:id,name_ru')
            ->where('id', $userId)
            ->select([
                'id',
                'name',
                'email',
                'email_verified_at',
                'father_name',
                'last_name',
                'avatar_url',
                'role_id',
                'email_confirmed',
                'phone',
                'created_at',
                'updated_at',
                'is_deleted',
                'deleted_at',
                'country_id',
                'region_id',
                'city_id',
                'other_email',
                'lon',
                'lat',
                'self_registered',
                'updated_at',
                'created_at',
                DB::raw("
                (
                    SELECT init.count + rec.count as count
                    FROM
                    (
                        SELECT count(*) as count
                        FROM friendship fr 
                        LEFT JOIN users u ON fr.initiator_user_id = u.id
                        WHERE
                        (fr.initiator_user_id = users.id OR fr.recipient_user_id = users.id) 
                         AND fr.removed = FALSE
                         AND u.role_id = 2
                         AND u.deleted_at IS NULL
                         AND u.id <> users.id
                     ) init,
                    (
                        SELECT count(*) as count
                        FROM friendship fr 
                        LEFT JOIN users u ON fr.recipient_user_id = u.id
                        WHERE
                        (fr.initiator_user_id = users.id OR fr.recipient_user_id = users.id) 
                         AND fr.removed = FALSE
                         AND u.role_id = 2 
                         AND u.deleted_at IS NULL
                         AND u.id <> users.id
                     ) rec
                ) as friendship_count
                "),
            ])
            ->first();

        if ($user->is_deleted)
            throw new ApiProblemException('Ваш профиль заблокирован, обратитесь к администрации ресурса', 403);

        $user = $this->addDataToUser( $user );

        return $user;
    }

    /**
     * Дополнение данными
     *
     * @param $user
     * @return mixed
     */
    public function addDataToUser( $user )
    {
        $newMessagesCount = 0;

        $newMessagesCount += DB::table('patient_messages')
            ->leftJoin('friendship', 'patient_messages.friendship_id', '=', 'friendship.id')
            ->where('patient_messages.is_read', '=', false)
            ->where('patient_messages.user_id', '<>', $user->id)
            ->whereRaw("(friendship.initiator_user_id = {$user->id} OR friendship.recipient_user_id = {$user->id})")
            ->count();

        $newMessagesCount += DB::table('sanatorium_doctor_messages')
            ->where('sanatorium_doctor_messages.is_read', '=', false)
            ->where('sanatorium_doctor_messages.user_id', '<>', $user->id)
            ->whereRaw("sanatorium_doctor_chat_id IN(SELECT id FROM sanatorium_doctor_chat WHERE patient_id = {$user->id} OR doctor_id = {$user->id})")
            ->count();

        $newMessagesCount += DB::table('crm_messages')
            ->join('crm_chat_recipients', 'crm_chat_recipients.chat_id', '=', 'crm_messages.chat_id')
            ->whereIn('crm_chat_recipients.recipient_id', [$user->id])
            ->where('crm_messages.sender_id', '<>', $user->id)
            ->where('crm_messages.new', '=', 1)
            ->count();

        $user->new_messages_count = $newMessagesCount;

        if (!is_null($user->object)){
            $feedbackCount = Feedback::where('object_id', $user->object->id)->whereNotNull('comment')->count();
            $user->feedback_count = $feedbackCount;
            unset($user->object);
        }

        //поездки
        if ($user->role->id == User::ROLE_USER){
            $tripCount = Reservation::where('email', $user->email)
                ->where('reservation_status_id', ReservationStatus::CONFIRMED)
                ->count();
            $user->trip_count = $tripCount;
        }

        return $user;
    }
}
