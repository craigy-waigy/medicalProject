<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Activity;

class ActivitylogService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * ActivitylogService constructor.
     *
     * @param PaginatorFormat $paginatorFormat
     */
    public function __construct(PaginatorFormat $paginatorFormat)
    {
        $this->paginatorFormat = $paginatorFormat;
    }

    /**
     * Поличение списка активности
     *
     * @param int|null $page
     * @param int|null $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param array|null $params
     * @return array
     * @throws ApiProblemException
     */
    public function listActivity(?int $page, ?int $rowsPerPage, ?string $searchKey, ?array $sorting = null,
                                   ?array $params = null)
    {
        $skip = ($page - 1)* $rowsPerPage;

        $qb = Activity::whereNotNull('id');

        if ( !is_null($sorting)) {
            foreach ($sorting as $key => $value) {
                $qb->orderBy($key, $value);
            }
        } else {
            $qb->orderBy('created_at', 'desc');
        }

        //фильтрация по ключевому слову
        if (!is_null($searchKey)){
            $searchKey = mb_strtolower($searchKey);
            $qb->whereRaw("lower(description) like '%{$searchKey}%'");
        }
        //фильтрация по пользователю
        if (!empty($params['user_id'])){
            $qb->where('causer_id', '=', $params['user_id'])->where('causer_type', 'App\Models\User');
        }
        //Фильтрация по типу активности
        if (!empty($params['subject_type'])){
            $qb = $this->filterSubject($qb, $params['subject_type']);
        }
        //фильтрация по роли пользователя
        if (!empty($params['role_id'])){
            $qb->whereHas('user', function ($q) use ($params) {
                $q->where('role_id', (int)$params['role_id']);
            });
        }
        //фильтрация по датам
        if (!empty($params['date_from']) && !empty($params['date_to'])){
            $dateFrom = (new \DateTime($params['date_from']))->format('Y-m-d H:i:s');
            $dateTo = (new \DateTime($params['date_to']))->format('Y-m-d H:i:s');
            $qb->whereRaw("(created_at >= '{$dateFrom}' AND created_at <= '{$dateTo}')");

        } elseif (!empty($params['date_from']) && empty($params['date_to'])){
            $dateFrom = (new \DateTime($params['date_from']))->format('Y-m-d H:i:s');
            $qb->where('created_at', '>=', $dateFrom);

        } elseif (empty($params['date_from']) && !empty($params['date_to'])){
            $dateTo = (new \DateTime($params['date_to']))->format('Y-m-d H:i:s');
            $qb->where('created_at', '<=', $dateTo);

        }
        $qb->select(['id', 'causer_id', 'description', 'subject_id', 'created_at']);
        $qb->with(['user' => function($q){
            $q->select(['id', 'fullname', 'email', 'role_id'])
                ->with('role:id,name');
        }]);

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Фильтрация по типу активности
     *
     * @param $qb
     * @param string $subjectType
     * @return mixed
     * @throws ApiProblemException
     */
    public function filterSubject($qb, string $subjectType)
    {
        switch ($subjectType){
            case 'object' :
                $qb->whereIn('subject_type',[
                    'App\Models\ObjectPlace',
                    'App\Models\ObjectImage',
                    'App\Models\ObjectAward',
                    'App\Models\ObjectAwardIcon',
                    'App\Models\ObjectFoodAndSport',
                    'App\Models\ObjectInfrastructure',
                    'App\Models\ObjectMedicalInformation',
                    'App\Models\ObjectMedicalProfile',
                    'App\Models\ObjectMedicalProfileExcludeDisease',
                    'App\Models\ObjectRoom',
                    'App\Models\ObjectRoomImage',
                    'App\Models\ObjectService',
                    'App\Models\ObjectTherapy',
                    'App\Models\SanatoriumDoctor',
                    'App\Models\Service',
                    'App\Models\ServiceCategory',
                    'App\Models\ShowcaseRoom',
                    'App\Models\ShowcaseRoomImage',
                    'App\Models\SomeDirectory',
                    'App\Models\AwardIcon',
                ]);
                break;

            case 'user' :
                $qb->whereIn('subject_type',[
                    'App\Models\User',
                ]);
                break;

            case 'moderation' :
                $qb->whereIn('subject_type',[
                    'App\Models\ModerationObject',
                    'App\Models\ModerationPartner',
                    'App\Models\ModerationPublication',
                ]);
                break;

            case 'news' :
                $qb->whereIn('subject_type',[
                    'App\Models\News',
                    'App\Models\NewsImage',
                ]);
                break;

            case 'offer' :
                $qb->whereIn('subject_type',[
                    'App\Models\Offer',
                    'App\Models\OfferImage',
                ]);
                break;

            case 'partner' :
                $qb->whereIn('subject_type',[
                    'App\Models\Partner',
                    'App\Models\PartnerFile',
                    'App\Models\PartnerGallery',
                    'App\Models\Publication',
                    'App\Models\PublicationFile',
                    'App\Models\PublicationGallery',
                ]);
                break;

            case 'geography' :
                $qb->whereIn('subject_type',[
                    'App\Models\City',
                    'App\Models\Country',
                    'App\Models\Region',
                ]);
                break;

            case 'seo' :
                $qb->whereIn('subject_type',[
                    'App\Models\SeoFilterUrl',
                    'App\Models\SeoInformation',
                    'App\Models\SeoTemplate',
                ]);
                break;

            case 'medical' :
                $qb->whereIn('subject_type',[
                    'App\Models\MedicalProfile',
                    'App\Models\MedicalProfileImage',
                    'App\Models\Disease',
                    'App\Models\DiseasesTerapy',
                    'App\Models\Therapy',
                    'App\Models\TherapyImages',
                ]);
                break;

            case 'reservations' :
                $qb->whereIn('subject_type',[
                    'App\Models\Reservation',
                ]);
                break;

            case 'other' :
                $qb->whereIn('subject_type',[
                    'App\Models\FileStorage',
                    'App\Models\StorageFile',
                    'App\Models\Role',
                    'App\Models\About',
                    'App\Models\Banner',
                    'App\Models\Faq',
                    'App\Models\FaqTag',
                    'App\Models\MainBanner',
                    'App\Models\MainPage',
                ]);
                break;

            default :
                throw new ApiProblemException('не определен тип активности на сервере', 422);
        }

        return $qb;
    }

    /**
     * Список типов активности
     *
     * @return array
     */
    public function listActivityTypes()
    {
        return [
          ['subject_type' => 'object', 'description' => 'Активность по объектам'],
          ['subject_type' => 'user', 'description' => 'Активность по пользователям'],
          ['subject_type' => 'moderation', 'description' => 'Активность по модерации'],
          ['subject_type' => 'news', 'description' => 'Активность по новостям'],
          ['subject_type' => 'offer', 'description' => 'Активность по спецпредложениям'],
          ['subject_type' => 'partner', 'description' => 'Активность по партнерам'],
          ['subject_type' => 'geography', 'description' => 'Активность по географии'],
          ['subject_type' => 'seo', 'description' => 'Активность по SEO'],
          ['subject_type' => 'medical', 'description' => 'Активность по медицине'],
          ['subject_type' => 'reservations', 'description' => 'Активность по бронированию'],

          ['subject_type' => 'other', 'description' => 'Другая активность'],
        ];
    }

    /**
     * Получение деталей активности
     *
     * @param int $activityId
     * @return array
     * @throws ApiProblemException
     */
    public function getActivityDetails(int $activityId)
    {
        $activity = Activity::where('id', $activityId)
            ->with(['user' => function($q){
            $q->select(['id', 'fullname', 'email', 'role_id'])
                ->with('role:id,name');
        }])->first();

        if (is_null($activity))
            throw new ApiProblemException('Активность не найдена', 404);

        $changes = [];
        $attributes = $activity->properties['attributes'] ?? [];
        $old = $activity->properties['old'] ?? null;

        foreach ($attributes as $key => $value){
            if ($old[$key] !== $value && ($key != 'updated_at' || $key != 'modified_at') ){
                $changes[] = [
                    'field' => $key,
                    'from' => $old[$key] ?? null,
                    'to' => $value
                ];
            }
        }

        return [
            'id' => $activity->id,
            'description' => $activity->description,
            'changes' => $changes,
            'created_at' => $activity->created_at->format('Y-m-d H:i:s'),
            'user' => $activity->user,
        ];
    }
}
