<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Friendship;
use App\Models\PatientMessage;
use App\Models\User;
use App\Models\ViewUser;
use App\Notifications\FriendshipAcceptedNotification;
use App\Notifications\NewFriendshipRequestNotification;
use App\Notifications\RemoveFriendshipInitiatorNotification;
use App\Notifications\RemoveFriendshipRecipientNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class FriendshipService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * PartnerService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Получение и поиск друзей
     *
     * @param int $userId
     * @param int $page
     * @param int $rowsPerPage
     * @param array|null $sorting
     * @param null|string $searchKey
     * @param array|null $params
     * @return array
     */
    public function getFriendship(int $userId, int $page, int $rowsPerPage, ?array $sorting = null,
                                  ?string $searchKey = null, ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = ViewUser::where('view_users.role_id', '=', User::ROLE_USER)
            ->leftJoin('friendship', function($join) use($userId){
                $join->on('view_users.id', '=', 'friendship.initiator_user_id')
                    ->orOn('view_users.id', '=', 'friendship.recipient_user_id');
            })
            ->whereRaw("(friendship.initiator_user_id = {$userId} OR friendship.recipient_user_id = {$userId}) AND view_users.id <> {$userId} AND friendship.removed = FALSE")
            ->when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $query = $query->orderBy($key, $value);
                }
                return $query;
            } else {
                return $query->orderBy('friendship.updated_at', 'desc');
            }
        });

        if ( !is_null($searchKey)){
            $qb->when($searchKey, function ($query, $searchKey){
                $query = $query->whereRaw("lower(fullname) LIKE '%{$searchKey}%'");
                $query = $query->OrWhereRaw("lower(email) LIKE '%{$searchKey}%'");

                return $query;
            });
        }

        $qb->select([
            'view_users.id',
            'view_users.name',
            'view_users.avatar_url as avatar',
            'view_users.fullname',
            'view_users.last_name',
            'view_users.father_name',
            'view_users.email',
            'friendship.accepted',
            'friendship.initiator_user_id',
            'friendship.id as friendship_id',
            DB::raw("(SELECT count(*) as count FROM patient_messages WHERE patient_messages.friendship_id = friendship.id) as messages_count"),
            DB::raw("(SELECT count(*) as count FROM patient_messages WHERE patient_messages.friendship_id = friendship.id AND patient_messages.user_id <> {$userId} AND patient_messages.is_read = FALSE) as new_messages_count"),
            DB::raw("(SELECT MAX(created_at) as last_message FROM patient_messages WHERE patient_messages.friendship_id = friendship.id GROUP BY patient_messages.friendship_id) as last_message")
        ]);
        if (isset($params['accepted'])){
            $qb->where('accepted', (bool)$params['accepted']);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        $prepareItems = [];
        foreach ($items as $item){
            if ($item->initiator_user_id === $userId)
                $item->incoming = false;
            else
                $item->incoming = true;

            if (!is_null($item->last_message)){
                $timezone = app('config')['app']['timezone'];
                $item->last_message =  (new Carbon($item->last_message, $timezone))->format('Y-m-d\TH:i:sP');
            }
            unset($item->initiator_user_id);
            $prepareItems[] = $item;
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $prepareItems);
    }

    /**
     * Запрос дружбы
     *
     * @param int $initiatorUserId
     * @param string $name
     * @param string $email
     * @throws ApiProblemException
     */
    public function addFriendship(int $initiatorUserId, string $name, string $email)
    {
        $initiatorUser = User::find($initiatorUserId);
        $recipientUser = $this->getRecipientUser($name, $email, $initiatorUserId);
        $password = $recipientUser['password'];
        $recipientUser = $recipientUser['user'];

        $friendship = Friendship::where([ //Если раньше дружба была разорвана то берем её
            ['initiator_user_id', $initiatorUser->id],
            ['recipient_user_id', $recipientUser->id],
        ])->orWhere([
            ['initiator_user_id', $recipientUser->id],
            ['recipient_user_id', $initiatorUser->id],
        ])->where('removed', true)->first();

        if (!is_null($friendship)){
            $friendship->accepted = false;
            $friendship->removed = false;
        } else {
            $friendship = new Friendship();
        }

        $friendship->initiator_user_id = $initiatorUser->id;
        $friendship->recipient_user_id = $recipientUser->id;
        $friendship->token = str_random(50);
        $friendship->save();

        $recipientUser->notify( new NewFriendshipRequestNotification($recipientUser, $initiatorUser, $friendship, $password) );
    }

    /**
     * Получение или создание пользователя
     *
     * @param string $name
     * @param string $email
     * @param int $initiatorUserId
     * @return array
     * @throws ApiProblemException
     */
    public function getRecipientUser(string $name, string $email, int $initiatorUserId)
    {
        $email = mb_strtolower($email);

        $userOtherRoleCount = User::where('email', $email)->where('role_id', '<>', User::ROLE_USER)->count();
        if ($userOtherRoleCount > 0)
            throw new ApiProblemException('Указанный email уже занят и используется другими участниками проекта', 422);

        $user = User::where('email', $email)->first();
        $password = null;

        if (!is_null($user)){
            $friendshipCount = Friendship::whereRaw("
            ((initiator_user_id = {$initiatorUserId} AND recipient_user_id = {$user->id})
            OR
            (recipient_user_id = {$initiatorUserId} AND initiator_user_id = {$user->id}))
            
            ")->where('removed', false)->count();

            if ($friendshipCount > 0)
                throw new ApiProblemException('Дружба уже существует', 422);
        }

        if (is_null($user)){
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->role_id = User::ROLE_USER;
            $user->confirm_token = str_random(50);

            $password = str_random(8);
            $user->password = bcrypt($password);

            $user->save();
        }

        return [
            'user' =>$user,
            'password' => $password,
        ];
    }

    /**
     * Подтверждение дружбы
     *
     * @param null|string $token
     * @param int|null $friendshipId
     * @param int|null $recipientUserId
     * @return array
     * @throws ApiProblemException
     */
    public function acceptFriendship(?string $token, ?int $friendshipId = null, ?int $recipientUserId = null)
    {
        if (!is_null($token))
            $friendship = Friendship::where('token', $token)->first();

        elseif (!is_null($friendshipId) && !is_null($recipientUserId))
            $friendship = Friendship::where('id', $friendshipId)->where('recipient_user_id', $recipientUserId)->first();
        else
            throw new ApiProblemException('Отпралено не достаточно данных', 422);

        if (is_null($friendship))
            throw new ApiProblemException('Дружба не найдена', 404);

        if ($friendship->accepted)
            throw new ApiProblemException('Дружба уже подтверждена', 422);

        $recipientUser = $friendship->recipientUser;
        $initiatorUser = $friendship->initiatorUser;

        $recipientUser->notify( new FriendshipAcceptedNotification( $initiatorUser ));
        $initiatorUser->notify( new FriendshipAcceptedNotification( $recipientUser ));

        $friendship->token = null;
        $friendship->accepted = true;
        $friendship->save();

        $patientMessage = new PatientMessage();
        $patientMessage->friendship_id = $friendship->id;
        $patientMessage->user_id = $initiatorUser->id;
        $patientMessage->message = $recipientUser->name . ' и ' . $initiatorUser->name . ' теперь друзья';

        //TODO: событие о новом сообщении

        if (!is_null($token)){
            $recipientUser->email_confirmed = true;
            $recipientUser->save();
        }

        $tokenResult = $recipientUser->createToken('web');
        $token = $tokenResult->token;
        $token->save();

        return [
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => (new \DateTime($tokenResult->token->expires_at))->getTimestamp()
        ];
    }

    /**
     * Удаление дружбы
     *
     * @param int $initiatorUserId
     * @param int $friendshipId
     * @throws ApiProblemException
     */
    public function removeFriendship(int $initiatorUserId, int $friendshipId)
    {
        $initiatorUser = User::find($initiatorUserId);
        if (is_null($initiatorUser))
            throw new ApiProblemException('Пользователь - инициятор не найден', 404);

        $friendship = Friendship::where('id', $friendshipId)
            ->whereRaw("initiator_user_id = {$initiatorUser->id} OR recipient_user_id = {$initiatorUser->id}")
            ->where('removed', false)->first();

        if (is_null($friendship))
            throw new ApiProblemException('Дружба с этим пользователем не найдена', 404);

        if ($friendship->removed)
            throw new ApiProblemException('Дружба уже отменена', 422);

        $friendship->removed = true;
        $friendship->save();

        $recipientUser = $friendship->recipientUser;

        $initiatorUser->notify( new RemoveFriendshipInitiatorNotification($recipientUser) );
        $recipientUser->notify( new RemoveFriendshipRecipientNotification($initiatorUser) );
    }
}
