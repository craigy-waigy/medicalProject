<?php

namespace App\Services;


use App\Models\ModerationPartner;
use App\Models\ModerationPublication;
use App\Models\ModerationStatus;
use App\Models\ObjectAward;
use App\Models\ObjectPlace;
use App\Models\Partner;
use App\Models\Publication;

class NotificationService
{
    /**
     * Получение счетчиков нотификаций
     *
     * @return array
     */
    public function get()
    {
        $moderationStatus = ModerationStatus::ON_MODERATE;

        $objectCount = ObjectPlace::where('is_deleted', false)->whereHas('moderationObject', function($q){
                $q->where('description_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('stars_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('payment_description_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('documents_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('contraindications_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('services_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('medical_profile_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('therapy_status_id', ModerationStatus::ON_MODERATE);
                $q->orWhere('contacts_status_id', ModerationStatus::ON_MODERATE);
        })
            ->orWhereRaw("id in( SELECT object_id FROM object_images WHERE moderation_status = {$moderationStatus})")
            ->count();
        $awardsCount = ObjectAward::where('is_new', true)->count();

        $partnerCount = Partner::whereHas('moderationPartner', function($q){
            $q->where('manager_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('organisation_short_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('organisation_full_name_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('address_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('telephones_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('email_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('mail_address_status_id', ModerationStatus::ON_MODERATE);
        })->orWhereRaw("id in( SELECT partner_id FROM partner_galleries WHERE moderation_status_id = {$moderationStatus})")
        ->count();

        $publicationCount = Publication::whereHas('moderationPublication', function($q){
            $q->where('description_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('medical_profiles_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('therapies_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('diseases_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('objects_status_id', ModerationStatus::ON_MODERATE);
            $q->orWhere('geography_status_id', ModerationStatus::ON_MODERATE);
        })->orWhereRaw("id in( SELECT publication_id FROM publication_galleries WHERE moderation_status_id = {$moderationStatus})")
        ->count();

        return [
          'moderation' => [
              'object_count' => $objectCount,
              'award_count' => $awardsCount,
              'partner_count' => $partnerCount,
              'publication_count' => $publicationCount,
          ]
        ];
    }
}
