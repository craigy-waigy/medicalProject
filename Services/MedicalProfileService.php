<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Disease;
use App\Models\DiseasesMedicalProfile;
use App\Models\DiseasesTerapy;
use App\Models\FavoriteMedicalProfile;
use App\Models\MedicalProfile;
use App\Models\MedicalProfileDefaultDisease;
use App\Models\MedicalProfileImage;
use App\Models\ObjectMedicalProfile;
use App\Models\ObjectMedicalProfileExcludeDisease;
use App\Models\ObjectPlace;
use App\Models\Therapy;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MedicalProfileService extends Model
{
    use ImageTrait;
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * MedicalProfileService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск профилей лечения
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param null|array $filterParams
     * @param null|string $locale
     * @param null|array $sorting
     * @return array
     * @throws ApiProblemException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $filterParams, ?array $sorting,
                           string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);
        $filter = [];

        $qb = MedicalProfile::where($filter);

        if (is_null($locale)){
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey) ){
                    $query = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                    $query = $query->orWhereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                    return $query;
                }
            });
            $qb->select(['id', 'name_ru', 'name_en', 'active']);

        } else {
            $qb->where('active', true);
            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if ( !is_null($searchKey) ){
                            $query = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                            return $query;
                        }
                    });
                    $qb->select(['id', 'name_ru as name', 'alias', 'description_ru as description']);
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if ( !is_null($searchKey) ){
                            $query = $query->whereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                            return $query;
                        }
                    });
                    $qb->select(['id', 'name_en as name', 'alias', 'description_en as description']);
                    break;

                default : throw new ApiProblemException('Неподдерживаемая локаль');
            }
            $qb = $this->getMedicalProfileLocaleFilter($qb, $locale);
        }

        if (!empty($filterParams['disease_id'])){
            $items = DiseasesMedicalProfile::where('disease_id', $filterParams['disease_id'])->get();
            $ids = [];
            foreach ($items as $item){
                $ids[] = $item->medical_profile_id;
            }
            $qb->whereIn('id', $ids);
        }

        if (!empty($filterParams['country_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT medical_profile_id FROM object_medical_profiles 
                                WHERE object_id in (
                                SELECT id FROM objects WHERE is_visibly = TRUE AND country_id = {$filterParams['country_id']}
                                )
                              )"
            );
        }
        if (!empty($filterParams['region_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT medical_profile_id FROM object_medical_profiles 
                                WHERE object_id in (
                                SELECT id FROM objects WHERE is_visibly = TRUE AND region_id = {$filterParams['region_id']}
                                )
                              )"
            );
        }
        if (!empty($filterParams['city_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT medical_profile_id FROM object_medical_profiles 
                                WHERE object_id in (
                                SELECT id FROM objects WHERE is_visibly = TRUE AND city_id = {$filterParams['city_id']}
                                )
                              )"
            );
        }
        if (isset($filterParams['ids'])){
            $qb = $qb->whereIn('id', $filterParams['ids']);
        }

        $qb->when($sorting, function ($query, $sorting){
            if ( !is_null($sorting)) {

                foreach ($sorting as $key => $value) {
                    if ($key != 'diseases_count' && $key != 'diseases_public_count')
                        $query = $query->orderBy($key, $value);
                }
                return $query;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        $witHydration = [];
        foreach ($items as $item){
            $item = $item->isFavorite();
            if (is_null($locale)){
                $item->diseases_count = DiseasesMedicalProfile::where('medical_profile_id', $item->id)
                    //->whereRaw("disease_id in(SELECT id FROM diseases WHERE has_children = false)")
                    ->count();
            } else {
                $item->diseases_public_count = DiseasesMedicalProfile::where('medical_profile_id', $item->id)
                    //->whereRaw("disease_id in(SELECT id FROM diseases WHERE has_children = false AND active = true)")
                    ->count();
            }
            $witHydration[] = $item;
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $witHydration);
    }

    /**
     * Создание нового медицинского профиля
     *
     * @param array $data
     * @return MedicalProfile
     */
    public function createProfile(array $data)
    {
        $newProfile = new MedicalProfile();
        foreach ($data as $field=>$value) {
            $newProfile->$field = $value;
        }
        $newProfile->save();

        return $newProfile;
    }

    /**
     * Редактирование мед. профиля
     *
     * @param array $data
     * @param int $profileId
     * @return mixed
     */
    public function editProfile(array $data, int $profileId)
    {
        $profile = MedicalProfile::find($profileId);

        if (!is_null($profile)){
            foreach ($data as $field=>$value){
                if ($field == 'diseases'){
                    if (is_string($value)) $value = json_decode($value, true);
                    $this->updateDiseaseRelations($profileId, $value);
                } else {
                    $profile->$field = $value;
                }
            }

            $profile->save();
        }

        return $profile;
    }

    /**
     * Получение мед. профиля.
     *
     * @param null|int $profileId
     * @param null|string $url
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getProfile(?int $profileId, string $url = null, string $locale = null)
    {
        if (!is_null($profileId)){
            $qb = MedicalProfile::where('id', $profileId);
        } elseif (!is_null($url)){
            $qb = MedicalProfile::where('alias', $url);
        } else {
            throw new ApiProblemException('Для получения данных необходимо передавать ID или alias', 422);
        }
        if (!is_null($locale)){
            $qb->where('active', true);
            switch ($locale){
                case 'ru' :
                    $qb->select(['id', 'name_ru as name', 'description_ru as description', 'active', 'alias'])->with(
                        'seo:medical_profile_id,for,h1_ru as h1,title_ru as title,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'diseasesPublic:diseases.id,diseases.name_ru as name,diseases.alias'
                    );
                    break;

                case 'en' :
                    $qb->select(['id', 'name_en as name', 'description_en as description', 'active', 'alias'])->with(
                        'seo:medical_profile_id,for,h1_en as h1,title_en as title,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'diseasesPublic:diseases.id,diseases.name_en as name,diseases.alias'
                    );
                    break;

                default : throw new ApiProblemException('Не поддерживаемая локаль', 412);
            }
            $qb = $this->getMedicalProfileLocaleFilter($qb, $locale);
        } else {
            $qb->with('seo',
                'diseases:diseases.id,diseases.parent,diseases.name_ru,diseases.name_en'
            );
        }
        if (!empty($filter['ids'])){
            $qb->whereIn('id', $filter['ids']);
        }
        $qb = $qb->with('images')->first();
        if (is_null($qb)) throw new ApiProblemException('Запись не найдена', 404);
        $qb = $qb->isFavorite();
        $qb = $this->hydrateTherapy($qb, $locale);

        return $qb;
    }

    /**
     * Удаление мед. профиля
     *
     * @param int $profileId
     * @return bool
     */
    public function deleteProfile(int $profileId)
    {
        $profile = MedicalProfile::find($profileId);

        if (!is_null($profile)){

            foreach ($profile->images as $image){
                Storage::delete('medical_profile/' . basename($image->image));
                $image->delete();
            }
            $profile->seo()->delete();
            DB::table('disease_medical_profile')->where('medical_profile_id', $profile->id)->delete();
            foreach ($profile->files as $storage){
                Storage::delete('files/' . basename($storage->file));
                $storage->delete();
            }
            $profile->delete();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавление изображения мед. профилю
     *
     * @param Request $request
     * @param int $profileId
     * @return MedicalProfileImage|null
     */
    public function addProfileImage(Request $request, int $profileId)
    {
        $profile = MedicalProfile::find($profileId);

        if (!is_null($profile)){

            if ($request->hasFile('image')){
                $path = $request->file('image')->store('medical_profile');

                $newImage = new MedicalProfileImage();
                $newImage->medical_profile_id = $profileId;
                $newImage->image = Storage::url($path);
                $newImage->description = $request->get('description') ?? null;

                $this->optimizeImage($newImage->image, 'medical_profile');

                $newImage->save();

                return $newImage;
            } else {
                return null;
            }
        }
    }

    /**
     * Получение изображения мед. профиля
     *
     * @param int $profileId
     * @return mixed
     */
    public function getProfileImages(int $profileId)
    {
        $images = MedicalProfile::find($profileId);
        if (!is_null($images)) return $images->images;
        else return [];
    }

    /**
     * Удаление изображения мед. профиля
     *
     * @param int $profileId
     * @param int $imageId
     * @return bool
     */
    public function deleteProfileImage(int $profileId, int $imageId)
    {
        $image = MedicalProfile::find($profileId)->images()->find($imageId);

        if (!is_null($image)){
            Storage::delete('medical_profile/' . basename($image->image));
            $image->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Добавление связи с Профилем
     *
     * @param $qb
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function hydrateTherapy( $qb, ?string $locale = null)
    {
        if (!is_null($locale)){
            $diseaseIds = [];
            foreach ($qb->diseasesPublic as $item){
                $diseaseIds[] = $item->id;
            }
            $therapyIds = DiseasesTerapy::whereIn('disease_id', $diseaseIds)->get(['therapy_id']);
            $ids = [];
            foreach ($therapyIds as $therapyId){
                $ids[] = $therapyId->therapy_id;
            }
            switch ($locale){
                case 'ru' :
                    $therapies = Therapy::whereIn('id', $ids)->select(['id', 'alias', 'name_ru as name']);
                    break;

                case 'en' :
                    $therapies = Therapy::whereIn('id', $ids)->select(['id', 'alias', 'name_en as name']);
                    break;

                default : throw new ApiProblemException('Не поддерживаемая локаль', 412);
            }
            $therapies = $this->getTherapyLocaleFilter($therapies, $locale);
            $qb->therapies_public = $therapies->where('active', true)->get();

        } else {
            $diseaseIds = [];
            foreach ($qb->diseases as $item){
                $diseaseIds[] = $item->id;
            }
            $therapyIds = DiseasesTerapy::whereIn('disease_id', $diseaseIds)->get(['therapy_id']);
            $ids = [];
            foreach ($therapyIds as $therapyId){
                $ids[] = $therapyId->therapy_id;
            }
            $therapies = Therapy::whereIn('id', $ids)->select(['id', 'alias', 'name_ru', 'name_en']);
            $qb->therapies = $therapies->get();
        }

        return $qb;
    }

    /**
     * Обновление связи мед.профиль - заболевание
     *
     * @param int $medicalProfileId
     * @param array $diseases
     */
    public function updateDiseaseRelations(int $medicalProfileId, array $diseases)
    {
        DB::transaction(function () use($diseases, $medicalProfileId){
            DB::table('disease_medical_profile')->where('medical_profile_id', $medicalProfileId)->delete();
            foreach ($diseases as $disease){
                DB::table('disease_medical_profile')->insert([
                    'medical_profile_id' => $medicalProfileId,
                    'disease_id' => $disease
                ]);
            }
        });
    }

    /**
     * Получение заболеваний по мед. профилю
     *
     * @param int $medicalProfileId
     * @param int $objectId
     * @return mixed
     */
    public function getDiseases(int $medicalProfileId, int $objectId)
    {
        $diseases = Disease::where('active', true)
            ->whereRaw("id in(SELECT disease_id FROM disease_medical_profile WHERE medical_profile_id = {$medicalProfileId})")
            ->select(['id', 'parent',  'name_ru', 'name_en', 'has_children'])
            //->where('has_children', false)
            ->orderBy('id', 'asc')
            ->get();
        $withExcluded = [];

        foreach ($diseases as $disease){
            $disease->excluded =
                ObjectMedicalProfileExcludeDisease::where('object_id', $objectId)
                    ->where('disease_id', $disease->id)->where('medical_profile_id', $medicalProfileId)->count() > 0;

            $disease->is_default =
                MedicalProfileDefaultDisease::where('diseases_id', $disease->id)->where('medical_profile_id', $medicalProfileId)->count() > 0;

            $withExcluded[] = $disease;
        }

        return $withExcluded;
    }

    /**
     * Проверка, что медпрофиль содержит только/не только дефолтные заболевания
     *
     * @param int $medicalProfileId
     * @param int $objectId
     */
    public function checkMedicalProfileHasDefaultDiseasesOnly(int $medicalProfileId, int $objectId)
    {

        $diseases = Disease::where('active', true)
            ->whereRaw("id in(SELECT disease_id FROM disease_medical_profile WHERE medical_profile_id = {$medicalProfileId})")
            ->select(['id'])
            ->get();

        $notExcluded = []; //количество выбранных заболеванеий в медпрофиле
        foreach ($diseases as $disease){
            $disease->excluded =
                ObjectMedicalProfileExcludeDisease::where('object_id', $objectId)
                    ->where('disease_id', $disease->id)->where('medical_profile_id', $medicalProfileId)->count() > 0;

            if (!$disease->excluded) {
                $notExcluded[] = $disease->id;
            }
        }

        $defaultDiseases = Disease::where('active', true)
            ->whereRaw("id IN( SELECT diseases_id FROM medical_profile_default_diseases WHERE medical_profile_id = {$medicalProfileId})")
            ->select(['id'])
            ->get()
            ->pluck('id')
            ->toArray();

        $notDefaultDiseases = array_diff($notExcluded, $defaultDiseases);//количество недефолтных заболеваний

        $objectMedicalProfile = ObjectMedicalProfile::where('object_id', $objectId)
            ->where('medical_profile_id', $medicalProfileId)
            ->first();

        if ($objectMedicalProfile) {
            $objectMedicalProfile->diseases_count = count($notExcluded);
            $objectMedicalProfile->not_default_diseases_count = count($notDefaultDiseases);
            if ($notDefaultDiseases) {
                $objectMedicalProfile->has_default_diseases_only = 0;
            } else {
                $objectMedicalProfile->has_default_diseases_only = 1;
            }

            $objectMedicalProfile->save();
        }
    }

    /**
     * Проверка, что все медпрофили объекта содержит только/не только дефолтные заболевания
     *
     * @param int $objectId
     */
    public function checkObjectHasDefaultDiseasesOnly(int $objectId)
    {
        $object = ObjectPlace::find($objectId);

        $objectMedicalProfiles = ObjectMedicalProfile::where('object_id', $objectId)
            ->get();

        $has_default_diseases_only = true;

        foreach ($objectMedicalProfiles as $profile) {
            if ($profile->has_default_diseases_only == false) $has_default_diseases_only = false;
        }

        $object->has_default_diseases_only = $has_default_diseases_only;

        $object->save();
    }

    /**
     * Отключение заболеваний в мед. профиле объекта-санатория
     *
     * @param int $objectId
     * @param int $medicalProfileId
     * @param array $diseases
     */
    public function offDiseases(int $objectId, int $medicalProfileId, array $diseases)
    {
        DB::transaction(function () use($objectId, $medicalProfileId, $diseases){
            ObjectMedicalProfileExcludeDisease::where('object_id', $objectId)
                ->where('medical_profile_id', $medicalProfileId)
                ->delete();

            foreach ($diseases as $diseaseId){
                $disease = Disease::find($diseaseId);
                if (is_null($disease)) throw new ApiProblemException('Заболевание не найдено');

                $diseaseOff = new ObjectMedicalProfileExcludeDisease();
                $diseaseOff->object_id = $objectId;
                $diseaseOff->disease_id = $diseaseId;
                $diseaseOff->medical_profile_id = $medicalProfileId;
                $diseaseOff->save();
            }
        });
    }

    /**
     * Получение списка избранных мед. профилей
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $sorting
     * @param string $locale
     * @param int $userId
     * @return array
     * @throws ApiProblemException
     */
    public function getFavorites(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting,
                                 string $locale, int $userId)
    {
        $favorites = FavoriteMedicalProfile::where('user_id', $userId)->get();
        $filterParams['ids'] = [];
        foreach ($favorites as $favorite){
            $filterParams['ids'][] = $favorite->medical_profile_id;
        }

        return $this->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, $locale);
    }

    /**
     * Добавление в избранное
     *
     * @param int $userId
     * @param int $medicalProfileId
     */
    public function addFavorite(int $userId, int $medicalProfileId)
    {
        $favorite = FavoriteMedicalProfile::where('user_id', $userId)->where('medical_profile_id', $medicalProfileId)
            ->first();
        !is_null($favorite) ? : $favorite = new FavoriteMedicalProfile;
        $favorite->user_id = $userId;
        $favorite->medical_profile_id = $medicalProfileId;
        $favorite->save();
    }

    /**
     * Удаление из избранного
     *
     * @param int $userId
     * @param int $medicalProfileId
     * @throws ApiProblemException
     */
    public function deleteFavorite(int $userId, int $medicalProfileId)
    {
        $favorite = FavoriteMedicalProfile::where('user_id', $userId)->where('medical_profile_id', $medicalProfileId)
            ->first();
        if(is_null($favorite))
            throw new ApiProblemException('Нет в избранном', 404);

        $favorite->delete();
    }

    /**
     * Получение списка мед. профилей в Личном кабинете
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param array|null $filterParams
     * @param array|null $sorting
     * @param string $locale
     * @param int $objectId
     * @return array
     * @throws ApiProblemException
     */
    public function getObjectMedicalProfiles(int $page, int $rowsPerPage, ?string $searchKey, ?array $filterParams, ?array $sorting,
                                             string $locale, int $objectId)
    {
        $medicalProfiles = $this->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, $locale);
        $items = $medicalProfiles['items'];
        unset($medicalProfiles['items']);
        $withExcluding = [];
        foreach ($items as $item){
            $diseaseCount =
                DiseasesMedicalProfile::where('medical_profile_id', $item->id)
                    ->whereRaw("disease_id in(SELECT id FROM diseases WHERE active = true)")->count() -
                ObjectMedicalProfileExcludeDisease::where('object_id', $objectId)->where('medical_profile_id', $item->id)
                    ->whereRaw("disease_id in(SELECT id FROM diseases WHERE active = true)")
                    ->count();
            unset($item->is_favorite);
            unset($item->description);
            unset($item->diseases_public_count);
            $item->diseases_count = $diseaseCount;
            $withExcluding[] = $item;
        }
        $medicalProfiles['items'] = $withExcluding;

        return $medicalProfiles;
    }

    /**
     * Добаление заболеваний по умолчанию к мед.профилям объекта
     *
     * @param int $objectId
     * @param array $medicalProfileIds
     */
    public function addMedicalProfileDefaultDiseases( int $objectId, array $medicalProfileIds)
    {
        if ( !empty($medicalProfileIds) ){
            foreach ($medicalProfileIds as $medicalProfileId){

                ObjectMedicalProfileExcludeDisease::where('object_id', $objectId)
                    ->where('medical_profile_id', $medicalProfileId)
                    ->delete();

                DB::statement("
                    INSERT INTO object_medical_profile_exclude_diseases (object_id, disease_id, medical_profile_id)
                    SELECT {$objectId} as object_id, disease_id, medical_profile_id FROM disease_medical_profile WHERE medical_profile_id = {$medicalProfileId}
                    EXCEPT
                    SELECT {$objectId} as object_id, diseases_id as disease_id, medical_profile_id FROM medical_profile_default_diseases WHERE medical_profile_id = {$medicalProfileId}
                ");

                //Проинициализируем данные о дефолтных/недефолтных заболеваниях
                 $this->checkMedicalProfileHasDefaultDiseasesOnly($medicalProfileId, $objectId);
                 $this->checkObjectHasDefaultDiseasesOnly($objectId);
            }
        }
    }

    /**
     * Сброс заболеваний медпрофилей к дефолтным
     *
     * @param int $objectId
     * @param array $medicalProfileIds
     */
    public function resetAllMedicalProfilesToDefaultDiseases( int $objectId)
    {
        $medicalProfileIds = MedicalProfile::select(['id'])
            ->get()
            ->pluck('id')
            ->toArray();

        $this->addMedicalProfileDefaultDiseases($objectId, $medicalProfileIds);
    }

    /**
     * Сброс заболеваний медпрофиля к дефолтным
     *
     * @param int $objectId
     * @param int $medicalProfileId
     */
    public function resetMedicalProfileToDefaultDiseases( int $objectId, int $medicalProfileId)
    {
        $medicalProfileIds = MedicalProfile::select(['id'])
            ->get()
            ->pluck('id')
            ->toArray();

        $this->addMedicalProfileDefaultDiseases($objectId, [$medicalProfileId]);
    }
}
