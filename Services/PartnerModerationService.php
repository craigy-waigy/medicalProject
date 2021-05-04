<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\ModerationStatus;
use App\Models\Partner;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use Illuminate\Support\Facades\DB;

class PartnerModerationService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * PublicationService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Модерация ФИО руковод.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveManagerName(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->description_ru)) $partner->manager_name_ru = $moderation->manager_name_ru;
            if ( !is_null($moderation->description_en)) $partner->manager_name_en = $moderation->manager_name_en;
           $partner->save();

           $moderation->manager_name_status_id = ModerationStatus::MODERATE_OK;
           $moderation->manager_name_ru = null;
           $moderation->manager_name_en = null;
           $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "ФИО Руководителя") );
        }
    }

    /**
     * Отклонение ФИО руководителя
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectManagerName(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->manager_name_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->manager_name_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "ФИО Руководителя", $message) );
        }
    }

    /**
     * Модерация краткого названия.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveOrganisationShortName(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->organisation_short_name_ru)) $partner->organisation_short_name_ru = $moderation->organisation_short_name_ru;
            if ( !is_null($moderation->organisation_short_name_en)) $partner->organisation_short_name_en = $moderation->organisation_short_name_en;
            $partner->save();

            $moderation->organisation_short_name_status_id = ModerationStatus::MODERATE_OK;
            $moderation->organisation_short_name_ru = null;
            $moderation->organisation_short_name_en = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Краткое название организации") );
        }
    }

    /**
     * Отклонение краткого названия организации
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectOrganisationShortName(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->organisation_short_name_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->organisation_short_name_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Краткое название организации", $message) );
        }
    }

    /**
     * Модерация полного названия.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveOrganisationFullName(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->organisation_full_name_ru)) $partner->organisation_full_name_ru = $moderation->organisation_full_name_ru;
            if ( !is_null($moderation->organisation_full_name_en)) $partner->organisation_full_name_en = $moderation->organisation_full_name_en;
            $partner->save();

            $moderation->organisation_full_name_status_id = ModerationStatus::MODERATE_OK;
            $moderation->organisation_full_name_ru = null;
            $moderation->organisation_full_name_en = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Полное название организации") );
        }
    }

    /**
     * Отклонение полного названия организации
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectOrganisationFullName(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->organisation_full_name_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->organisation_full_name_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Полное название организации", $message) );
        }
    }

    /**
     * Модерация описания.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveDescription(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->description_ru)) $partner->description_ru = $moderation->description_ru;
            if ( !is_null($moderation->description_en)) $partner->description_en = $moderation->description_ru;
            $partner->save();

            $moderation->description_status_id = ModerationStatus::MODERATE_OK;
            $moderation->description_ru = null;
            $moderation->description_ru = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Описание организации") );
        }
    }

    /**
     * Отклонение описания организации
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectDescription(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->description_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->description_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Описание организации", $message) );
        }
    }

    /**
     * Модерация адреса.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveAddress(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->address_ru)) $partner->address_ru = $moderation->address_ru;
            if ( !is_null($moderation->address_en)) $partner->address_en = $moderation->address_en;
            $partner->save();

            $moderation->address_status_id = ModerationStatus::MODERATE_OK;
            $moderation->address_ru = null;
            $moderation->address_ru = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Адрес") );
        }
    }

    /**
     * Отклонение адреса
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectAddress(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->address_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->address_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Адрес", $message) );
        }
    }

    /**
     * Модерация телефонов.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveTelephones(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->telephones)) $partner->telephones = $moderation->telephones;
            $partner->save();

            $moderation->telephones_status_id = ModerationStatus::MODERATE_OK;
            $moderation->telephones = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Телефоны") );
        }
    }

    /**
     * Отклонение телефонов
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectTelephones(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->telephones_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->telephones_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Телефоны", $message) );
        }
    }

    /**
     * Модерация email.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveEmail(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->email)) $partner->email = $moderation->email;
            $partner->save();

            $moderation->email_status_id = ModerationStatus::MODERATE_OK;
            $moderation->email = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Электронная почта") );
        }
    }

    /**
     * Отклонение email
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectEmail(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->email_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->email_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Электронная почта", $message) );
        }
    }

    /**
     * Модерация почтового адреса.
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function approveMailAddress(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation){
            if ( !is_null($moderation->mail_address_ru)) $partner->mail_address_ru = $moderation->mail_address_ru;
            if ( !is_null($moderation->mail_address_en)) $partner->mail_address_en = $moderation->mail_address_en;
            $partner->save();

            $moderation->mail_address_status_id = ModerationStatus::MODERATE_OK;
            $moderation->mail_address_ru = null;
            $moderation->mail_address_en = null;
            $moderation->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationAcceptNotification( "Почтовый адрес") );
        }
    }

    /**
     * Отклонение почтового адреса
     *
     * @param int $partnerId
     * @param string $message
     * @throws ApiProblemException
     */
    public function rejectMailAddress(int $partnerId, string $message)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);
        $moderation = $partner->moderationPartner;
        DB::transaction(function () use($partner, $moderation, $message){
            $moderation->mail_address_status_id = ModerationStatus::MODERATE_REJECT;
            $moderation->mail_address_message = $message;
            $partner->save();
        });
        $user = $partner->user;
        if ( !is_null($user) ){
            if ( $user->email_confirmed )
                $user->notify( new ModerationRejectNotification( "Почтовый адрес", $message) );
        }
    }

    public function getForApproval(int $page, int $rowsPerPage,?array $sorting, ?string $searchKey)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Partner::when($sorting, function ($query, $sorting){
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

                    $query = $query->orWhere('manager_name_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('manager_name_en', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('organisation_short_name_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('organisation_short_name_en', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('organisation_full_name_ru', 'like', "%{$searchKey}%");
                    $query = $query->orWhere('organisation_full_name_en', 'like', "%{$searchKey}%");

                    return $query;
                }
            });
            $qb->with('type')->withCount('publications');


        if (!empty($params['partner_type_id'])){
            $qb->where('partner_type_id', $params['partner_type_id']);
        }

        $qb->whereHas('moderationPartner', function($q){
            $q->where('manager_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('organisation_short_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('organisation_full_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('address_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('telephones_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('email_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('mail_address_status_id', ModerationStatus::ON_MODERATE);
        });
        $moderationStatus = ModerationStatus::ON_MODERATE;
        $qb->orWhereRaw("id in( SELECT partner_id FROM partner_galleries WHERE moderation_status_id = {$moderationStatus})");

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();
        foreach ($items as $partner){
            $partner->hydrateTypePublications(null);
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    public function getPartnerForApproval(int $partnerId)
    {
        $partner = Partner::where('id', $partnerId);
        $partner->with('type:id,name_ru as name,image', 'seo');
        $partner->with('partnerFiles');
        $partner = $partner->first();
        if (is_null($partner)) throw new ApiProblemException('Партнер не найден', 404);

        $partner->prepareModerationImage(null);
        $partner->hydrateTypePublications(null);
        $partner->hydrateModeration();

        return $partner;
    }
}
