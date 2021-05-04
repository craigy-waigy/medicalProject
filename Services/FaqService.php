<?php

namespace App\Services;


use App\Exceptions\FaqTagAlreadyUseException;
use App\Exceptions\NotFoundException;
use App\Exceptions\UnsupportLocaleException;
use App\Libraries\Models\PaginatorFormat;
use App\Models\Faq;
use App\Models\FaqTag;
use App\Traits\LocaleControlTrait;

class FaqService
{
    use LocaleControlTrait;

    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * FaqService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Поиск факов
     *
     * @param int $page
     * @param int $rowsPerPage
     * @param string|null $searchKey
     * @param array|null $faqTags
     * @param string|null $locality
     * @param array|null $sorting
     * @return array
     * @throws UnsupportLocaleException
     */
    public function search(int $page, int $rowsPerPage, ?string $searchKey, array $faqTags = null, string $locality = null,
                           ?array $sorting = null)
    {
        $skip = ($page - 1)* $rowsPerPage;
        $searchKey = strip_tags($searchKey);
        $searchKey = mb_strtolower($searchKey);

        $qb = Faq::
        when($faqTags, function ($query, $faqTags){
            if ( !is_null($faqTags)) {
                $cond = '';
                foreach ($faqTags as $value){
                    $cond = $query->whereJsonContains('faq_tags', $value, 'or');
                }
                return $cond;
            }
        })
            ->when($searchKey, function ($query, $searchKey){
                if ( !is_null($searchKey)) {

                    $cond = $query->whereRaw("
                    lower(question_ru) LIKE '%{$searchKey}%' OR
                    lower(question_en) LIKE '%{$searchKey}%' OR
                    lower(answer_ru) LIKE '%{$searchKey}%' OR
                    lower(answer_en) LIKE '%{$searchKey}%'
                    ");

                    return $cond;
                }
            });

        if (!is_null($locality)){
            switch ($locality){
                case 'ru': $qb->select(['id', 'question_ru as question', 'answer_ru as answer']); break;
                case 'en': $qb->select(['id', 'question_en as question', 'answer_en as answer']); break;
                default: throw new UnsupportLocaleException();
            }
            $qb = $this->getFaqLocaleFilter($qb, $locality);
        }
        if ( !is_null($sorting)){
            foreach ($sorting as $field => $direction)
                $qb->orderBy($field, $direction);
        }

        $total = $qb->count();
        $items = $qb->skip($skip)->take($rowsPerPage)->get();

        return $this->paginatorFormat->dataFormat($page, $rowsPerPage, $total, $items);
    }

    /**
     * Добавление нового фака
     *
     * @param array $data
     * @return Faq
     */
    public function addFaq(array $data)
    {
        $faq = new Faq;
        foreach ($data as $field=>$value){
            if ($field == 'faq_tags'){
                if  (!is_array($value)) $value = json_decode($value, true);
            }
            $faq->$field = $value;
        }
        $faq->save();

        return $faq;
    }

    /**
     * Редактирование фака
     *
     * @param array $data
     * @param int $faqId
     * @return mixed
     * @throws NotFoundException
     */
    public function editFaq(array $data, int $faqId)
    {
        $faq = Faq::find($faqId);
        if (is_null($faq)) throw new NotFoundException();
        foreach ($data as $field=>$value){
            if ($field == 'faq_tags'){
                if  (!is_array($value)) $value = json_decode($value, true);
            }
            $faq->$field = $value;
        }
        $faq->save();

        return $faq;
    }

    /**
     * Получение фака
     *
     * @param int $faqId
     * @param null $locale
     * @return mixed
     * @throws NotFoundException
     * @throws UnsupportLocaleException
     */
    public function getFaq(int $faqId, $locale = null)
    {
        if (!is_null($locale)){
            switch ($locale){
                case 'ru':
                    $faq = Faq::where('id', $faqId)->select(['id', 'question_ru as question', 'answer_ru as answer'])
                        ->first();
                    break;

                case 'en': $faq = Faq::where('id', $faqId)->select(['id', 'question_en as question', 'answer_en as answer'])
                        ->first();
                    break;
                default: throw new UnsupportLocaleException();
            }
            $faq = $this->getFaqLocaleFilter($faq, $locale);
        } else $faq = Faq::find($faqId);
        if (is_null($faq)) throw new NotFoundException();

        return $faq;
    }

    /**
     * Удаление фака
     *
     * @param int $faqId
     * @throws NotFoundException
     */
    public function deleteFaq(int $faqId)
    {
        $faq = Faq::find($faqId);
        if (is_null($faq)) throw new NotFoundException();
        $faq->delete();
    }

    /**
     * Получение всех тэгов фака
     *
     * @return FaqTag[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTags()
    {
        return FaqTag::all();
    }

    /**
     * Добавление нового тэга
     *
     * @param array $data
     * @return FaqTag
     */
    public function addTag(array $data)
    {
        $faqTag = new FaqTag;

        $faqTag->name = $data['name'];
        $faqTag->description = $data['description'] ?? null;
        $faqTag->slug = str_slug($data['name']);
        $faqTag->save();

        return $faqTag;
    }

    /**
     * Удаление тэга
     *
     * @param int $faqTagId
     * @throws FaqTagAlreadyUseException
     * @throws NotFoundException
     */
    public function deleteFaqTag(int $faqTagId)
    {
        $faqTag = FaqTag::find($faqTagId);
        if (is_null($faqTag)) throw new NotFoundException();
        $countUse = Faq::whereJsonContains('faq_tags', $faqTag->slug, 'or')->count();
        if ($countUse > 0) throw new FaqTagAlreadyUseException;

        $faqTag->delete();
    }
}
