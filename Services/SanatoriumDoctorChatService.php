<?php

namespace App\Services;


use App\Events\SanatoriumDoctorNewMessage;
use App\Events\SanatoriumDoctorReadMessage;
use App\Exceptions\ApiProblemException;
use App\Jobs\HasNewMessageSanatoriumDoctorChat;
use App\Libraries\Models\PaginatorFormat;
use App\Models\SanatoriumDoctor;
use App\Models\SanatoriumDoctorChat;
use App\Models\SanatoriumDoctorMessage;
use App\Models\User;
use App\Models\ViewUser;
use App\Notifications\MessageFromPageToSanatoriumDoctor;
use App\Notifications\NewMessageToSanatoriumDoctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class SanatoriumDoctorChatService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * SanatoriumDoctorChat constructor.
     * @param PaginatorFormat $paginatorFormat
     */
    public function __construct(PaginatorFormat $paginatorFormat)
    {
        $this->paginatorFormat = $paginatorFormat;
    }

    /**
     * Получение списка чатов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param int $userId
     * @param null|string $searchKey
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function listChats(int $page, int $rowsPerPage, int $userId, ?string $searchKey = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $user = User::find($userId);

        $qb = ViewUser::where('view_users.is_deleted', false);

        if (!is_null($user->sanatoriumDoctor)){
            $qb->leftJoin('sanatorium_doctor_chat', 'sanatorium_doctor_chat.patient_id', 'view_users.id')
                ->where('sanatorium_doctor_chat.doctor_id', $user->id)
                ->whereNull('sanatorium_doctor_chat.deleted_at')
            ;

            if ( !is_null($searchKey)){
                $qb->when($searchKey, function ($query, $searchKey){
                    $query = $query->whereRaw("lower(view_users.fullname) LIKE '%{$searchKey}%'");
                    $query = $query->OrWhereRaw("lower(view_users.email) LIKE '%{$searchKey}%'");

                    return $query;
                });
            }
            $select = [
                DB::raw("(SELECT count(*) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE ch.doctor_id = {$user->id} AND msg.user_id = view_users.id) as messages_count"),

                DB::raw("(SELECT count(*) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE 
                                msg.is_read = FALSE
                                AND msg.user_id = view_users.id
                                AND msg.user_id <> {$user->id}
                                AND ch.doctor_id = {$user->id}) as new_messages_count"),

                DB::raw("(SELECT max(msg.created_at) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE ch.doctor_id = {$user->id} AND msg.user_id = view_users.id) as last_message"),
            ];
        }
        else {
            $qb->leftJoin('sanatorium_doctor_chat', 'sanatorium_doctor_chat.doctor_id', 'view_users.id')
                ->where('sanatorium_doctor_chat.patient_id', $user->id)
                ->whereNull('sanatorium_doctor_chat.deleted_at')
            ;
            if ( !is_null($searchKey)){
                $qb->when($searchKey, function ($query, $searchKey){
                    $query = $query->whereRaw("lower(view_users.fullname) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(view_users.email) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereHas('sanatoriumDoctor.object', function ($q) use($searchKey){
                        $q->whereRaw("lower(title_ru) LIKE '%{$searchKey}%' OR lower(title_en) LIKE '%{$searchKey}%'") ;
                    });

                    return $query;
                });
            }
            $select = [
                DB::raw("(SELECT count(*) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE ch.patient_id = {$user->id} AND msg.user_id = view_users.id) as messages_count"),

                DB::raw("(SELECT count(*) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE 
                                msg.is_read = FALSE
                                AND msg.user_id = view_users.id
                                AND msg.user_id <> {$user->id}
                                AND ch.patient_id = {$user->id}) as new_messages_count"),

                DB::raw("(SELECT max(msg.created_at) 
                                FROM sanatorium_doctor_messages msg 
                                LEFT JOIN sanatorium_doctor_chat ch ON msg.sanatorium_doctor_chat_id = ch.id 
                                WHERE ch.patient_id = {$user->id} AND msg.user_id = view_users.id) as last_message"),
            ];
        }
        $qb = $this->sanatoriumDoctorData($qb, $locale);

        $qb->select(array_merge([
            'view_users.id',
            'view_users.name',
            'view_users.avatar_url as avatar',
            'view_users.fullname',
            'view_users.last_name',
            'view_users.father_name',
            'view_users.email',
            'sanatorium_doctor_chat.id as sanatorium_doctor_chat_id',
        ], $select));

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)
            ->orderBy('new_messages_count', 'desc')
            ->orderBy('last_message', 'desc')
            ->get();
        $itemsPrepared = [];
        foreach ($items as $item){
            if (!is_null($item->last_message)){
                $timezone = app('config')['app']['timezone'];
                $item->last_message =  (new Carbon($item->last_message, $timezone))->format('Y-m-d\TH:i:sP');
            }
            $itemsPrepared[] = $item;
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $itemsPrepared);
    }

    /**
     * Получение чата
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param int $sanatoriumDoctorChatId
     * @param int $userId
     * @param null|string $searchKey
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function getChat(int $page, int $rowsPerPage, int $sanatoriumDoctorChatId, int $userId,
                            ?string $searchKey = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $sanatoriumDoctorChat = SanatoriumDoctorChat::where('id', $sanatoriumDoctorChatId)
            ->whereRaw("(doctor_id = {$userId} OR patient_id = {$userId})")
            ->first();
        if (is_null($sanatoriumDoctorChat))
            throw new ApiProblemException('Переписка не найдена', 404);

        $qb = SanatoriumDoctorMessage::where('sanatorium_doctor_chat_id', $sanatoriumDoctorChatId)
            ->whereRaw("sanatorium_doctor_chat_id in (
                SELECT id FROM sanatorium_doctor_chat WHERE patient_id = {$userId} OR doctor_id = {$userId}
            )")
        ;
        if (!is_null($searchKey)){
            $qb->whereRaw("lower(message) LIKE '%{$searchKey}%'");
        }
        $qb->with('user:id,fullname,avatar_url as avatar');
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('created_at', 'desc')->get();
        $items->sortBy('created_at');
        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);


        $sanatoriumDoctorChat->patient_id !== $userId ?
            $chatWithUserId = $sanatoriumDoctorChat->patient_id : $chatWithUserId = $sanatoriumDoctorChat->doctor_id;

        $chatWithUser = ViewUser::where('id', $chatWithUserId)
            ->select([
                'id',
                'name',
                'avatar_url as avatar',
                'fullname',
                'last_name',
                'father_name',
                'email'
            ]);
        $chatWithUser = $this->sanatoriumDoctorData($chatWithUser, $locale);
        $chatWithUser = $chatWithUser->first();

        $response['chat_with_user'] = $chatWithUser;

        return $response;
    }

    /**
     * Отправка сообщения
     *
     * @param string $message
     * @param int $sanatoriumDoctorChatId
     * @param int $userId
     * @param null|string $locale
     * @param null|string $password
     * @param bool|null $first
     * @return SanatoriumDoctorMessage|mixed
     * @throws ApiProblemException
     */
    public function sendMessage(string $message, int $sanatoriumDoctorChatId, int $userId, ?string $locale,
                                ?string $password = null, ?bool $first = false)
    {
        $sanatoriumDoctorChat = SanatoriumDoctorChat::find($sanatoriumDoctorChatId);
        if (is_null($sanatoriumDoctorChat))
            throw new ApiProblemException('Переписка не найдена', 404);

        $sanatoriumDoctorMessage = new SanatoriumDoctorMessage;
        $sanatoriumDoctorMessage->sanatorium_doctor_chat_id = $sanatoriumDoctorChatId;
        $sanatoriumDoctorMessage->user_id = $userId;
        $sanatoriumDoctorMessage->message = $message;
        $sanatoriumDoctorMessage->save();

        if ($first){
            $sanatoriumDoctorChat->patient
                ->notify( new MessageFromPageToSanatoriumDoctor($sanatoriumDoctorChat->patient,
                    $sanatoriumDoctorChat->doctor->sanatoriumDoctor, $message, $password) );
        }

        $sanatoriumDoctorMessage = $this->getMessage($sanatoriumDoctorMessage->id, $locale);

        event( new SanatoriumDoctorNewMessage( $sanatoriumDoctorMessage ) );

        HasNewMessageSanatoriumDoctorChat::dispatch($sanatoriumDoctorChat, $sanatoriumDoctorMessage->id)
            ->delay( now()->addMinute(2) );


        return $sanatoriumDoctorMessage;
    }

    /**
     * Пометка сообщения прочитанным
     *
     * @param string $messageId
     * @param int $sanatoriumDoctorChatId
     * @param int $userId
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function readMessage(string $messageId, int $sanatoriumDoctorChatId, int $userId, ?string $locale = null)
    {
        $sanatoriumDoctorChat = SanatoriumDoctorChat::find($sanatoriumDoctorChatId);
        if (is_null($sanatoriumDoctorChat))
            throw new ApiProblemException('Переписка не найдена', 404);

        $sanatoriumDoctorMessage = SanatoriumDoctorMessage::where('id', $messageId)
            ->where('sanatorium_doctor_chat_id', $sanatoriumDoctorChatId)->first();

        if ($sanatoriumDoctorMessage->user_id !== $userId && !$sanatoriumDoctorMessage->is_read){
            $sanatoriumDoctorMessage->is_read = true;
            $sanatoriumDoctorMessage->save();

            $sanatoriumDoctorMessage = $this->getMessage($sanatoriumDoctorMessage->id, $locale);

            event( new SanatoriumDoctorReadMessage( $sanatoriumDoctorMessage ) );

        } else $sanatoriumDoctorMessage = $this->getMessage($sanatoriumDoctorMessage->id, $locale);

        return $sanatoriumDoctorMessage;
    }

    /**
     * Получение сообщения
     *
     * @param int $messageId
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getMessage(int $messageId, string $locale)
    {
        $sanatoriumDoctorMessage = SanatoriumDoctorMessage::where('id', $messageId)
            ->with('user:id,fullname,avatar_url as avater')
            ->first();

        if (is_null($sanatoriumDoctorMessage))
            throw new ApiProblemException('Сообщение не найдено', 404);

        return $sanatoriumDoctorMessage;
    }

    /**
     * Отправка сообщения со страницы санатория
     *
     * @param string $message
     * @param int $doctorId
     * @param array $data
     * @param string $locale
     * @return SanatoriumDoctorMessage|mixed
     * @throws ApiProblemException
     */
    public function sendPatientMessageFromObjectPage(string $message, int $doctorId, array $data, string $locale)
    {
        $sanatoriumDoctor = SanatoriumDoctor::where('user_id', $doctorId)->first();
        if (is_null($sanatoriumDoctor))
            throw new ApiProblemException('Врач не найден', 404);

        $patient = User::where('email', $data['email'])->first();
        if (is_null($patient)){
            $patient = new User;

            foreach ($data as $field => $value){
                $patient->$field = $value;
            }
            $patient->role_id = User::ROLE_USER;
            $patient->confirm_token = str_random(50);

            $password = str_random(8);
            $patient->password = bcrypt($password);

            $patient->save();
        }

        $sanatoriumDoctorChat = SanatoriumDoctorChat::where('object_id', $sanatoriumDoctor->object_id)
            ->where('patient_id', $patient->id)->where('doctor_id', $doctorId)->first();

        if (is_null($sanatoriumDoctorChat)){
            $sanatoriumDoctorChat = new SanatoriumDoctorChat;
            $sanatoriumDoctorChat->object_id = $sanatoriumDoctor->object_id;
            $sanatoriumDoctorChat->patient_id = $patient->id;
            $sanatoriumDoctorChat->doctor_id = $sanatoriumDoctor->user_id;
            $sanatoriumDoctorChat->save();
        }

        return $this->sendMessage($message, $sanatoriumDoctorChat->id, $patient->id, $locale,
            $password ?? null, true);
    }

    /**
     * Добавляем данные доктора пользователю
     *
     * @param $qb
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function sanatoriumDoctorData($qb, ?string $locale)
    {
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $qb->with([
                        'sanatoriumDoctor' => function ($query) { $query->select([
                            'id', 'user_id', 'object_id', 'specializations_ru as specializations', 'languages','online'
                        ]); },
                        'sanatoriumDoctor.object' => function ($query) { $query->select([
                            'id', 'title_ru as title', 'country_id', 'region_id', 'city_id'
                        ])->with(
                            'country:id,name_ru as name',
                            'region:id,name_ru as name',
                            'city:id,name_ru as name'
                        ); },
                    ]);
                    break;

                case 'en' :
                    $qb->with([
                        'sanatoriumDoctor' => function ($query) { $query->select([
                            'id', 'user_id', 'object_id', 'specializations_en as specializations', 'languages','online'
                        ]); },
                        'sanatoriumDoctor.object' => function ($query) { $query->select([
                            'id', 'title_en as title', 'country_id', 'region_id', 'city_id'
                        ])->with(
                            'country:id,name_en as name',
                            'region:id,name_en as name',
                            'city:id,name_en as name'
                        ); },
                    ]);
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
        }

        return $qb;
    }

    /**
     * Удаление переписки
     *
     * @param int $sanatoriumDoctorChatId
     * @param int $userId
     * @throws ApiProblemException
     */
    public function deleteChat(int $sanatoriumDoctorChatId, int $userId)
    {
        $user = User::find($userId);

        $sanatoriumDoctorChat = SanatoriumDoctorChat::where('id',$sanatoriumDoctorChatId);
        if (!is_null($user->sanatoriumDoctor))
            $sanatoriumDoctorChat->where('doctor_id', $user->id);
        else
            $sanatoriumDoctorChat->where('patient_id', $user->id);

        $sanatoriumDoctorChat = $sanatoriumDoctorChat->first();

        if (is_null($sanatoriumDoctorChat))
            throw new ApiProblemException('Переписка не найдена', 404);

        $sanatoriumDoctorChat->messages()->delete();
        $sanatoriumDoctorChat->delete();
    }
}
