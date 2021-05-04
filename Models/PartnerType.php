<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerType extends Model
{
    /**
     * Партнеры
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function partners()
    {
        return $this->hasMany(Partner::class);
    }

    /**
     * Сео информация
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne(SeoInformation::class)->select(['partner_type_id', 'for', 'h1_ru', 'h1_en', 'title_ru', 'title_en', 'url', 'meta_description_ru',
            'meta_description_en', 'meta_keywords_ru', 'meta_keywords_en']);
    }
}
