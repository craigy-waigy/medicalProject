<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Models\Feedback;
use App\Models\ObjectPlace;
use App\Models\ObjectViewingCount;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ShowcaseRoom;
use App\Traits\LocaleControlTrait;

class PublicObjectService
{
    use LocaleControlTrait;

    /**
     * Получение данных по объекту
     *
     * @param string $locale
     * @param string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getObject(string $locale, string $alias)
    {
        $object = ObjectPlace::where('alias', $alias)->where('is_visibly', true);

        switch ($locale){
            case 'ru' :
                $object->select([
                    'id',
                    'country_id',
                    'region_id',
                    'city_id',
                    'title_ru as title',
                    'description_ru as description',
                    'documents_ru as documents',
                    'visa_information_ru as visa_information',
                    'contraindications_ru as contraindications',
                    'payment_description_ru as payment_description',
                    'in_action',
                    'address',
                    'stars',
                    'lat',
                    'lon',
                    'reviewpro_code',
                    'heating_rating',
                    'full_rating',
                    'street_view_link',
                ])->with(
                    'moods:moods.id,name_ru as name,alias,image,crop_image',
                    'country:id,name_ru as name,alias',
                    'region:id,name_ru as name,alias',
                    'city:id,name_ru as name,alias',
                    'seo:id,object_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords'
                );
                $object->with(['sanatoriumDoctors' => function ($q){
                    $q->select(['id', 'user_id', 'object_id', 'online', 'languages', 'specializations_ru as specializations'])
                        ->where('online', true)
                        ->with('user')
                    ;
                }]);
                $medicalProfilesSelect = ['medical_profiles.id', 'medical_profiles.name_ru as name', 'medical_profiles.alias', 'medical_profiles.description_ru as description'];
                $therapiesSelect = ['therapy.id', 'therapy.name_ru as name', 'therapy.alias', 'therapy.desc_ru as description'];
                $awardIconsSelect = ['award_icons.id', 'award_icons.title_ru as name', 'award_icons.description_ru as description', 'image'];
                break;

            case 'en' :
                $object->select([
                    'id',
                    'country_id',
                    'region_id',
                    'city_id',
                    'title_en as title',
                    'description_en as description',
                    'documents_en as documents',
                    'visa_information_en as visa_information',
                    'contraindications_en as contraindications',
                    'payment_description_en as payment_description',
                    'in_action',
                    'address',
                    'stars',
                    'lat',
                    'lon',
                    'reviewpro_code',
                    'heating_rating',
                    'full_rating',
                ])->with(
                    'moods:moods.id,name_en as name,alias,image,crop_image',
                    'country:id,name_en as name,alias',
                    'region:id,name_en as name,alias',
                    'city:id,name_en as name,alias',
                    'seo:id,object_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords'
                );
                $medicalProfilesSelect = ['medical_profiles.id', 'medical_profiles.name_en as name', 'medical_profiles.alias', 'medical_profiles.description_en as description'];
                $therapiesSelect = ['therapy.id', 'therapy.name_en as name', 'therapy.alias', 'therapy.desc_en as description'];
                $awardIconsSelect = ['award_icons.id', 'award_icons.title_en as name', 'award_icons.description_en as description', 'image'];
                break;

            default :
                throw new ApiProblemException('Локаль не поддерживается', 422);
        }

        $object = $this->getObjectsLocaleFilter($object, $locale);

        $object = $this->getWithMedicalProfilesLocaleFilter($object, $locale, 'medicalProfilesPublic', $medicalProfilesSelect);
        $object = $this->getWithTherapiesLocaleFilter($object, $locale, 'therapiesPublic', $therapiesSelect);
        $object = $this->getWithAwardLocaleFilter($object, $locale, 'awardIconsPublic', $awardIconsSelect);


        $object->with('moderatedImages:id,object_id,image,small as thumbs,description,sorting_rule,is_main');

        $object->whereHas('moderatedImages', function ($q){
            $q->havingRaw("count(*) > 0")->groupBy('id');
        });

        $object = $object->first();
        if (is_null($object))
            throw new ApiProblemException('Объект не найден', 404);

        $object = $object->isFavorite();
        $object->addRandomDoctor($locale);

        $object->service_categories = $this->services($object->id, $locale);
        $object->showcase_rooms_public = $this->showcaseRoomPublic($object->id, $locale);

        $feedback = $this->getFeedback($object->id);
        $object->feedback = $feedback['content'];
        $object->feedback_count = $feedback['content_count'];

        return $object;
    }

    /**
     * Получение Услуг
     *
     * @param int $objectId
     * @param string $locale
     * @return array
     */
    public  function services(int $objectId, string $locale)
    {
        $categories = ServiceCategory::where('active', true)
            ->whereRaw("
            id in(
            SELECT service_category_id FROM services 
            WHERE active = TRUE AND 
            id in(SELECT service_id FROM object_services WHERE object_id = {$objectId}))
            ");

        switch ($locale){
            case 'ru' :
                $categories->select([
                    'id',
                    'name_ru as name',
                    'image',
                ]);
                break;

            case 'en' :
                $categories->select([
                    'id',
                    'name_en as name',
                    'image',
                ]);
                break;
        }
        $categories = $categories->orderBy('sorting', 'asc')
            ->where('name_' . $locale, '<>', '')->whereNotNull('name_' . $locale)
            ->whereHas('services', function ( $q ) use($objectId, $locale){
                $q->select(['id', 'service_category_id', "name_$locale as name"]);
                $q->whereRaw("id in (SELECT service_id FROM object_services WHERE object_id = {$objectId})");
                $q->where('name_' . $locale, '<>', '')->whereNotNull('name_' . $locale);
                $q->where('active', true);
            })
            ->with(['services' => function( $q ) use($objectId, $locale){
                $q->select(['id', 'service_category_id', "name_$locale as name"]);
                $q->whereRaw("id in (SELECT service_id FROM object_services WHERE object_id = {$objectId})");
                $q->where('name_' . $locale, '<>', '')->whereNotNull('name_' . $locale);
                $q->where('active', true);
            }]);

        return $categories->get();
    }

    /**
     * Витрина номеров
     *
     * @param int $objectId
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public  function showcaseRoomPublic(int $objectId, string $locale)
    {
        $showcase = ShowcaseRoom::where('active', true)->where('object_id', $objectId);
        switch ($locale){
            case 'ru' :
                $showcase->select([
                    'id',
                    'capacity',
                    'capacity_min',
                    'capacity_max',
                    'square',
                    'title_ru as title',
                    'description_ru as description',
                    'interior_ru as interior',
                    'price',
                    'image'
                ]);
                break;

            case 'en' :
                $showcase->select([
                    'id',
                    'capacity',
                    'capacity_min',
                    'capacity_max',
                    'square',
                    'title_en as title',
                    'description_en as description',
                    'interior_en as interior',
                    'price',
                    'image'
                ]);
                break;

            default :
                throw new ApiProblemException('Локаль не поддерживается', 422);
        }
        $showcase->with('images:id,showcase_room_id,description,thumbs as image,sorting_rule,is_main');
        $showcase = $this->getShowcaseRoomLocaleFilter($showcase, $locale);

        return $showcase->get();
    }

    /**
     * Получение отзывов
     *
     * @param int $objectId
     * @return mixed
     */
    public function getFeedback(int $objectId)
    {
        $feedback = Feedback::where('object_id', $objectId)
            ->whereNotNull('comment')
            ->with(
                'reservation:id,email',
                'reservation.user:id,fullname,email,avatar_url as avatar',
                'sanatoriumAnswer:id,feedback_id,comment,updated_at as commented_at'
            )
            ->select([
                'id', 'reservation_id', 'object_id', 'quality_impressions', 'quality_healing', 'quality_rooms',
                'quality_cleaning_rooms', 'quality_nutrition', 'quality_entertainment', 'liked', 'not_liked', 'comment', 'has_answer'
            ])
            ->orderBy('created_at', 'desc')->take(2)
            ->get();
        $feedbackCount = Feedback::where('object_id', $objectId)->whereNotNull('comment')->count();

        return [
            'content' => $feedback,
            'content_count' => $feedbackCount,
        ];
    }
}
