<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ModerationStatus;
use App\Models\Reservation;
use App\Models\ReservationStatus;
use App\Models\ShowcaseRoom;
use App\Models\User;
use App\Notifications\NewCheckoutNotification;
use App\Notifications\NewReservationToUser;
use Illuminate\Support\Facades\Log;

class ReservationService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ReservationService constructor.
     *
     * @param PaginatorFormat $paginatorFormat
     */
    public function __construct(PaginatorFormat $paginatorFormat)
    {
        $this->paginatorFormat = $paginatorFormat;
    }

    /**
     * Получение списка статусов брони
     *
     * @return ReservationStatus[]|\Illuminate\Database\Eloquent\Collection
     */
    public function listReservationStatus()
    {
        return ReservationStatus::all();
    }

    /**
     * Добавление новой брони
     *
     * @param int $roomId
     * @param array $data
     * @param bool $fromAdmin
     * @return Reservation
     * @throws ApiProblemException
     */
    public function create(int $roomId, array $data, bool $fromAdmin = false)
    {
        $room = ShowcaseRoom::find($roomId);
        if (is_null($room))
            throw new ApiProblemException('Номер не найден', 404);

        $email = mb_strtolower($data['email']);
        $generatedPassword= null;
        $user = User::where('email', $email)->first();
        if ( is_null($user) ){
            $user = new User();
            $generatedPassword = str_random(8);
            $user->email = $email;
            $user->name = $data['name'];
            $user->password = bcrypt($generatedPassword);
            $user->role_id = User::ROLE_USER;
            $user->self_registered = false;
            $user->confirm_token = str_random(50);
            $user->save();
        }

        $data['to_date'] = (new \DateTime( $data['from_date'] ))->modify("+{$data['date_count']} days")
            ->format('Y-m-d H:i:s');
        $data['email'] = mb_strtolower($data['email']);

        $reservation = new Reservation;
        $reservation->showcase_room_id = $room->id;
        $reservation->token = str_random(50);

        foreach ($data as $field => $value){
            $reservation->$field = $value;
        }
        $reservation->save();

        if (!$fromAdmin){
            $data['room'] = $room;
            $doctorEmail = env('DOCTOR_EMAIL', 'digital@tour-shop.ru');
            $doctor = User::where('email', $doctorEmail)->first();

            if ( is_null($doctor))
                Log::error('DOCTOR_EMAIL не содержится в таблице users, поэтому письмо не может быть отправлено ему');
            else
                $doctor->notify(new NewCheckoutNotification($data));
        }



        $user->notify( new NewReservationToUser($reservation, $generatedPassword));

        return $this->get($reservation->id);
    }

    /**
     * Редактирование брони
     *
     * @param int $reservationId
     * @param array $data
     * @return mixed
     * @throws ApiProblemException
     */
    public function update(int $reservationId, array $data)
    {
        $reservation = Reservation::find($reservationId);
        if (is_null($reservation))
            throw new ApiProblemException('Бронь не найдена', 404);

        is_null($data['email']) ? : $data['email'] = mb_strtolower($data['email']);

        if ( !empty($data['from_date']) && !empty($data['date_count']) ){

            $data['to_date'] = (new \DateTime( $data['from_date'] ))->modify("+{$data['date_count']} days")
                ->format('Y-m-d H:i:s');

        } elseif ( !empty($data['from_date']) && empty($data['date_count'])){

            $data['to_date'] = (new \DateTime( $data['from_date'] ))->modify("+{$reservation->date_count} days")
                ->format('Y-m-d H:i:s');

        } elseif ( empty($data['from_date']) && !empty($data['date_count']) ){

            $data['to_date'] = (new \DateTime( $reservation->from_date ))->modify("+{$data['date_count']} days")
                ->format('Y-m-d H:i:s');

        }

        foreach ($data as $field => $value){
            $reservation->$field = $value;
        }
        $reservation->save();

        return $this->get($reservation->id);
    }

    /**
     * Получение списка бронирований
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param array|null $filter
     * @param null|string $locale
     * @return array
     * @throws ApiProblemException
     */
    public function list(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting = null, ?array $filter = null,
                         ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $qb = Reservation::whereNotNull('id');

        if (!is_null($sorting)){
            foreach ($sorting as $field => $direction){
                $qb->orderBy($field, $direction);
            }
        }

        if (is_null($locale)){
            if ( !is_null($searchKey) ){
                $searchKey = strip_tags($searchKey);
                $searchKey = mb_strtolower($searchKey);

                $qb->where(function ($query) use($searchKey) {
                    $query->whereRaw("
                        lower(name) LIKE '%{$searchKey}%' OR
                        lower(email) LIKE '%{$searchKey}%' OR
                        lower(text) LIKE '%{$searchKey}%'
                    ");
                    $query->orWhereHas('room', function ($q) use ($searchKey){
                        $q->whereRaw("
                            lower(title_ru) LIKE '%{$searchKey}%' OR
                            lower(title_en) LIKE '%{$searchKey}%'
                        ");
                    });
                    $query->orWhereHas('room.object', function ($q) use ($searchKey){
                        $q->whereRaw("
                            lower(title_ru) LIKE '%{$searchKey}%' OR
                            lower(title_en) LIKE '%{$searchKey}%'
                        ");
                    });
                });


            }
            $qb->with(
                'status',
                'room:id,object_id,title_ru',
                'room.object:id,title_ru,country_id,region_id,city_id',
                'room.object.country:id,name_ru',
                'room.object.region:id,name_ru',
                'room.object.city:id,name_ru'
            );
        } else {
            switch ($locale){
                case 'ru' :
                    if ( !is_null($searchKey) ){
                        $searchKey = strip_tags($searchKey);
                        $searchKey = mb_strtolower($searchKey);

                        $qb->where(function ($query) use($searchKey) {
                            $query->whereRaw("
                                lower(name) LIKE '%{$searchKey}%' OR
                                lower(email) LIKE '%{$searchKey}%' OR
                                lower(text) LIKE '%{$searchKey}%'
                            ");
                            $query->orWhereHas('room', function ($q) use ($searchKey){
                                $q->whereRaw("
                                    lower(title_ru) LIKE '%{$searchKey}%'
                                ");
                            });
                            $query->orWhereHas('room.object', function ($q) use ($searchKey){
                                $q->whereRaw("
                                    lower(title_ru) LIKE '%{$searchKey}%'
                                ");
                            });
                        });
                    }
                    $qb->with('status:id,name')
                        ->with([ 'room' => function ( $q ){
                            $q->select([ 'id', 'object_id', 'title_ru as title' ])
                                ->with([ 'object' => function ( $q ) {
                                    $q->select([ 'id', 'title_ru as title', 'country_id', 'region_id', 'city_id',
                                        'alias', 'stars' ])
                                        ->with(
                                            'country:id,name_ru as name',
                                            'region:id,name_ru as name',
                                            'city:id,name_ru as name',
                                            'image:id,object_id,thumbs as image,description'
                                        );
                                }]);
                        }]);
                    break;

                case 'en' :
                    if ( !is_null($searchKey) ){
                        $searchKey = strip_tags($searchKey);
                        $searchKey = mb_strtolower($searchKey);

                        $qb->where(function ($query) use($searchKey) {
                            $query->whereRaw("
                                lower(name) LIKE '%{$searchKey}%' OR
                                lower(email) LIKE '%{$searchKey}%' OR
                                lower(text) LIKE '%{$searchKey}%'
                            ");
                            $query->orWhereHas('room', function ($q) use ($searchKey){
                                $q->whereRaw("
                                    lower(title_en) LIKE '%{$searchKey}%'
                                ");
                            });
                            $query->orWhereHas('room.object', function ($q) use ($searchKey){
                                $q->whereRaw("
                                    lower(title_en) LIKE '%{$searchKey}%'
                                ");
                            });
                        });
                    }
                    $qb->with('status:id,name_en as name')
                        ->with([ 'room' => function ( $q ){
                            $q->select([ 'id', 'object_id', 'title_en as title' ])
                                ->with([ 'object' => function ( $q ) {
                                    $q->select([ 'id', 'title_en as title', 'country_id', 'region_id', 'city_id',
                                        'alias', 'stars' ])
                                        ->with(
                                            'country:id,name_en as name',
                                            'region:id,name_en as name',
                                            'city:id,name_en as name',
                                            'image:id,object_id,thumbs as image,description'
                                        );
                                }]);
                        }]);
                    break;

                default :
                    throw new ApiProblemException("Не поддерживаемая локаль {$locale}", 422);
            }
        }

        if (!empty($filter['reservation_status_id'])){
            $qb->where('reservation_status_id', (int)$filter['reservation_status_id']);
        }
        if (!empty($filter['reservation_id'])){
            $qb->where('id', (int)$filter['reservation_id']);
        }
        if (!empty($filter['email'])){
            $qb->where('email', (string)$filter['email']);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)
            ->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение брони
     *
     * @param int $reservationId
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(int $reservationId, ?string $locale = null)
    {
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $reservation = Reservation::where('id',$reservationId)->with(
                        'status:id,name',
                        'room:id,object_id,title_ru as title',
                        'room.object:id,title_ru as title,country_id,region_id,city_id',
                        'room.object.country:id,name_ru as name',
                        'room.object.region:id,name_ru as name',
                        'room.object.city:id,name_ru as name'
                    )->first();
                    break;

                case 'en' :
                    $reservation = Reservation::where('id',$reservationId)->with(
                        'status:id,name_en as name',
                        'room:id,object_id,title_en as title',
                        'room.object:id,title_en as title,country_id,region_id,city_id',
                        'room.object.country:id,name_en as name',
                        'room.object.region:id,name_en as name',
                        'room.object.city:id,name_en as name'
                    )->first();
                    break;

                default :
                    throw new ApiProblemException("Не поддерживаемая локаль {$locale}", 422);
            }
        } else {
            $reservation = Reservation::where('id',$reservationId)->with(
                'status',
                'room:id,object_id,title_ru',
                'room.object:id,title_ru,country_id,region_id,city_id',
                'room.object.country:id,name_ru',
                'room.object.region:id,name_ru',
                'room.object.city:id,name_ru'
            )->first();
        }

        if (is_null($reservation))
            throw new ApiProblemException('Бронь не найдена', 404);

        return $reservation;
    }

    /**
     * Удаление брони
     *
     * @param int $reservationId
     * @throws ApiProblemException
     */
    public function delete(int $reservationId)
    {
        $reservation = Reservation::where('id',$reservationId)->with('status')->first();
        if (is_null($reservation))
            throw new ApiProblemException('Бронь не найдена', 404);

        $reservation->delete();
    }
}
