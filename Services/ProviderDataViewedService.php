<?php

namespace App\Services;

use App\Exceptions\ApiProblemException;
use App\Models\ProviderDataViewed;
use Illuminate\Http\Request;
use App\Libraries\Models\PaginatorFormat;

class ProviderDataViewedService
{
    /**
     * @var PaginatorFormat
     */
    protected $paginatorFormat;

    /**
     * MoodService constructor.
     */
    public function __construct()
    {
        $this->paginatorFormat = new PaginatorFormat();
    }

    /**
     * Использованы ли данные от поставщика для поля
     *
     * @return ProviderDataViewed
     */
    public function getViewed($objectId)
    {
        $providerDataViewed = ProviderDataViewed::where('object_id', $objectId)->get();

        return $this->formatter($providerDataViewed);
    }

    /**
     * Форматирование ответа
     *
     * @return array
     */
    public function formatter($vieweds)
    {
        $result = [];

        if ($vieweds) {
            foreach ($vieweds as $viewed) {

                $viewed_fields = [];
                if ($viewed->viewed_fields) {
                    foreach ($viewed->viewed_fields as $viewed_field) {
                        $viewed_fields[$viewed_field] = true;
                    }
                }

                $result[] = [
                    'object_id' => $viewed->object_id,
                    'provider_id' => $viewed->provider_id,
                    'viewed_fields' => $viewed_fields
                ];
            }
        }

        return $result;
    }


    /**
     * Создание записи о просмотренности инфы от поставщика
     *
     * @return ProviderDataViewed
     */
    public function markViewed($objectId, $providerId, $viewedField)
    {
        $providerDataViewed = ProviderDataViewed::where('object_id', $objectId)
            ->where('provider_id', $providerId)
            ->first();

        if (!$providerDataViewed) {
            $providerDataViewed = new ProviderDataViewed();
            $providerDataViewed->object_id = $objectId;
            $providerDataViewed->provider_id = $providerId;
        }

        $viewedArray = $providerDataViewed->viewed_fields ?? [];
        if (!is_array($viewedArray)) {
            $viewedArray = json_decode($providerDataViewed->viewed_fields);
        }

        array_push($viewedArray, $viewedField);

        $viewedArray = array_unique($viewedArray);

        $providerDataViewed->viewed_fields = $viewedArray;

        $providerDataViewed->save();

        return $providerDataViewed;
    }

    /**
     * Удаление записи о просмотренности инфы от поставщика
     *
     * @return ProviderDataViewed|boolean
     */
    public function markUnviewed($objectId, $providerId, $viewedField)
    {
        $providerDataViewed = ProviderDataViewed::where('object_id', $objectId)
            ->where('provider_id', $providerId)
            ->first();

        if (!$providerDataViewed) return false;

        $viewedArray = $providerDataViewed->viewed_fields;

        if (!is_array($viewedArray)) {
            $viewedArray = json_decode($providerDataViewed->viewed_fields);
        }

        $key = array_search($viewedField, $viewedArray);
        if (isset($key)) unset($viewedArray[$key]);

        $viewedArray= array_values($viewedArray);

        $providerDataViewed->viewed_fields = $viewedArray;

        $providerDataViewed->save();

        return $providerDataViewed;
    }

}
