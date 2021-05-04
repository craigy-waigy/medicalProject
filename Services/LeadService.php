<?php


namespace App\Services;


use App\Models\CRM\Lead;
use App\Models\CRM\LeadUser;
use App\Models\CRM\Notice;
use App\Models\ShowcaseRoom;
use App\Models\User;

class LeadService
{
    /**
     * Создание нового лида
     *
     * @param array $data
     * @param string $type
     * @throws \Exception
     */
    public function createLead(array $data, string $type)
    {
        $customer = User::where('email', $data['email'])->first();
        $clientName = $data['name'];
        !isset($data['last_name']) ? : $clientName .= ' ' . $data['last_name'];
        !isset($data['middle_name']) ? : $clientName .= ' ' . $data['middle_name'];

        $lead = new Lead;

        $lead->type = $type;
        $lead->object_id = $data['object_id'] ?? null;
        $lead->client_name = $clientName;
        $lead->client_email = $data['email'];
        $lead->client_phone = $data['tel'];
        $lead->client_wishes = $this->getWishes($data, $type);
        $lead->client_question = $data['offer'];
        $lead->customer_id = $customer->id;
        $lead->save();

        $this->setLeadToUser( $lead );
    }

    /**
     * Пожелания
     *
     * @param $data
     * @param $type
     * @return string
     * @throws \Exception
     */
    protected function getWishes($data, $type)
    {
        $wishes = '';

        if ($type == Lead::TYPE_ORDER){
            $room = ShowcaseRoom::find($data['showcase_room_id']);
            $fromDate = (new \DateTime($data['from_date']))->format('d.m.Y');
            $wishes .= "<br><b>Объект: </b> {$room->object->title_ru} <br>";
            $wishes .= "<b>Номер: </b> {$room->title_ru} <br>";
            $wishes .= "<b>Дата заезда: </b> {$fromDate} <br>";
            $wishes .= "<b>Количество дней: </b> {$data['date_count']}";
        }

        return $wishes;
    }

    /**
     * Назначение мэнеджера
     *
     * @param Lead $lead
     */
    public function setLeadToUser( Lead $lead )
    {
        $users = User::
            where('active', true)
            ->where('leadable', true)
            ->withCount('leadUsers')
            ->orderBy('lead_users_count', 'asc')
            ->take(10)
            ->get();

        if ( $users->count() > 0 ){

            $user = $users->splice( rand(0, ($users->count() -1)) )->first();

            $leadUser = new LeadUser;
            $leadUser->user_id = $user->id;
            $leadUser->lead_id = $lead->id;
            $leadUser->save();

            $lead->user_id = $user->id;
            $lead->save();
        }
    }
}