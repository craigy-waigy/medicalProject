<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublicationType extends Model
{
    public $timestamps = null;

    const ARTICLES = 1;
    const ANALYTICS = 2;
    const RESEARCHES = 2;

    /**
     * Публикации
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publications()
    {
        return $this->hasMany(Publication::class);
    }

    /**
     * Сео информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class)->select(['publication_type_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
            'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }
}
