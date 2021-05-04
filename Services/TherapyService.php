<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\DiseasesMedicalProfile;
use App\Models\DiseasesTerapy;
use App\Models\FavoriteTherapy;
use App\Models\MedicalProfile;
use App\Models\Therapy;
use App\Models\TherapyImages;
use App\Traits\ImageTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TherapyService extends Model
{
    use ImageTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * TherapyService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск методов лечения
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param null|string $locale
     * @param null|array $filterParams
     * @param null|array $sorting
     * @return array
     * @throws ApiProblemException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $filterParams,
                           ?array $sorting, string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $filter = [];

        $qb = Therapy::where($filter);

        if (is_null($locale)){
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey) ){
                    $query = $query->whereRaw("LOWER(name_ru) LIKE '%$searchKey%'");
                    $query = $query->orWhereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                    return $query;
                }
            });
            $qb->select(['id', 'name_ru', 'name_en', 'active'])->withCount('diseases');
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
                    $qb->select(['id', 'name_ru as name', 'alias', 'desc_ru as description'])->withCount('diseasesPublic');
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if ( !is_null($searchKey) ){
                            $query = $query->whereRaw("LOWER(name_en) LIKE '%$searchKey%'");
                            return $query;
                        }
                    });
                    $qb->select(['id', 'name_en as name', 'alias', 'desc_en as description'])->withCount('diseasesPublic');
                    break;

                default : throw new ApiProblemException('Неподдерживаемая локаль');
            }
        }

        if (!empty($filterParams['disease_id'])){
            $items = DiseasesTerapy::where('disease_id', $filterParams['disease_id'])->get();
            $ids = [];
            foreach ($items as $item){
                $ids[] = $item->medical_profile_id;
            }
            $qb->whereIn('id', $ids);
        }
        if (!empty($filterParams['country_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT therapy_id FROM object_therapies 
                                WHERE object_id in (
                                SELECT id FROM objects WHERE is_visibly = TRUE AND country_id = {$filterParams['country_id']}
                                )
                              )"
            );
        }
        if (!empty($filterParams['region_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT therapy_id FROM object_therapies 
                                WHERE object_id in (
                                SELECT id FROM objects WHERE is_visibly = TRUE AND region_id = {$filterParams['region_id']}
                                )
                              )"
            );
        }
        if (!empty($filterParams['city_id'])){
            $qb = $qb->whereRaw("id in(
                              SELECT DISTINCT therapy_id FROM object_therapies 
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
                    $orderBy = $query->orderBy($key, $value);
                }
                return $orderBy;
            } else {
                return $query->orderBy('id', 'asc');
            }
        });

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();
        $withFavorites = [];
        foreach ($items as $item){
            $withFavorites[] = $item->isFavorite();
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Создание нового метода лечения
     *
     * @param array $data
     * @return Therapy
     */
    public function createTherapy(array $data)
    {
        $newTherapy = new Therapy();
       foreach ($data as $field=>$value){
           $newTherapy->$field = $value;
       }
        $newTherapy->save();

        return $newTherapy;
    }

    /**
     * Редактирование метода лечения
     *
     * @param array $data
     * @param int $therapyId
     * @return mixed
     * @throws ApiProblemException
     */
    public function editTherapy(array $data, int $therapyId)
    {
        $therapy = Therapy::find($therapyId);
        if(is_null($therapy)) throw new ApiProblemException('Метод лечения не найден', 404);

        foreach ($data as $field=>$value){
            if ($field == 'diseases'){
                if (is_string($value)) $value = json_decode($value, true);
                $this->updateDiseaseRelations($therapyId, $value);
            } else {
                $therapy->$field = $value;
            }
        }
        $therapy->save();

        return $therapy;
    }

    /**
     * Получение метода лечения
     *
     * @param int $therapyId
     * @param null|string $url
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function getTherapy(?int $therapyId, string $url = null, string $locale = null)
    {
        if (!is_null($therapyId)){
            $qb = Therapy::where('id', $therapyId);
        } elseif (!is_null($url)){
            $qb = Therapy::where('alias', $url);
        } else {
            throw new ApiProblemException('Для получения данных необходимо передавать ID или alias', 422);
        }
        if (!is_null($locale)){
            $qb->where('active', true);
            switch ($locale){
                case 'ru' :
                    $qb->select(['id', 'name_ru as name', 'desc_ru as description', 'active', 'alias'])->with(
                        'seo:therapy_id,for,h1_ru as h1,title_ru as title,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'diseasesPublic:diseases.id,diseases.name_ru as name,diseases.alias'
                    );
                    break;

                case 'en' :
                    $qb->select(['id', 'name_en as name', 'desc_en as description', 'active', 'alias'])->with(
                        'seo:therapy_id,for,h1_en as h1,title_en as title,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'diseasesPublic:diseases.id,diseases.name_en as name,diseases.alias'
                    );
                    break;

                default : throw new ApiProblemException('Не поддерживаемая локаль', 412);
            }
        } else {
            $qb->with('seo',
                'diseases:diseases.id,diseases.parent,diseases.name_ru,diseases.name_en'
            );
        }
        $qb = $qb->with('images')->first();
        if (is_null($qb)) throw new ApiProblemException('Запись не найдена', 404);
        $qb = $qb->isFavorite();
        $qb = $this->hydrateMedicalProfile($qb, $locale);

        return $qb;
    }

    /**
     * Удаление метода лечения
     *
     * @param int $therapyId
     * @return bool
     */
    public function deleteTherapy(int $therapyId)
    {
        $therapy = Therapy::find($therapyId);

        if (!is_null($therapy)){

            foreach ($therapy->images as $image){
                Storage::delete('therapy/' . basename($image->image));
                $image->delete();
            }
            $therapy->seo()->delete();
            DB::table('diseases_therapy')->where('therapy_id', $therapy->id)->delete();
            foreach ($therapy->files as $storage){
                Storage::delete('files/' . basename($storage->file));
                $storage->delete();
            }
            $therapy->delete();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Добавление изображения метода лечения
     *
     * @param Request $request
     * @param int $therapyId
     * @return TherapyImages|null
     */
    public function addTherapyImage(Request $request, int $therapyId)
    {
        $therapy = Therapy::find($therapyId);

        if (!is_null($therapy)){

            if ($request->hasFile('image')){
                $path = $request->file('image')->store('therapy');

                $newImage = new TherapyImages();
                $newImage->therapy_id = $therapyId;
                $newImage->image = Storage::url($path);
                $newImage->description = $request->get('description') ?? null;

                $this->optimizeImage($newImage->image, 'therapy');

                $newImage->save();

                return $newImage;
            } else {
                return null;
            }
        }
    }

    /**
     * Получение изображений метода лечения
     *
     * @param int $therapyId
     * @return mixed
     */
    public function getTherapyImages(int $therapyId)
    {
        $images = Therapy::find($therapyId);
        if (!is_null($images)) return $images->images;
        else return [];
    }

    /**
     * Удаление изображения метода лечения
     *
     * @param int $therapyId
     * @param int $imageId
     * @return bool
     */
    public function deleteTherapyImage(int $therapyId,  int $imageId)
    {
        $image = Therapy::find($therapyId)->images()->find($imageId);
        if (!is_null($image)){
            Storage::delete('therapy/' . basename($image->image));
            $image->delete();

            return true;
        } else {

            return false;
        }
    }

    /**
     * Добавление связи с профилем
     *
     * @param $qb
     * @param null|string $locale
     * @return mixed
     * @throws ApiProblemException
     */
    public function hydrateMedicalProfile( $qb, ?string $locale = null)
    {
        if (!is_null($locale)){
            $diseaseIds = [];
            foreach ($qb->diseasesPublic as $item){
                $diseaseIds[] = $item->id;
            }
            $profileIds = DiseasesMedicalProfile::whereIn('disease_id', $diseaseIds)->get(['medical_profile_id']);
            $ids = [];
            foreach ($profileIds as $profileId){
                $ids[] = $profileId->medical_profile_id;
            }
            switch ($locale){
                case 'ru' :
                    $profiles = MedicalProfile::whereIn('id', $ids)->select(['id', 'alias', 'name_ru as name']);
                    break;

                case 'en' :
                    $profiles = MedicalProfile::whereIn('id', $ids)->select(['id', 'alias', 'name_en as name']);
                    break;

                default : throw new ApiProblemException('Не поддерживаемая локаль', 412);
            }
            $qb->medical_profiles_public = $profiles->get();

        } else {
            $diseaseIds = [];
            foreach ($qb->diseases as $item){
                $diseaseIds[] = $item->id;
            }
            $profileIds = DiseasesMedicalProfile::whereIn('disease_id', $diseaseIds)->get(['medical_profile_id']);
            $ids = [];
            foreach ($profileIds as $profileId){
                $ids[] = $profileId->medical_profile_id;
            }
            $profiles = MedicalProfile::whereIn('id', $ids)->select(['id', 'alias', 'name_ru', 'name_en']);
            $qb->medical_profiles = $profiles->get();
        }

        return $qb;
    }

    /**
     * Обновление связи метода лечения и заболевания
     *
     * @param int $therapyId
     * @param array $diseases
     */
    public function updateDiseaseRelations(int $therapyId, array $diseases)
    {
        DB::transaction(function () use($diseases, $therapyId){
            DB::table('diseases_therapy')->where('therapy_id', $therapyId)->delete();
            foreach ($diseases as $disease){
                DB::table('diseases_therapy')->insert([
                    'therapy_id' => $therapyId,
                    'disease_id' => $disease
                ]);
            }
        });
    }

    /**
     *
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
        $favorites = FavoriteTherapy::where('user_id', $userId)->get();
        $filterParams['ids'] = [];
        foreach ($favorites as $favorite){
            $filterParams['ids'][] = $favorite->therapy_id;
        }

        return $this->search($page, $rowsPerPage, $searchKey, $filterParams, $sorting, $locale);
    }

    /**
     *
     *
     * @param int $userId
     * @param int $therapyId
     */
    public function addFavorite(int $userId, int $therapyId)
    {
        $favorite = FavoriteTherapy::where('user_id', $userId)->where('therapy_id', $therapyId)
            ->first();
        !is_null($favorite) ? : $favorite = new FavoriteTherapy;
        $favorite->user_id = $userId;
        $favorite->therapy_id = $therapyId;
        $favorite->save();
    }

    /**
     *
     *
     * @param int $userId
     * @param int $therapyId
     * @throws ApiProblemException
     */
    public function deleteFavorite(int $userId, int $therapyId)
    {
        $favorite = FavoriteTherapy::where('user_id', $userId)->where('therapy_id', $therapyId)
            ->first();
        if(is_null($favorite))
            throw new ApiProblemException('Нет в избранном', 404);

        $favorite->delete();
    }
}
