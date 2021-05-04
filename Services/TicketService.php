<?php


namespace App\Services;


use App\Libraries\Models\PaginatorFormat;
use App\Models\SanatoriumOffer;
use App\Models\Ticket;

class TicketService
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
     * Сохранение тикета с сайта
     *
     * @param array $data
     * @return Ticket
     */
    public function createTicket( array $data)
    {
        activity()->disableLogging();
        $ticket = new Ticket;
        foreach ($data as $field => $value){
            $ticket->$field = $value;
        }
        $ticket->save();
        activity()->enableLogging();

        return $ticket;
    }

    /**
     * Сохранение предложения санатория
     *
     * @param array $data
     * @return SanatoriumOffer
     */
    public function createSanatoriumOffer( array $data)
    {
        activity()->disableLogging();
        $sanatoriumOffer = new SanatoriumOffer;
        foreach ($data as $field => $value){
            $sanatoriumOffer->$field = $value;
        }
        $sanatoriumOffer->save();
        activity()->disableLogging();

        return $sanatoriumOffer;
    }
}