<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\ModerationPartner;
use App\Models\ModerationStatus;
use App\Models\Partner;
use App\Models\PartnerFile;
use App\Models\PartnerGallery;
use App\Models\PartnerType;
use App\Notifications\ModerationAcceptNotification;
use App\Notifications\ModerationRejectNotification;
use App\Traits\ImageTrait;
use App\Traits\LocaleControlTrait;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;
use Illuminate\Support\Facades\Storage;

class PartnerService
{
    use ImageTrait;
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * PartnerService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Типы партнеров
     */

    /**
     * Добавление нового типа партнера
     *
     * @param Request $request
     * @return PartnerType
     */
    public function addType(Request $request)
    {
        $type = new PartnerType();
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('partner_logo');
            $type->image = Storage::url($path);
        }
        $data = $request->only('name_ru', 'name_en');
        foreach ($data as $field=>$value){
            $type->$field = $value;
        }
        $type->save();

        return $type;
    }

    /**
     * Редактирование типа партнера
     *
     * @param Request $request
     * @param int $partnerTypeId
     * @return mixed
     * @throws ApiProblemException
     */
    public function editType(Request $request, int $partnerTypeId)
    {
        $type = PartnerType::find($partnerTypeId);
        if (is_null($type)) throw new ApiProblemException('Тип партнера не найден', 404);

        if ($request->hasFile('image')){
            $path = $request->file('image')->store('partner_logo');
            Storage::delete('partner_logo/' . basename($type->image));
            $type->image = Storage::url($path);
        }
        $data = $request->only('name_ru', 'name_en');
        foreach ($data as $field=>$value){
            $type->$field = $value;
        }
        $type->save();

        return $type;
    }

    /**
     * Получение всех типов партнера
     *
     * @return PartnerType[]|\Illuminate\Database\Eloquent\Collection
     * @throws
     */
    public function allTypes(?string $locale = null)
    {
        if (!is_null($locale)){
            switch ($locale){
                case 'ru' :
                    $types = PartnerType::select([ 'id', 'name_ru as name', 'image', 'alias' ])->whereNotNull('alias');
                    $types->where('name_ru', '<>', '')->whereNotNull('name_ru');
                    break;

                case 'en' :
                    $types = PartnerType::select([ 'id', 'name_en as name', 'image', 'alias' ])->whereNotNull('alias');
                    $types->where('name_en', '<>', '')->whereNotNull('name_en');
                    break;

                default :
                    throw new ApiProblemException('Локаль ' . $locale . ' не поддерживается', 422);
            }
            $types = $types->whereRaw('(SELECT count(*) FROM partners p INNER JOIN partner_types t ON p.partner_type_id = t."id" WHERE p.partner_type_id = partner_types."id" AND deleted_at IS NULL) > 0')
                ->get();
        } else {
            $types = PartnerType::all();
        }

        return $types;
    }

    /**
     * Получение типа партнера
     *
     * @param int $partnerTypeId
     * @return mixed
     * @throws ApiProblemException
     */
    public function getType(?int $partnerTypeId, ?string $locale = null, ?string $alias = null)
    {
        if (!is_null($locale)){
            $type = PartnerType::where('alias', $alias);
            switch ($locale){
                case 'ru' :
                    $type->select([ 'id', 'name_ru as name', 'image', 'alias' ]);
                    $type->with('seo:id,partner_type_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords');
                    $type->where('name_ru', '<>', '')->whereNotNull('name_ru');
                    break;

                case 'en' :
                    $type->select([ 'id', 'name_en as name', 'image', 'alias' ]);
                    $type->with('seo:id,partner_type_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords');
                    $type->where('name_en', '<>', '')->whereNotNull('name_en');
                    break;

                default :
                    throw new ApiProblemException('Локаль ' . $locale . ' не поддерживается', 422);
            }
        } else {
            $type = PartnerType::where('id', $partnerTypeId);
            $type->with('seo');
        }
        $type = $type->first();
        if (is_null($type)) throw new ApiProblemException('Тип партнера не найден', 404);

        return $type;
    }

    /**
     * Удаление типа партнера
     *
     * @param int $partnerTypeId
     * @throws ApiProblemException
     */
    public function deleteType(int $partnerTypeId)
    {
        $type = PartnerType::find($partnerTypeId);
        if (is_null($type)) throw new ApiProblemException('Тип партнера не найден', 404);

        if ($type->partners()->count() > 0)
            throw new ApiProblemException('Невозможно удалить. Есть связанные данные', 422);

        $type->seo()->delete();
        $type->delete();
    }

    /**
     * Партнеры
     */

    /**
     * Создание партнера
     *
     * @param Request $request
     * @return Partner
     */
    public function addPartner(Request $request)
    {
        $data = $request->only(
            'partner_type_id',
            'manager_name_ru',
            'manager_name_en',
            'organisation_short_name_ru',
            'organisation_short_name_en',
            'organisation_full_name_ru',
            'organisation_full_name_en',
            'description_ru',
            'description_en',
            'mail_address_ru',
            'mail_address_en',
            'email',
            'address_ru',
            'address_en',
            'active'
        );
        $partner = new Partner();
        foreach ($data as $field=>$value){
            $partner->$field = $value;
        }
        $telephones = $request->get('telephones') ?? "[]";
        if (!is_array($telephones)) $telephones = json_decode($telephones);
        $partner->telephones = $telephones;

        if ($request->hasFile('logo')){
            $path = $request->file('logo')->store('partner_logo');
            $partner->logo = $this->convertImage($path, 'partner_logo', 'png');
        }
        $partner->save();
        $partner->moderationPartner()->insert(['partner_id' => $partner->id]);

        return $partner;
    }

    /**
     * Редактирование партнера
     *
     * @param Request $request
     * @param int $partnerId
     * @return mixed
     * @throws ApiProblemException
     */
    public function editPartner(Request $request, int $partnerId, bool $fromAccount = false)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);

        if ($fromAccount){
            $data = $request->only('active');
            $moderateData = $request->only(

                'manager_name_ru',
                'manager_name_en',
                'organisation_short_name_ru',
                'organisation_short_name_en',
                'organisation_full_name_ru',
                'organisation_full_name_en',
                'description_ru',
                'description_en',
                'email',
                'mail_address_ru',
                'mail_address_en',
                'address_ru',
                'address_en',
                'telephones'
            );
            $this->saveModerationData($moderateData, $partnerId);

        } else {
            $data = $request->only(
                'partner_type_id',
                'manager_name_ru',
                'manager_name_en',
                'organisation_short_name_ru',
                'organisation_short_name_en',
                'organisation_full_name_ru',
                'organisation_full_name_en',
                'description_ru',
                'description_en',
                'email',
                'mail_address_ru',
                'mail_address_en',
                'address_ru',
                'address_en',
                'active'
            );
            $telephones = $request->get('telephones') ?? "[]";
            if (!is_array($telephones)) $telephones = json_decode($telephones);
            $partner->telephones = $telephones;
        }

        foreach ($data as $field=>$value){
            $partner->$field = $value;
        }

        if ($request->hasFile('logo')){
            $path = $request->file('logo')->store('partner_logo');
            Storage::delete('partner_logo/' . basename($partner->logo));
            $partner->logo = $this->convertImage($path, 'partner_logo', 'png');
        }
        $partner->save();
        $partner->hydrateModeration();

        return $partner;
    }

    /**
     * Сохранение данных на модерацию
     *
     * @param array $data
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function saveModerationData(array $data, int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner))
            throw new ApiProblemException('Партнер не найден', 404);

        $moderationPartner = $partner->moderationPartner;
        if (is_null($moderationPartner)){
            $moderationPartner = new ModerationPartner();
            $moderationPartner->partner_id = $partnerId;
        }
        foreach ($data as $field => $value){
            switch ($field){
                case 'manager_name_ru' :
                    $moderationPartner->manager_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->manager_name_ru = $value;
                    $moderationPartner->manager_name_message = null;
                    break;

                case 'manager_name_en' :
                    $moderationPartner->manager_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->manager_name_en = $value;
                    $moderationPartner->manager_name_message = null;
                    break;

                case 'organisation_short_name_ru' :
                    $moderationPartner->organisation_short_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->organisation_short_name_ru = $value;
                    $moderationPartner->organisation_short_name_message = null;
                    break;

                case 'organisation_short_name_en' :
                    $moderationPartner->organisation_short_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->organisation_short_name_en = $value;
                    $moderationPartner->organisation_short_name_message = null;
                    break;

                case 'organisation_full_name_ru' :
                    $moderationPartner->organisation_full_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->organisation_full_name_ru = $value;
                    $moderationPartner->organisation_full_name_message = null;
                    break;

                case 'organisation_full_name_en' :
                    $moderationPartner->organisation_full_name_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->organisation_full_name_en = $value;
                    $moderationPartner->organisation_full_name_message = null;
                    break;

                case 'description_ru' :
                    $moderationPartner->description_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->description_ru = $value;
                    $moderationPartner->description_message = null;
                    break;

                case 'description_en' :
                    $moderationPartner->description_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->description_en = $value;
                    $moderationPartner->description_message = null;
                    break;

                case 'address_ru' :
                    $moderationPartner->address_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->address_ru = $value;
                    $moderationPartner->address_message = null;
                    break;

                case 'address_en' :
                    $moderationPartner->address_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->address_en = $value;
                    $moderationPartner->address_message = null;
                    break;

                case 'telephones' :
                    $moderationPartner->telephones_status_id = ModerationStatus::ON_MODERATE;
                    if (!is_array($value)) $value = json_decode($value, true);
                    $moderationPartner->telephones = $value;
                    $moderationPartner->telephones_message = null;
                    break;

                case 'email' :
                    $moderationPartner->email_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->email = $value;
                    $moderationPartner->email_message = null;
                    break;

                case 'mail_address_ru' :
                    $moderationPartner->mail_address_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->mail_address_ru = $value;
                    $moderationPartner->mail_address_message = null;
                    break;

                case 'mail_address_en' :
                    $moderationPartner->mail_address_status_id = ModerationStatus::ON_MODERATE;
                    $moderationPartner->mail_address_en = $value;
                    $moderationPartner->mail_address_message = null;
                    break;

            }
        }
        $moderationPartner->save();
    }

    /**
     * Поиск по партнерам
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param null|string $searchKey
     * @param null|string $locale
     * @param array|null $sorting
     * @param array|null $params
     * @return array
     * @throws ApiProblemException
     */
    public function searchPartner(int $page, int $rowsPerPage, ?string $searchKey,
                                  ?array $sorting = null, ?string $locale = null, ?array $params = null)
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

        if (!is_null($locale)){
            $qb->where('active', true);
            switch ($locale){
                case 'ru' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(manager_name_ru) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(organisation_short_name_ru) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(organisation_full_name_ru) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select([
                        'id',
                        'partner_type_id',
                        'manager_name_ru as manager_name',
                        'organisation_short_name_ru as organisation_short_name',
                        'organisation_full_name_ru as organisation_full_name',
                        'address_ru as address',
                        'mail_address_ru as address',
                        'logo',
                        'alias',
                    ])->with(
                        'type:id,name_ru as name,image,alias'
                    )->withCount('moderatedPublication');
                    break;

                case 'en' :
                    $qb->when($searchKey, function ($query, $searchKey){
                        if (!is_null($searchKey)){
                            $query = $query->whereRaw("lower(manager_name_en) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(organisation_short_name_en) LIKE '%{$searchKey}%'");
                            $query = $query->orWhereRaw("lower(organisation_full_name_en) LIKE '%{$searchKey}%'");

                            return $query;
                        }
                    })->select([
                        'id',
                        'partner_type_id',
                        'manager_name_en as manager_name',
                        'organisation_short_name_en as organisation_short_name',
                        'organisation_full_name_en as organisation_full_name',
                        'address_en as address',
                        'mail_address_en as address',
                        'logo',
                        'alias',
                    ])->with(
                        'type:id,name_en as name,image,alias'
                    )->withCount('moderatedPublication');
                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $qb = $this->getPartnerLocaleFilter($qb, $locale);
        } else {
            $qb->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey)) {
                    $query = $query->whereRaw("lower(manager_name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(organisation_short_name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(organisation_full_name_ru) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(manager_name_en) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(organisation_short_name_en) LIKE '%{$searchKey}%'");
                    $query = $query->orWhereRaw("lower(organisation_full_name_en) LIKE '%{$searchKey}%'");

                    return $query;
                }
            });
            $qb->with('type')->withCount('publications');
        }


        if (!empty($params['partner_type_id'])){
            $qb->where('partner_type_id', $params['partner_type_id']);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();
        foreach ($items as $partner){
            $partner->hydrateTypePublications($locale);
        }

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);

    }

    /**
     * Получение партнера
     *
     * @param int|null $partnerId
     * @param null|string $locale
     * @param null|string $alias
     * @param bool|null $fromAccount
     * @return mixed
     * @throws ApiProblemException
     */
    public function getPartner(?int $partnerId, ?string $locale = null, ?string $alias = null, ?bool $fromAccount = false)
    {
        if (!is_null($locale)){
            $partner = Partner::where('alias', $alias)->where('active', true);
            switch ($locale){
                case 'ru' :
                    $partner->select([
                        'id',
                        'partner_type_id',
                        'manager_name_ru as manager_name',
                        'organisation_short_name_ru as organisation_short_name',
                        'organisation_full_name_ru as organisation_full_name',
                        'address_ru as address',
                        'mail_address_ru as mail_address',
                        'description_ru as description',
                        'logo',
                        'alias',
                        'email',
                        'telephones',
                    ])->with(
                        'type:id,name_ru as name,image,alias',
                        'seo:id,partner_id,h1_ru as h1,title_ru as title,url,meta_description_ru as meta_description,meta_keywords_ru as meta_keywords'
                    )->withCount('moderatedPublication');
                    break;

                case 'en' :
                    $partner->select([
                        'id',
                        'partner_type_id',
                        'manager_name_en as manager_name,alias',
                        'organisation_short_name_en as organisation_short_name',
                        'organisation_full_name_en as organisation_full_name',
                        'address_en as address',
                        'mail_address_en as mail_address',
                        'description_en as description',
                        'logo',
                        'alias',
                        'email',
                        'telephones'
                    ])->with(
                        'type:id,name_en as name,image',
                        'seo:id,partner_id,h1_en as h1,title_en as title,url,meta_description_en as meta_description,meta_keywords_en as meta_keywords'
                    )->withCount('moderatedPublication');
                    break;

                    break;

                default :
                    throw new ApiProblemException('Не поддерживаемая локаль', 422);
            }
            $partner = $this->getPartnerLocaleFilter($partner, $locale);
        } else {
            $partner = Partner::where('id', $partnerId);
            $partner->with('type:id,name_ru as name,image', 'seo');
        }
        $partner->with('partnerFiles');
        $partner = $partner->first();
        if (is_null($partner)) throw new ApiProblemException('Партнер не найден', 404);

        $partner->prepareModerationImage($locale);
        $partner->hydrateTypePublications($locale);
        if ($fromAccount)
            $partner->hydrateModeration();

        return $partner;
    }

    /**
     * Удаление партнера
     *
     * @param int $partnerId
     * @throws ApiProblemException
     */
    public function deletePartner(int $partnerId)
    {
        $partner = Partner::find($partnerId);
        if (is_null($partner)) throw new ApiProblemException('Партнер не найден', 404);

        $partner->delete();
    }

    /**
     * Галлерея
     */

    /**
     *  Добавление изображения
     *
     * @param Request $request
     * @param bool $fromAdmin
     * @param int $partnerId
     * @return PartnerGallery
     */
    public function addImage(Request $request, int $partnerId, bool $fromAdmin = false)
    {
        $imageGallery = new PartnerGallery();
        if ($request->hasFile('image')){
            $path = $request->file('image')->store('partner_gallery');
            $imageGallery->image = Storage::url($path);
            $this->optimizeImage($imageGallery->image, 'partner_gallery');
        }
        $data = $request->only('description', 'sorting_rule');
        $imageGallery->partner_id = $partnerId;
        foreach ($data as $field=>$value){
            $imageGallery->$field = $value;
        }

        if ($fromAdmin)
            $imageGallery->moderation_status_id = ModerationStatus::MODERATE_OK;
        $imageGallery->save();
        $imageGallery = PartnerGallery::find($imageGallery->id);

        return $imageGallery->prepareFormatResponseImage($imageGallery);
    }

    /**
     * Редактирование изображения
     *
     * @param array $data
     * @param int $imageId
     * @param int|null $partnerId
     * @return array|null
     * @throws ApiProblemException
     */
    public function editImage(array $data, int $imageId, ?int $partnerId = null)
    {
        if (is_null($partnerId)){
            $imageGallery = PartnerGallery::find($imageId);
        } else {
            $imageGallery = PartnerGallery::where('id', $imageId)->where('partner_id', $partnerId)->first();
        }
        if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);

        foreach ($data as $field=>$value){
            if ($field == 'is_main' && $value){
                PartnerGallery::where('partner_id', $imageGallery->partner_id)->update(['is_main' => false]);
                $imageGallery->is_main = true;
            } elseif ($field == 'moderation'){
                if (!is_array($value)) $value = json_decode($value, true);
                $this->moderate($value, $imageId);
            } else {
                $imageGallery->$field = $value;
            }
        }
        $imageGallery->save();
        $imageGallery = PartnerGallery::find($imageGallery->id);

        return $imageGallery->prepareFormatResponseImage($imageGallery);
    }

    /**
     * Получение галлереи изображений партнера
     *
     * @param int $partnerId
     * @return mixed
     */
    public function getImages(int $partnerId)
    {
        $images = PartnerGallery::where('partner_id', $partnerId)->orderBy('sorting_rule', 'asc')->get();
        $resultImages = [];
        foreach ($images as $image){
            $resultImages[] = $image->prepareFormatResponseImage($image);
        }

        return $resultImages;
    }

    /**
     * Удаление изображения из галлереи
     *
     * @param int $imageId
     * @param int|null $partnerId
     * @throws ApiProblemException
     */
    public function deleteImage(int $imageId, ?int $partnerId = null)
    {
        if (is_null($partnerId)){
            $imageGallery = PartnerGallery::find($imageId);
        } else {
            $imageGallery = PartnerGallery::where('id', $imageId)->where('partner_id', $partnerId)->first();
        }
        if (is_null($imageGallery)) throw new ApiProblemException('Изображение не найдено', 404);

        Storage::delete('partner_gallery/' . basename($imageGallery->image));
        $imageGallery->delete();
    }

    /**
     * Сортировка изображений
     *
     * @param array $sorting
     * @param int $partnerId
     * @return array
     * @throws ApiProblemException
     */
    public function sortingImage(array $sorting, int $partnerId)
    {
        $sortingRule = 0;
        foreach ($sorting as $imageId){
            $image = PartnerGallery::where('id', $imageId)->where('partner_id', $partnerId)->first();
            if (is_null($image))
                throw new ApiProblemException("Изображение с ID={$imageId} не найдено", 404);
            $image->sorting_rule = $sortingRule;
            $image->save();
            $sortingRule++;
        }
       return $sorting;
    }

    /**
     * Загрузка файла партнера.
     *
     * @param int $partnerId
     * @param Request $request
     * @return PartnerFile
     * @throws ApiProblemException
     */
    public function addFile(int $partnerId, Request $request)
    {
        if ($request->hasFile('file')){
            $path = $request->file('file')->store('partner_files');
            $file = new PartnerFile();
            $file->file = Storage::url($path);
            $file->partner_id = $partnerId;
            $file->description = $request->get('description');
            $file->save();
            return $file;
        } else throw new ApiProblemException('файл не отправлен', 422);
    }

    /**
     * Удаление файла партнера.
     *
     * @param int $fileId
     * @param int|null $partnerId
     * @throws ApiProblemException
     */
    public function deleteFile(int $fileId, ?int $partnerId = null)
    {
       $file = PartnerFile::where('id', $fileId);
       if (!is_null($partnerId))
           $file->where('partner_id', $partnerId);
        $file = $file->first();

       if (is_null($file))
           throw new ApiProblemException('Файл не найден', 404);

       Storage::delete('partner_files/' . basename($file->file));
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
        $image = PartnerGallery::find($imageId);
        if (is_null($image))
            throw new ApiProblemException('Изображение не найдено', 404);

        if (isset($moderateData['approve'])){
            if ($moderateData['approve']){
                $image->moderation_status_id = ModerationStatus::MODERATE_OK;
                $image->moderation_message = null;
                $user = $image->partner->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationAcceptNotification( "Изображение") );
                }
            } else {
                if (empty($moderateData['message']))
                    throw new ApiProblemException('Необходим опредоставить сообщение о причине отказа', 422);

                $image->moderation_status_id = ModerationStatus::MODERATE_REJECT;
                $image->moderation_message = $moderateData['message'];
                $user = $image->partner->user;
                if ( !is_null($user) ){
                    if ( $user->email_confirmed )
                        $user->notify( new ModerationRejectNotification( "Изображение", $moderateData['message']) );
                }
            }
            $image->save();
        } else {
            throw new ApiProblemException('Не верный формат модерации', 422);
        }
    }
}
