<?php

namespace App\Services;


use App\Events\FriendshipNewMessage;
use App\Events\FriendshipReadMessage;
use App\Exceptions\ApiProblemException;
use App\Jobs\HasNewMessageFriendship;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Friendship;
use App\Models\PatientMessage;
use App\Models\User;
use App\Models\ViewUser;

class PatientChatService
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
     * Получение чата
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param int $friendshipId
     * @param int $userId
     * @param null|string $searchKey
     * @return array
     * @throws ApiProblemException
     */
    public function getChat(int $page, int $rowsPerPage, int $friendshipId, int $userId, ?string $searchKey = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = PatientMessage::where('friendship_id', $friendshipId);
        $qb->whereRaw("friendship_id in(
            SELECT id FROM friendship WHERE initiator_user_id = {$userId} OR recipient_user_id = {$userId}
        )");

        if (!is_null($searchKey)){
            $qb->whereRaw("lower(message) LIKE '%{$searchKey}%'");
        }
        $qb->with('user:id,fullname,avatar_url as avatar');

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('created_at', 'desc')->get();
        $items->sortBy('created_at');
        $friendship = Friendship::find($friendshipId);
        if (is_null($friendship))
            throw new ApiProblemException('Дружба не существует', 404);
        if ($friendship->initiator_user_id !== $userId){
            $friend = ViewUser::where('id', $friendship->initiator_user_id)->select([
                'id',
                'name',
                'avatar_url as avatar',
                'fullname',
                'last_name',
                'father_name',
                'email'
            ])->first();
        } else {
            $friend = ViewUser::where('id', $friendship->recipient_user_id)->select([
                'id',
                'name',
                'avatar_url as avatar',
                'fullname',
                'last_name',
                'father_name',
                'email'
            ])->first();
        }
        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
        $response['friend'] = $friend;

        return $response;
    }

    /**
     * Отправка сообщения
     *
     * @param string $message
     * @param int $friendshipId
     * @param int $userId
     * @return mixed
     * @throws ApiProblemException
     */
    public function sendMessage(string $message, int $friendshipId, int $userId)
    {
        $this->checkFriendship($friendshipId, $userId);

        $patientMessage = new PatientMessage();
        $patientMessage->friendship_id = $friendshipId;
        $patientMessage->user_id = $userId;
        $patientMessage->message = strip_tags($message);
        $patientMessage->save();
        $patientMessage = PatientMessage::where('id', $patientMessage->id)
            ->with('user:id,fullname,avatar_url as avatar')
            ->first();

        event( new FriendshipNewMessage( $patientMessage ) );

        $friendship = Friendship::find($friendshipId);
        HasNewMessageFriendship::dispatch($friendship, $patientMessage->id)->delay( now()->addMinute(2) );

        return $patientMessage;
    }

    /**
     * Пометка сообщения как прочитанное
     *
     * @param int $messageId
     * @param int $friendshipId
     * @param int $userId
     * @return mixed
     * @throws ApiProblemException
     */
    public function readMessage(int $messageId,  int $friendshipId, int $userId)
    {
        $this->checkFriendship($friendshipId, $userId);
        $patientMessage = PatientMessage::where('id', $messageId)
            ->with('user:id,fullname,avatar_url as avatar')
            ->where('friendship_id', $friendshipId)
            ->first();
        if ($patientMessage->user_id != $userId && !$patientMessage->is_read ){
            $patientMessage->is_read = true;
            $patientMessage->save();

            $patientMessage = PatientMessage::where('id', $patientMessage->id)
                ->with('user:id,fullname,avatar_url as avatar')->first();

            event(new FriendshipReadMessage($patientMessage));
        }

        return $patientMessage;
    }

    /**
     * Проверка прав на переписку пользователя.
     *
     * @param int $friendshipId
     * @param int $userId
     * @throws ApiProblemException
     */
    public function checkFriendship(int $friendshipId, int $userId)
    {
        $friendship = Friendship::where('id', $friendshipId)
            ->whereRaw("initiator_user_id = {$userId} OR recipient_user_id = {$userId}")->first();
        if (is_null($friendship))
            throw new ApiProblemException('Переписка не найдена', 404);

        if ($friendship->removed)
            throw new ApiProblemException('Дружба отменена с этим пользователем, вы не можете писать в чат', 403);

        if (!$friendship->accepted)
            throw new ApiProblemException('Дружба не подтверждена с этим пользователем, вы не можете писать в чат', 403);
    }
}
