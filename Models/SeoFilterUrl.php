<?php

namespace App\Models;

use App\Exceptions\ApiProblemException;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SeoFilterUrl extends Model
{
    use LogsActivity;

    public $timestamps = null;

    protected $guarded = [];
    protected static $logUnguarded = true;

    /**
     * LogsActivity, название события
     *
     * @param string $eventName
     * @return string
     */
    public function getDescriptionForEvent(string $eventName): string
    {
        if ($eventName == 'created')
            $event = 'добавлен';

        elseif ($eventName == 'updated')
            $event = 'обновлен';

        elseif ($eventName == 'deleted')
            $event = 'удален';
        else
            $event = $eventName;

        return "Url фильтра объектов был {$event}";
    }

    /**
     * Подготовка мета данных
     *
     * @param $locale
     * @return array
     * @throws ApiProblemException
     */
    public function getCustomMetaData($locale)
    {
        switch ($locale){
            case 'ru' :
                $customSeo = [
                    'id' => $this->id,
                    'url' => $this->url,
                    'title' => $this->title_ru,
                    'meta_description' => $this->description_ru,
                    'text' => $this->text_ru,
                ];
                break;

            case 'en' :
                $customSeo = [
                    'id' => $this->id,
                    'url' => $this->url,
                    'title' => $this->title_en,
                    'meta_description' => $this->description_en,
                    'text' => $this->text_en,
                ];
                break;

            default :
                throw new ApiProblemException('Не поддерживаемая локаль в мета-данных url фильтра', 422);
        }

        return $customSeo;
    }
}
