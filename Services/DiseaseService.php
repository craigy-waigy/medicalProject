<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Exceptions\UnsupportLocaleException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Disease;
use App\Models\DiseasesMedicalProfile;
use App\Models\DiseasesTerapy;
use App\Models\FavoriteDisease;
use App\Models\MedicalProfile;
use App\Models\ObjectPlace;
use App\Models\Therapy;
use App\Traits\LocaleControlTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DiseaseService extends Model
{
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * DiseaseService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    const LOCATION_TYPE_REGION = 'region_id';
    const LOCATION_TYPE_COUNTRY = 'country_id';
    const LOCATION_TYPE_CITY = 'city_id';

    /**
     * Получение и поиск заболеваний
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param null|int $profileId
     * @param null|int $therapyId
     * @param null|int $parent
     * @param null|string $locale
     * @param null|array $sorting
     * @param null|array $filterParams
     * @return array
     * @throws UnsupportLocaleException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?int $profileId, ?int $therapyId,
                           ?int $parent,?array $filterParams, ?array $sorting, $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $filter = [];
        if (!is_null($parent)) $filter[] = ['parent', $parent];

        $qb = Disease::where($filter);

        if (is_null($locale)){
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey) ){
                    $query = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                    $query = $query->orWhereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                    return $query;
                }
            });
            $qb->select(['id', 'parent', 'name_ru', 'active', 'has_children', 'open_new_page', 'temporarily_disabled'])
                ->withCount('medicalProfiles');
        } else {
            $qb->where('active', true);

            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if ( !is_null($searchKey) ){
                            $searchCond = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                            return $searchCond;
                        }
                    });
                    $qb->select(['id', 'parent', 'name_ru as name', 'alias', 'has_children', 'desc_ru as description', 'open_new_page', 'temporarily_disabled'])->withCount('therapiesPublic', 'medicalProfilesPublic');
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if ( !is_null($searchKey) ){
                            $searchCond = $query->whereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                            return $searchCond;
                        }
                    });
                    $qb->select(['id', 'parent', 'name_en as name', 'alias', 'has_children', 'desc_en as description', 'open_new_page', 'temporarily_disabled'])->withCount('therapiesPublic', 'medicalProfilesPublic');
                    break;

                default : throw new UnsupportLocaleException();
            }
            $qb = $this->getDiseaseLocaleFilter($qb, $locale);
        }

        if ( !is_null($profileId) || !is_null($therapyId) ) {
            $ids = [];
            if (!is_null($profileId) xor !is_null($therapyId)){
                if (!is_null($profileId)){
                    $items = DiseasesMedicalProfile::where('medical_profile_id', $profileId)->get();
                    foreach ($items as $item){
                        $ids[] = $item->disease_id;
                    }
                }
                if (!is_null($therapyId)){
                    $items = DiseasesTerapy::where('therapy_id', $therapyId)->get();
                    foreach ($items as $item){
                        $ids[] = $item->disease_id;
                    }
                }
            } else {
                $diseasesIds = DB::table('diseases')
                    ->leftJoin('disease_medical_profile', 'diseases.id', '=', 'disease_medical_profile.disease_id')
                    ->leftJoin('diseases_therapy', 'diseases.id', 'diseases_therapy.disease_id')
                    ->select(['diseases.id'])
                    ->where('disease_medical_profile.medical_profile_id', '=', $profileId)
                    ->where('diseases_therapy.therapy_id', '=', $therapyId)
                    ->whereNotNull('disease_medical_profile.id')
                    ->whereNotNull('diseases_therapy.id')
                    ->get();
                foreach ($diseasesIds as $diseasesId){
                    $ids[] = $diseasesId->id;
                }
            }
            $qb->whereIn('id', $ids);
        }

        if (!empty($filterParams['disease_id'])){
            $items = DiseasesMedicalProfile::where('disease_id', $filterParams['disease_id'])->get();
            $ids = [];
            foreach ($items as $item){
                $ids[] = $item->medical_profile_id;
            }
            $qb->whereIn('id', $ids);
        }

        if (!empty($filterParams[self::LOCATION_TYPE_COUNTRY])){
            $checkedDiseases = self::getDiseasesIntersect($filterParams[self::LOCATION_TYPE_COUNTRY], self::LOCATION_TYPE_COUNTRY);
            $qb->whereIn('id', array_slice($checkedDiseases, $skip, $rowsPerPage));
            $items = $qb->orderBy('id', 'asc')->get();
            $total = count($checkedDiseases);

            return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);

            /*$qb = $qb->whereRaw("id in(
                              SELECT DISTINCT disease_id FROM disease_medical_profile WHERE medical_profile_id in(
                                SELECT DISTINCT medical_profile_id FROM object_medical_profiles 
                                    WHERE object_id in (
                                    SELECT id FROM objects WHERE is_visibly = TRUE AND country_id = {$filterParams['country_id']}
                                    )
                                 )
                              )"
            );*/
        }
        if (!empty($filterParams[self::LOCATION_TYPE_REGION])){
            $checkedDiseases = self::getDiseasesIntersect($filterParams[self::LOCATION_TYPE_REGION], self::LOCATION_TYPE_REGION);
            $qb->whereIn('id', array_slice($checkedDiseases, $skip, $rowsPerPage));
            $items = $qb->orderBy('id', 'asc')->get();
            $total = count($checkedDiseases);

            return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
        }
        if (!empty($filterParams[self::LOCATION_TYPE_CITY])){
            $checkedDiseases = self::getDiseasesIntersect($filterParams[self::LOCATION_TYPE_CITY], self::LOCATION_TYPE_CITY);
            $qb->whereIn('id', array_slice($checkedDiseases, $skip, $rowsPerPage));
            $items = $qb->orderBy('id', 'asc')->get();
            $total = count($checkedDiseases);

            return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
        }

        if (isset($filterParams['ids'])){
            $qb = $qb->whereIn('id', $filterParams['ids']);
        }

        $qb->when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $query = $query->orderBy($key, $value);
                }
                return $query;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });

        $qb->when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    $orderBy = $query->orderBy($key, $value);
                }
                return $orderBy;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->orderBy('id', 'asc')->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Поиск пересечений между заболеваниями профиля и исключенными заболеваниями в этом профиле
     *
     * @param array $data
     * @return Disease
     */
    public static function getDiseasesIntersect($locationId, $locationType)
    {
        //санатории региона с их медпрофилями и заболеваниями профилей
        $objects = ObjectPlace::with('medicalProfilesPublic.diseasesPublic:diseases.id')
            ->select('objects.id')
            ->where($locationType, $locationId)
            ->get()
            ->toArray();

        $objectsIds = [];
        foreach ($objects as $object) {
            $objectsIds[] = $object['id'];
        }

        //Для всех объектов вытягиваем object_medical_profile_exclude_diseases
        $excludeDiseases = DB::table('object_medical_profile_exclude_diseases')
            ->select(['object_id', 'disease_id', 'medical_profile_id'])
            ->whereIn('object_id', $objectsIds)
            ->get()
            ->toArray();

        //['id объекта']['id мед.профиля'] = ['массив id исключенных заболеваний']
        $exDiseases = [];
        foreach ($excludeDiseases as $excludeDisease) {
            $exDiseases[$excludeDisease->object_id][$excludeDisease->medical_profile_id][] = $excludeDisease->disease_id;
        }

        $checkedDiseases = [];
        foreach ($objects as $object) {
            if (isset($object['medical_profiles_public'])){
                foreach ($object['medical_profiles_public'] as $medProfile) {
                    if (isset($medProfile['diseases_public'])) {
                        foreach ($medProfile['diseases_public'] as $disease) {
                            if (isset($exDiseases[$object['id']][$medProfile['id']]) && in_array($disease['id'], $exDiseases[$object['id']][$medProfile['id']])){
                                continue;
                            } else {
                                $checkedDiseases[] = $disease['id'];
                            }
                        }
                    }
                }
            }
        }

        $checkedDiseases = array_unique($checkedDiseases);

        return $checkedDiseases;
    }

    /**
     * Добавление нового заболевания
     *
     * @param array $data
     * @return Disease
     */
    public function addDisease(array $data)
    {
        $newDisease = new Disease();
        foreach ($data as $field=>$value){
            if (!is_null($value)) $newDisease->$field = $value;

            if (isset($data['parent']) && $data['parent']){
                DB::statement("UPDATE diseases SET has_children = TRUE WHERE id = {$data['parent']}");
            }
        }
        $newDisease->save();

        return $newDisease;
    }

    /**
     * Редактирование заболевания
     *
     * @param array $data
     * @param int $diseaseId
     * @param array $therapiesIds
     * @return mixed
     */
    public function editDisease(array $data, int $diseaseId, ?array $therapiesIds)
    {
        $disease = Disease::find($diseaseId);
        if (!is_null($disease)){
            foreach ($data as $field=>$value){
                $disease->$field = $value;
            }
            $disease->save();

            if (isset($data['parent']) && $data['parent']){
                DB::statement("UPDATE diseases SET has_children = TRUE WHERE id = {$data['parent']}");
            }
        }

        if (is_array($therapiesIds)) {
            DiseasesTerapy::where('disease_id', $diseaseId)->delete();

            $therapies = Therapy::find($therapiesIds);

            foreach ($therapies as $therapy){
                $disease->therapies()->attach($therapy);
            }
        }

        return $disease;
    }

    /**
     * Получение заболевания
     *
     * @param int $diseaseId
     * @param string $url
     * @param string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getDisease(int $diseaseId = null, string $url = null, string $locale = null)
    {
        if (!is_null($diseaseId)){
            $qb = Disease::where('id', $diseaseId);
        } elseif (!is_null($url)){
            $qb = Disease::where('alias', $url);
        } else {
            throw new ApiProblemException('Для получения данных необходимо передавать ID или alias', 422);
        }
        if (!is_null($locale)){
            $qb->where('active', true);
            switch ($locale){
                case 'ru' :
                    $qb->select(['id', 'name_ru as name', 'desc_ru as description', 'active', 'alias', 'has_children'])->with(
                        'seo:disease_id,for,h1_ru as h1,title_ru as title,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'medicalProfilesPublic:medical_profiles.id,medical_profiles.name_ru as name,medical_profiles.alias',
                        'therapiesPublic:therapy.id,therapy.name_ru as name,therapy.alias',
                        'medicalProfilesPublic.seo:id,medical_profile_id,title_ru as title,order'
                    );
                    $qb->with(['parentInfo' => function($q){
                        $q->select(['id', 'name_ru as name', 'alias', 'active']);
                        $q->where('active', true);
                    }]);
                    break;

                case 'en' :
                    $qb->select(['id', 'name_en as name', 'desc_en as description', 'active', 'alias', 'has_children'])->with(
                        'seo:disease_id,for,h1_en as h1,title_en as title,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'medicalProfilesPublic:medical_profiles.id,medical_profiles.name_en as name,medical_profiles.alias',
                        'therapiesPublic:therapy.id,therapy.name_en as name,therapy.alias',
                        'medicalProfilesPublic.seo:id,medical_profile_id,title_en as title,order'
                    );
                    $qb->with(['parentInfo' => function($q){
                        $q->select(['id', 'name_en as name', 'alias', 'active']);
                        $q->where('active', true);
                    }]);
                    break;

                default : throw new ApiProblemException('Не поддерживаемая локаль', 412);
            }
            $qb = $this->getDiseaseLocaleFilter($qb, $locale);
            $qb->addSelect(['parent']);
        } else {
            $qb->with('seo', 'medicalProfiles:medical_profiles.id,medical_profiles.name_ru,medical_profiles.name_en,medical_profiles.active,medical_profiles.alias',
                'therapies:therapy.id,therapy.name_ru,therapy.name_en,therapy.alias');
        }

        $item = $qb->first();
        if (is_null($item)) throw new ApiProblemException('Запись не найдена', 404);
        $item = $item->isFavorite();

        return $item;
    }

    /**
     * Удаление заболевания
     *
     * @param int $diseaseId
     * @return bool
     */
    public function deleteDisease(int $diseaseId)
    {
        $disease = Disease::find($diseaseId);
        if (!is_null($disease)){
            $disease->seo()->delete();
            DB::table('diseases_therapy')->where('disease_id', $disease->id)->delete();
            DB::table('disease_medical_profile')->where('disease_id', $disease->id)->delete();
            foreach ($disease->files as $storage){
                Storage::delete('files/' . basename($storage->file));
                $storage->delete();
            }
            $disease->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Установление связи заболевания - мед. профиль
     *
     * @param int $diseaseId
     * @param int $medicalProfileId
     * @return array
     */
    public function addDiseaseMedicalProfile(int $diseaseId, int $medicalProfileId)
    {
        $disease = Disease::find($diseaseId);
        $medicalProfile = MedicalProfile::find($medicalProfileId);

        if (is_null($disease)){

            return ['message' => 'заболевание не найдено', 'status' => 404];
        } elseif (is_null($medicalProfile)) {

            return ['message' => 'Мед. профиль не найдено', 'status' => 404];
        } else {
            $exist = $disease->medicalProfiles()->find($medicalProfileId);
            if (is_null($exist)){
                $disease->medicalProfiles()->attach($medicalProfile);
            }

            return ['message' => 'Связь успешно установлена', 'status' => 200];
        }
    }

    /**
     * Удаление связи заболевания - мед. профиль
     *
     * @param int $diseaseId
     * @param int $medicalProfileId
     * @return array
     */
    public function deleteDiseaseMedicalProfile(int $diseaseId, int $medicalProfileId)
    {
        $disease = Disease::find($diseaseId);
        $medicalProfile = MedicalProfile::find($medicalProfileId);

        if (is_null($disease)){

            return ['message' => 'заболевание не найдено', 'status' => 404];
        } elseif (is_null($medicalProfile)) {

            return ['message' => 'Мед. профиль не найден', 'status' => 404];
        } else {
            $disease->medicalProfiles()->detach($medicalProfile);

            return ['message' => 'Связь успешно разорвана', 'status' => 200];
        }
    }

    /**
     * Установление связи заболевание - метод лечения
     *
     * @param int $diseaseId
     * @param int $therapyId
     * @return array
     */
    public function addDiseaseTherapy(int $diseaseId, int $therapyId)
    {
        $disease = Disease::find($diseaseId);
        $therapy = Therapy::find($therapyId);

        if (is_null($disease)){

            return ['message' => 'заболевание не найдено', 'status' => 404];
        } elseif (is_null($therapy)) {

            return ['message' => 'Мет. лечения не найден', 'status' => 404];
        } else {
            $exist = $disease->therapiesAdmin()->find($therapyId);
            if (is_null($exist)){
                $disease->therapies()->attach($therapy);
            }

            return ['message' => 'Связь успешно установлена', 'status' => 200];
        }
    }

    /**
     * Удаление связи заболевание - метод лечения
     *
     * @param int $diseaseId
     * @param int $therapyId
     * @return array
     */
    public function deleteDiseaseTherapy(int $diseaseId, int $therapyId)
    {
        $disease = Disease::find($diseaseId);
        $therapy = Therapy::find($therapyId);

        if (is_null($disease)){

            return ['message' => 'заболевание не найдено', 'status' => 404];
        } elseif (is_null($therapy)) {

            return ['message' => 'Мет. лечения не найден', 'status' => 404];
        } else {
            $disease->therapies()->detach($therapy);

            return ['message' => 'Связь успешно разорвана', 'status' => 200];
        }
    }

    /**
     * Получение списка избранных заболеваний
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param string $locale
     * @param int $userId
     * @return array
     * @throws UnsupportLocaleException
     */
    public function getFavorites(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting,
                                 string $locale, int $userId)
    {
        $favorites = FavoriteDisease::where('user_id', $userId)->get();
        $filterParams['ids'] = [];
        foreach ($favorites as $favorite){
            $filterParams['ids'][] = $favorite->disease_id;
        }

        return $this->search($page, $rowsPerPage, $searchKey, null, null, null, $filterParams,
            $sorting, $locale);
    }

    /**
     * Добавление в избранное
     *
     * @param int $userId
     * @param int $diseaseId
     */
    public function addFavorite(int $userId, int $diseaseId)
    {
        $favorite = FavoriteDisease::where('user_id', $userId)->where('disease_id', $diseaseId)
            ->first();
        !is_null($favorite) ? : $favorite = new FavoriteDisease;
        $favorite->user_id = $userId;
        $favorite->disease_id = $diseaseId;
        $favorite->save();
    }

    /**
     * Удаление из избранного
     *
     * @param int $userId
     * @param int $diseaseId
     * @throws ApiProblemException
     */
    public function deleteFavorite(int $userId, int $diseaseId)
    {
        $favorite = FavoriteDisease::where('user_id', $userId)->where('disease_id', $diseaseId)
            ->first();
        if(is_null($favorite))
            throw new ApiProblemException('Нет в избранном', 404);

        $favorite->delete();
    }
}
