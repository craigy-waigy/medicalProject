<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\CRM\Chat;
use App\Models\CRM\ChatRecipient;
use App\Models\CRM\Message;
use App\Models\CRM\Notice;
use Illuminate\Support\Facades\DB;

class SupportCRMService
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
     * @param string|null $searchKey
     * @param string|null $locale
     * @return array
     */
    public function listChats(int $page, int $rowsPerPage, int $userId, ?string $searchKey = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $qb = Chat::query();
        $qb->select([
           'crm_chats.id as chat_id',
           'view_users.name',
           'view_users.last_name',
           'view_users.father_name',
           'view_users.fullname',
           'view_users.email',
           'view_users.avatar_url as avatar',
           'crm_leads.client_question as question',
           DB::raw("(SELECT count(*) FROM crm_messages WHERE chat_id = crm_chats.id AND sender_id <> {$userId}) as messages_count"),
           DB::raw("(SELECT count(*) FROM crm_messages WHERE chat_id = crm_chats.id AND sender_id <> {$userId} AND new = 1) as new_messages_count"),
           DB::raw("(SELECT created_at FROM crm_messages WHERE chat_id = crm_chats.id ORDER BY created_at DESC LIMIT 1) as last_message"),
        ]);
        $qb->join('crm_chat_recipients', 'crm_chat_recipients.chat_id', '=', 'crm_chats.id');
        $qb->where('crm_chat_recipients.recipient_id', $userId);
        $qb->join('crm_leads', 'crm_leads.chat_id', '=', 'crm_chats.id');
        $qb->leftJoin('view_users', 'view_users.id', '=', 'crm_leads.user_id');
        $qb->distinct('crm_chats.id');

        $total = $qb->count('crm_chats.id');
        $items = $qb->take($rowsPerPage)->skip($skip)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение чата
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param int $chatId
     * @param int $userId
     * @param string|null $searchKey
     * @param string|null $locale
     * @return array
     */
    public function getChat(int $page, int $rowsPerPage, int $chatId, int $userId,
                            ?string $searchKey = null, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $chatInfo = Chat::select([
            'view_users.name',
            'view_users.last_name',
            'view_users.father_name',
            'view_users.fullname',
            'view_users.email',
            'view_users.avatar_url as avatar',
            'crm_leads.client_question as question',
            ]);
        $chatInfo->join('crm_chat_recipients', 'crm_chat_recipients.chat_id', '=', 'crm_chats.id');
        $chatInfo->where('crm_chat_recipients.recipient_id', $userId);
        $chatInfo->join('crm_leads', 'crm_leads.chat_id', '=', 'crm_chats.id');
        $chatInfo->leftJoin('view_users', 'view_users.id', '=', 'crm_leads.user_id');
        $chatInfo->where('crm_chats.id', $chatId);
        $chatInfo = $chatInfo->first();

        $qb = Message::query();
        $qb->join('crm_chat_recipients', 'crm_chat_recipients.chat_id', '=', 'crm_messages.chat_id');
        $qb->where('crm_chat_recipients.recipient_id', $userId);
        $qb->where('crm_messages.chat_id', $chatId);

        $qb->with('user:id,fullname,avatar_url as avatar');

        $qb->select([
            'crm_messages.id',
            'crm_messages.chat_id',
            'crm_messages.sender_id',
            'crm_messages.message',
            'crm_messages.created_at',
            DB::raw('CASE WHEN crm_messages.new = 1 THEN FALSE ELSE TRUE END as is_read'),
        ]);

        if (!is_null($searchKey)){
            $searchKey = strip_tags($searchKey);
            $searchKey = mb_strtolower($searchKey);
            $qb->whereRaw("lower(crm_messages.message) LIKE '%{$searchKey}%'");
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('created_at', 'desc')->get();
        $items->sortBy('created_at');

        $preparedItems = [];
        foreach ($items as $item){
            $item->user_id = $item->sender_id;
            unset($item->sender_id);
            $preparedItems[] = $item;
        }

        $response = $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $preparedItems);
        $response['chat_info'] = $chatInfo;

        return $response;
    }

    /**
     * Отправка сообщения
     *
     * @param string $message
     * @param int $chatId
     * @param int $userId
     * @param string|null $locale
     * @return Message|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws ApiProblemException
     */
    public function sendMessage(string $message, int $chatId, int $userId, ?string $locale = null)
    {
        $chat = Chat::find($chatId);
        if ( is_null($chat) )
            throw new ApiProblemException('Чат не найден', 404);

        $this->checkRecipients($chatId, $userId);

        $chatMessage = new Message;
        $chatMessage->sender_id = $userId;
        $chatMessage->sender_type = 'App\\Models\\Customer';
        $chatMessage->chat_id = $chat->id;
        $chatMessage->message = $message;
        $chatMessage->save();

        $notice = new Notice;
        $notice->user_id = $chat->lead->user_id;
        $notice->type = Notice::TYPE_LEAD_MESSAGE;
        $notice->relation_id = $chat->lead->id;
        $notice->save();

        return $this->getMessage($chatMessage->id);
    }

    /**
     * Пометка сообщения прочтенным
     *
     * @param int $messageId
     * @param int $chatId
     * @param int $userId
     * @return Message|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws ApiProblemException
     */
    public function readMessage(int $messageId, int $chatId, int $userId)
    {
        $message = Message::where('id', $messageId)->where('chat_id', $chatId)->first();

        if ( is_null($message) )
            throw new ApiProblemException('Сообщение не найдено', 404);

        $this->checkRecipients($chatId, $userId);

        if ($message->sender_id !== $userId){

            $message->new = false;
            $message->save();

        }

        return $this->getMessage($message->id);
    }

    /**
     * Получение сообщения
     *
     * @param int $messageId
     * @return Message|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     * @throws ApiProblemException
     */
    public function getMessage( int $messageId )
    {
        $message = Message::with('user:id,fullname,avatar_url as avatar')
            ->select([
            'crm_messages.id',
            'crm_messages.sender_id',
            'crm_messages.message',
            'crm_messages.created_at',
            DB::raw('CASE WHEN crm_messages.new = 1 THEN FALSE ELSE TRUE END as is_read'),
        ])->where('id', $messageId)->first();

        if ( is_null($message) )
            throw new ApiProblemException('Сообщение не найдено', 404);

        $message->user_id = $message->sender_id;
        unset($message->sender_id);

        return $message;
    }

    /**
     * Проверка получателей
     *
     * @param int $chatId
     * @param $userId
     * @throws ApiProblemException
     */
    private function checkRecipients(int $chatId, $userId)
    {
        $emptyRecipient = ChatRecipient::query()
                ->where('chat_id', $chatId)->where('recipient_id', $userId)->count() == 0;
        if ($emptyRecipient)
            throw new ApiProblemException('Вас нет списках получателей сообщений чата', 422);
    }

}