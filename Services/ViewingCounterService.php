<?php

namespace App\Services;


use App\Exceptions\ApiProblemException;
use App\Models\ViewingCounter;

class ViewingCounterService
{
    /**
     * Сохранение значений счетчика
     *
     * @param array $geoIpParams
     * @param array $params
     * @throws ApiProblemException
     */
    public function counter(array $geoIpParams, array $params)
    {
        $counter = new ViewingCounter;

        foreach ($geoIpParams as $field => $value){
            $counter->$field = $value;
        }
        $emptyParams = true;
        foreach ($params as $field => $value){
            $counter->$field = $value;
            is_null($value) ? : $emptyParams = false;
        }
        if ($emptyParams)
            throw new ApiProblemException('Не отправлено ни одного ID', 400);

        $counter->save();
    }
}
