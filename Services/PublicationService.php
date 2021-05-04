<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Disease;
use App\Models\MedicalProfile;
use App\Models\ModerationStatus;
use App\Models\ObjectPlace;
use App\Models\Publication;
use App\Models\PublicationDisease;
use App\Models\PublicationFile;
use App\Models\PublicationGallery;
use App\Models\PublicationGeography;
use App\Models\PublicationMedicalProfile;
use App\Models\PublicationObject;
use App\Models\PublicationTherapy;
use App\Models\PublicationType;
use App\Models\Therapy;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PublicationService
{
    use ImageTrait;
    use LocaleControlTrait;

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
     * Получение типов публикации
     *
     * @param string $locale
     * @return PublicationType[]|\Illuminate\Database\Eloquent\Collection
     * @throws ApiProblemException
     */
    public function getPublicationTypes(?string $locale = null)
    {
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $types = PublicationType::select([ 'id', 'name_ru as name', 'alias' ])
                        ->where('name_ru', '<>', '')->whereNotNull('name_ru')
                        ->whereNotNull('alias')->get();
                    break;

                case 'en' :
                    $types = PublicationType::select([ 'id', 'name_en as name', 'alias' ])
                        ->where('name_en', '<>', '')->whereNotNull('name_en')
                        ->whereNotNull('alias')->get();
                    break;

                default :
                    throw new ApiProblemException('Локаль ' . $locale . ' не поддерживается', 422);
            }
        } else {
            $types = PublicationType::all();
        }

        return $types;
    }

    /**
     * Получение типа публикации
     *
     * @param int|null $publicationTypeId
     * @param null|string $locale
     * @param null|string $alias
     * @return mixed
     * @throws ApiProblemException
     */
    public function getPublicationType(?int $publicationTypeId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            $type = PublicationType::where('alias', $alias);
            switch ($locale){
                case 'ru' :
                    $type->select([ 'id', 'name_ru as name', 'alias' ])->where('name_ru', '<>', '')->whereNotNull('name_ru');
                    $type->with('seo:id,publication_type_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords');
                    break;

                case 'en' :
                    $type->select([ 'id', 'name_en as name', 'alias' ])->where('name_en', '<>', '')->whereNotNull('name_en');
                    $type->with('seo:id,publication_type_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords');
                    break;

                default :
                    throw new ApiProblemException('Локаль ' . $locale . ' не поддерживается', 422);
            }
        } else {
            $type = PublicationType::where('id', $publicationTypeId);
            $type->with('seo');
        }
        $type = $type->first();
        if (is_null($type)) throw new ApiProblemException('Тип партнера не найден', 404);

        return $type;
    }

    /**
     * Добавление нового типа публикации
     *
     * @param Request $request
     * @return PublicationType
     */
    public function addPublicationType(Request $request)
    {
        $type = new PublicationType();
        $data = $request->only('name_ru', 'name_en');
        foreach ($data as $field => $value){
            $type->$field = $value;
        }
        $type->save();

        return $type;
    }

    /**
     * Редактирование типа публикации
     *
     * @param Request $request
     * @param int $typeId
     * @return mixed
     * @throws ApiProblemException
     */
    public function editPublicationType(Request $request, int $typeId)
    {
        $type = PublicationType::find($typeId);
        if (is_null($type))
            throw new ApiProblemException('Тип не найден', 404);
        $data = $request->only('name_ru', 'name_en');
        foreach ($data as $field => $value){
            $type->$field = $value;
        }
        $type->save();

        return $type;
    }

    /**
     * Удаление типа публикации
     *
     * @param int $typeId
     * @throws ApiProblemException
     */
    public function deletePublicationType(int $typeId)
    {
        $type = PublicationType::find($typeId);
        if (is_null($type))
            throw new ApiProblemException('Тип не найден', 404);

        if ($type->publications()->count() > 0)
            throw new ApiProblemException('Невозможно удалить. Удалите все публикации сначала', 404);
        $type->seo()->delete();
        $type->delete();
    }

    /**
     * ====================== Publications =========================================================================
     */

    /**
     * Добавление публикации
     *
     * @param array $data
     * @param int $partnerId
     * @param bool $fromAdmin
     * @return Publication
     */
    public function add(array $data, int $partnerId, bool $fromAdmin = false)
    {
        $publication = new Publication();
        $publication->publication_type_id = $data['publication_type_id'];
        if ($fromAdmin){
            $publication->moderation_status_id = ModerationStatus::NO_MODERATE;
        } else {
            $publication->moderation_status_id = ModerationStatus::ON_MODERATE;
        }
        $publication->partner_id = $partnerId;
        foreach ($data as $field=>$value){
            $publication->$field = $value;
        }
        if ($fromAdmin){
            $publication->moderation_status_id = ModerationStatus::NO_MODERATE;
        }
        $publication->save();
        $publication->moderationPublication()->insert(['publication_id' => $publication->id]);
        if (!$fromAdmin){
            DB::transaction(function () use ($publication){
                $publication->alias = str_slug($publication->title_ru . '-' . $publication->id);
                $publication->seo()->insert([
                    'for' => 'publication',
                    'publication_id' => $publication->id,
                    'url' => $publication->alias,
                    'title_ru' => $publication->title_ru,
                    'title_en' => $publication->title_en,
                    'h1_ru' => $publication->title_en,
                ]);
                $publication->save();
            });
        }

        return $publication;
    }

    /**
     * Редактирование публикации
     *
     * @param array $data
     * @param int $publicationId
     * @param null|int $partnerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function edit(array $data, int $publicationId, ?int $partnerId = null)
    {
        if (is_null($partnerId)){
            $publication = Publication::find($publicationId);
        } else {
            $publication = Publication::where('id', $publicationId)->where('partner_id', $partnerId)->first();
        }
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        foreach ($data as $field=>$value){
            $publication->$field = $value;
        }
        $publication->save();

        $publication->hydrateModeration();
        $publication->prepareModerationImage(null);

        return $publication;
    }

    /**
     * Поиск публикации
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param null|string $locale
     * @param bool $fromAccount
     * @param array|null $sorting
     * @param array|null $filter
     * @return array
     * @throws ApiProblemException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, ?array $sorting = null, ?array $filter = null,
                           bool $fromAccount = false, ?string $locale = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Publication::
        when($sorting, function ($query, $sorting){
                if ( !is_null($sorting)) {

                    foreach ($sorting as $key => $value) {
                        $query = $query->orderBy($key, $value);
                    }
                    return $query;
                } else {
                    return $query->orderBy('id', 'asc');
                }
            });
        //Типы партнеров
        if (!empty($filter['partner_id'])){
            $qb->where('partner_id', $filter['partner_id']);
        }
        //Типы публикаций
        if (!empty($filter['publication_type_id'])){
            $qb->where('publication_type_id', $filter['publication_type_id']);
        }
        //Страны
        if (!empty($filter['country_id'])){
            $qb->whereHas('geography.country', function ($query) use($filter){
                $query->where('id', $filter['country_id']);
            });
        }
        //Регионы
        if (!empty($filter['region_id'])){
            $qb->whereHas('geography.region', function ($query) use($filter){
                $query->where('id', $filter['region_id']);
            });
        }
        //Города
        if (!empty($filter['city_id'])){
            $qb->whereHas('geography.city', function ($query) use($filter){
                $query->where('id', $filter['city_id']);
            });
        }
        //Мед. профили
        if (!empty($filter['medical_profile_id'])){
            $qb->whereHas('medicalProfiles', function ($query) use($filter){
                $query->where('medical_profiles.id', $filter['medical_profile_id']);
            });
        }
        //Методы лечения
        if (!empty($filter['therapy_id'])){
            $qb->whereHas('therapies', function ($query) use($filter){
                $query->where('therapy.id', $filter['therapy_id']);
            });
        }
        //Заболевания
        if (!empty($filter['disease_id'])){
            $qb->whereHas('diseases', function ($query) use($filter){
                $query->where('diseases.id', $filter['disease_id']);
            });
        }
        //Объекты
        if (!empty($filter['object_id'])){
            $qb->whereHas('objects', function ($query) use($filter){
                $query->where('objects.id', $filter['object_id']);
            });
        }

        if (!is_null($locale)){
            $qb->where('active', true)->whereIn('moderation_status_id', [
                ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE])->whereNotNull('alias');

            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(description_ru) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(author_ru) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select([
                        'id',
                        'publication_type_id',
                        'partner_id',
                        'title_ru as title',
                        'published_at',
                        'alias',
                    ])->with(
                        'type:id,name_ru as name,alias',
                        'partner:id,partner_type_id,organisation_short_name_ru as organisation_short_name,logo,alias',
                        'partner.type:id,name_ru as name,alias'
                    );
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(title_en) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(description_en) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(author_en) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select([
                        'id',
                        'publication_type_id',
                        'partner_id',
                        'title_en as title',
                        'published_at',
                        'alias',
                    ])->with(
                        'type:id,name_en as name,alias',
                        'partner:id,partner_type_id,organisation_short_name_en as organisation_short_name,logo,alias',
                        'partner.type:id,name_ru as name,alias'
                    );
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $qb = $this->getPublicationLocaleFilter($qb, $locale);
        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey)) {
                    $query = $query->whereRaw("lower(title_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(description_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(author_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(title_en) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(description_en) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(author_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            })->with(
                'type:id,name_ru',
                'partner:id,partner_type_id,organisation_short_name_ru',
                'partner.type:id,image,name_ru'
            );

        }

        if ($fromAccount){
            $qb->where('active', true);
        }
        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Получение публикации
     *
     * @param int|null $publicationId
     * @param null|string $locale
     * @param null|string $alias
     * @param null|int $partnerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function get(?int $publicationId, ?string $locale = null, ?string $alias = null, ?int $partnerId = null)
    {
        if (!is_null($locale)){
            $publication = Publication::where('active', true)->whereIn('moderation_status_id', [
                ModerationStatus::MODERATE_OK, ModerationStatus::NO_MODERATE])
            ->where('alias', $alias);

            switch ($locale){
                case 'ru' :
                    $publication->select([
                        'id',
                        'publication_type_id',
                        'partner_id',
                        'title_ru as title',
                        'author_ru as author',
                        'description_ru as description',
                        'published_at',
                        'alias',
                    ])->with(
                        'seo:id,publication_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords',
                        'type:id,name_ru as name,alias',
                        'partner:id,organisation_short_name_ru as organisation_short_name,logo,alias,telephones,email,mail_address_ru as mail_address,address_ru as address',
                        'partner.type:id,name_ru as name,alias',

                        'geography.country:countries.id,name_ru as name,alias,is_visible',
                        'geography.region:regions.id,country_id,name_ru as name,alias,is_visible',
                        'geography.region.country:countries.id,name_ru as name,alias,is_visible',
                        'geography.city.region:regions.id,country_id,name_ru as name,alias,is_visible',
                        'geography.city.country:countries.id,name_ru as name,alias,is_visible',
                        'geography.city:cities.id,country_id,region_id,name_ru as name,alias,is_visible'
                    );
                    $objectSelect = ['objects.id', 'objects.title_ru as title', 'objects.alias'];
                    $medicalProfilesSelect = ['medical_profiles.id', 'medical_profiles.name_ru as name', 'medical_profiles.alias'];
                    $therapiesSelect = ['therapy.id', 'therapy.name_ru as name', 'therapy.alias'];
                    $diseasesSelect = ['diseases.id', 'diseases.name_ru as name', 'diseases.alias'];

                    break;

                case 'en' :
                    $publication->select([
                        'id',
                        'publication_type_id',
                        'partner_id',
                        'title_en as title',
                        'author_en as author',
                        'description_en as description',
                        'published_at',
                        'alias',
                    ])->with(
                        'seo:id,publication_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords',
                        'type:id,name_en as name,alias',
                        'partner:id,organisation_short_name_en as organisation_short_name,logo,alias,telephones,email,mail_address_en as mail_address,address_en as address',
                        'partner.type:id,name_en as name,alias',

                        'geography.country:countries.id,name_en as name,alias,is_visible',
                        'geography.region:regions.id,country_id,name_en as name,alias,is_visible',
                        'geography.region.country:countries.id,name_en as name,alias,is_visible',
                        'geography.city.region:regions.id,country_id,name_en as name,alias,is_visible',
                        'geography.city.country:countries.id,name_en as name,alias,is_visible',
                        'geography.city:cities.id,country_id,region_id,name_en as name,alias,is_visible'
                    );
                    $objectSelect = ['objects.id', 'objects.title_en as title', 'objects.alias'];
                    $medicalProfilesSelect = ['medical_profiles.id', 'medical_profiles.name_en as name', 'medical_profiles.alias'];
                    $therapiesSelect = ['therapy.id', 'therapy.name_en as name', 'therapy.alias'];
                    $diseasesSelect = ['diseases.id', 'diseases.name_en as name', 'diseases.alias'];
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $publication = $this->getPublicationLocaleFilter($publication, $locale);

            $publication = $this->getWithObjectLocaleFilter($publication, $locale, 'objects', $objectSelect);
            $publication = $this->getWithMedicalProfilesLocaleFilter($publication, $locale, 'medicalProfiles', $medicalProfilesSelect);
            $publication = $this->getWithTherapiesLocaleFilter($publication, $locale, 'therapies', $therapiesSelect);
            $publication = $this->getWithDiseasesLocaleFilter($publication, $locale, 'diseases', $diseasesSelect);

        } else {
            $publication = Publication::where('id', $publicationId);
            $publication->with(
                'type:id,name_ru,alias',
                'partner:id,organisation_short_name_ru,logo,alias',
                'partner.type:id,name_ru,alias',
                'seo',

                'objects:objects.id,objects.title_ru,objects.alias',
                'medicalProfiles:medical_profiles.id,medical_profiles.name_ru,medical_profiles.alias',
                'therapies:therapy.id,therapy.name_ru,therapy.alias',
                'diseases:diseases.id,diseases.name_ru,diseases.alias',

                'geography.country:countries.id,name_ru,alias,is_visible',
                'geography.region:regions.id,country_id,name_ru,alias,is_visible',
                'geography.region.country:countries.id,name_ru,alias,is_visible',
                'geography.city.region:regions.id,country_id,name_ru,alias,is_visible',
                'geography.city.country:countries.id,name_ru,alias,is_visible',
                'geography.city:cities.id,country_id,region_id,name_ru,alias,is_visible'
                );
        }

        if (!is_null($partnerId)){
            $publication->where('partner_id', $partnerId);
        }
        $publication->with('publicationFiles');
        $publication = $publication->first();
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        $publication->hydrateModeration();
        $publication->prepareModerationImage($locale);

        return $publication;
    }

    /**
     * Удаление публикации
     *
     * @param int $publicationId
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function delete(int $publicationId, ?int $partnerId = null)
    {
        if (is_null($partnerId)){
            $publication = Publication::find($publicationId);
        } else {
            $publication = Publication::where('id', $publicationId)->where('partner_id', $partnerId)->first();
        }
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        if (!is_null($publication->images))
            foreach ($publication->images as $image){
                $this->deleteImage($image->id);
            }

        if (!is_null($publication->files))
            foreach ($publication->files as $file){
                Storage::delete('files/' . basename($file->file));
                $file->delete();
            }
        if (!is_null($publication->seo))
            $publication->seo()->delete();

        if (!is_null($publication->publicationFiles))
            foreach ($publication->publicationFiles as $file){
                $this->deleteFile($file->id);
            }

        $publication->delete();
    }

    /**
     * ====================== Publication Gallery ===================================================================
     */

    /**
     * Добавление изображения к публикации
     *
     * @param Request $request
     * @param bool $fromAdmin
     * @param int|null $partnerId
     * @return PublicationGallery
     * @throws ApiProblemException
     */
    public function addImage(Request $request, bool $fromAdmin = false, ?int $partnerId = null)
    {
        if (!is_null($partnerId)){
            $publication = Publication::where('id', $request->get('publication_id'))
                ->where('partner_id', $partnerId)->first();
            if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);
        }
        $imageGallery = new PublicationGallery();
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('publication_gallery');
            $imageGallery->image = Storage::url($path);
            $this->optimizeImage($imageGallery->image, 'publication_gallery');
        }
        $data = $request->only('publication_id', 'description', 'sorting_rule');
        foreach ($data as $field=>$value){
            $imageGallery->$field = $value;
        }

        if ($fromAdmin) $imageGallery->moderation_status_id = ModerationStatus::MODERATE_OK;
        $imageGallery->save();
        $imageGallery = PublicationGallery::find($imageGallery->id);

        return $imageGallery->prepareFormatResponseImage($imageGallery);
    }

    /**
     * Редактирование изображения
     *
     * @param array $data
     * @param int $imageId
     * @param null|int $partnerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function editImage(array $data, int $imageId, ?int $partnerId = null)
    {
        if (!is_null($partnerId)){
            $imageGallery = PublicationGallery::find($imageId);
            if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);

            $publication = Publication::where('id', $imageGallery->publication_id)
                ->where('partner_id', $partnerId)->first();
            if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);
        }

        $imageGallery = PublicationGallery::find($imageId);
        if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);

        foreach ($data as $field=>$value){
            if ($field == 'is_main' && $value){
                PublicationGallery::where('publication_id', $imageGallery->publication_id)->update(['is_main' => false]);
                $imageGallery->is_main = true;
            } elseif ($field == 'moderation'){
                if (!is_array($value)) $value = json_decode($value, true);
                $this->moderate($value, $imageId);
            } else {
                $imageGallery->$field = $value;
            }
        }
        $imageGallery->save();
        $imageGallery = PublicationGallery::find($imageGallery->id);

        return $imageGallery->prepareFormatResponseImage($imageGallery);
    }

    /**
     * Сортировка изображений
     *
     * @param array $sorting
     * @param int $publicationId
     * @param null|int $partnerId
     * @return array
     * @throws ApiProblemException
     */
    public function sortingImage(array $sorting, int $publicationId, ?int $partnerId = null)
    {
        if (!is_null($partnerId)){
            $publication = Publication::where('id', $publicationId)->where('partner_id', $partnerId)->first();
            if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);
        }
        $sortingRule = 0;
        foreach ($sorting as $imageId){
            $image = PublicationGallery::where('id', $imageId)->where('publication_id', $publicationId)->first();
            if (is_null($image))
                throw new ApiProblemException("Изображение с ID={$imageId} не найдено", 404);
            $image->sorting_rule = $sortingRule;
            $image->save();
            $sortingRule++;
        }
        return $sorting;
    }

    /**
     * Получение изображений
     *
     * @param int $publicationId
     * @param int|null $partnerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getImages(int $publicationId, ?int $partnerId = null)
    {
        if (!is_null($partnerId)){
            $publication = Publication::where('id', $publicationId)
                ->where('partner_id', $partnerId)->first();
            if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);
        }
        $images = PublicationGallery::where('publication_id', $publicationId)->orderBy('sorting_rule', 'asc')->get();
        $resultImages = [];
        foreach ($images as $image){
            $resultImages[] = $image->prepareFormatResponseImage($image);
        }

        return $resultImages;
    }

    /**
     * Удаление изображения
     *
     * @param int $imageId
     * @param null|int $partnerId
     * @throws ApiProblemException
     */
    public function deleteImage(int $imageId, ?int $partnerId = null)
    {
        if (!is_null($partnerId)){
            $imageGallery = PublicationGallery::find($imageId);
            if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);

            $publication = Publication::where('id', $imageGallery->publication_id)
                ->where('partner_id', $partnerId)->first();
            if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);
        }
        $imageGallery = PublicationGallery::find($imageId);
        if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);
        Storage::delete('publication_gallery/' . basename($imageGallery->image));
        $imageGallery->delete();
    }

    /**
     * ====================== Relations ============================================================================
     */

    /**
     * Редактирование связей публикации
     *
     * @param int $publicationId
     * @param array|null $medicalProfileIds
     * @param array|null $therapyIds
     * @param array|null $diseaseIds
     * @param array|null $objectIds
     * @param int|null $countryId
     * @param int|null $regionId
     * @param int|null $cityId
     * @throws ApiProblemException
     */
    public function relations(int $publicationId,
                                ?array $medicalProfileIds, ?array $therapyIds, ?array $diseaseIds, ?array $objectIds,
                                ?int $countryId, ?int $regionId, ?int $cityId)
    {
        $publication = Publication::find($publicationId);
        if (is_null($publication)) throw new ApiProblemException('Публикация не найдена', 404);

        if (!empty($medicalProfileIds)){

            DB::transaction(function () use($medicalProfileIds, $publication){
                PublicationMedicalProfile::where('publication_id', $publication->id)->delete();
                foreach ($medicalProfileIds as $id){
                    $attach = MedicalProfile::find($id);
                    if (is_null($attach))
                        throw new
                        ApiProblemException("Не найден мед. профиль с ID={$id}", 404);
                    $publication->medicalProfiles()->attach($attach);
                }
            });
        }
        if (!empty($therapyIds)){

            DB::transaction(function () use($therapyIds, $publication){
                PublicationTherapy::where('publication_id', $publication->id)->delete();
                foreach ($therapyIds as $id){
                    $attach = Therapy::find($id);
                    if (is_null($attach))
                        throw new
                        ApiProblemException("Не найден метод лечения с ID={$id}", 404);
                    $publication->therapies()->attach($attach);
                }
            });
        }
        if (!empty($diseaseIds)){

            DB::transaction(function () use($diseaseIds, $publication){
                PublicationDisease::where('publication_id', $publication->id)->delete();
                foreach ($diseaseIds as $id){
                    $attach = Disease::find($id);
                    if (is_null($attach))
                        throw new
                        ApiProblemException("Не найдено заболевание с ID={$id}", 404);
                    $publication->diseases()->attach($attach);
                }
            });
        }
        if (!empty($objectIds)){

            DB::transaction(function () use($objectIds, $publication){
                PublicationObject::where('publication_id', $publication->id)->delete();
                foreach ($objectIds as $id){
                    $attach = ObjectPlace::find($id);
                    if (is_null($attach))
                        throw new
                        ApiProblemException("Не найден объект - санаторий с ID={$id}", 404);
                    $publication->objects()->attach($attach);
                }
            });
        }
        if (!is_null($countryId) || !is_null($regionId) || !is_null($cityId)){
            $pivot = PublicationGeography::where('publication_id', $publication->id)->first();
            if (is_null($pivot)) $pivot = new PublicationGeography;
            $pivot->publication_id = $publication->id;
            $pivot->country_id = $countryId;
            $pivot->region_id = $regionId;
            $pivot->city_id = $cityId;
            $pivot->save();
        }
    }

    /**
     * Загрузка файла партнера.
     *
     * @param int $publicationId
     * @param Request $request
     * @return PublicationFile
     * @throws ApiProblemException
     */
    public function addFile(int $publicationId, Request $request)
    {
        if ($request->hasFile('file')){
            $path = $request->file('file')->store('publication_files');
            $file = new PublicationFile();
            $file->file = Storage::url($path);
            $file->publication_id = $publicationId;
            $file->description = $request->get('description');
            $file->save();
            return $file;
        } else throw new ApiProblemException('файл не отправлен', 422);
    }

    /**
     * Удаление файла партнера.
     *
     * @param int $fileId
     * @param int|null $publicationId
     * @throws ApiProblemException
     */
    public function deleteFile(int $fileId, ?int $publicationId = null)
    {
        $file = PublicationFile::where('id', $fileId);
        if (!is_null($publicationId))
            $file->where('publication_id', $publicationId);
        $file = $file->first();

        if (is_null($file))
            throw new ApiProblemException('Файл не найден', 404);

        Storage::delete('publication_files/' . basename($file->file));
        $file->delete();
    }

    /**
     * Модерация изображений
     *
     * @param array $moderateData
     * @param $imageId
     * @throws ApiProblemException
     */
    public function moderate(array $moderateData, $imageId)
    {
        $image = PublicationGallery::find($imageId);
        if (is_null($image))
            throw new ApiProblemException('Изображение не найдено', 404);

        if (isset($moderateData['approve'])){
            if ($moderateData['approve']){
                $image->moderation_status_id = ModerationStatus::MODERATE_OK;
                $image->moderation_message = null;
                $user = $image->publication->partner->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationAcceptNotification( "Изображение публикации") );
                }
            } else {
                if (empty($moderateData['message']))
                    throw new ApiProblemException('Необходим опредоставить сообщение о причине отказа', 422);

                $image->moderation_status_id = ModerationStatus::MODERATE_REJECT;
                $image->moderation_message = $moderateData['message'];
                $user = $image->publication->partner->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationRejectNotification( "Изображение публикации", $moderateData['message']) );
                }
            }
            $image->save();
        } else {
            throw new ApiProblemException('Не верный формат модерации', 422);
        }
    }
}
