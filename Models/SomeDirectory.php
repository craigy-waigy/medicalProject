<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class SomeDirectory extends Model
{
    use LogsActivity;

    public $timestamps = null;

    const TYPE_MESSENGERS = 'messengers';                   //Месенджеры
    const TYPE_CLIMATIC_FACTORS = 'climatic_factors';       //Климатические факторы
    const TYPE_HEALING_MUD = 'healing_mud';                 //Лечебная грязь
    const TYPE_CERTIFIED_PERSONAL = 'certified_personal';   //Сертифицированный персонал
    const TYPE_CONTINGENT = 'contingent';                   //Контингент
    const TYPE_ROOM_EQUIPMENT = 'room_equipment';           //Оборудование номеров
    const TYPE_MINI_BAR = 'mini_bar';                       //Состав мини бара
    const TYPE_FOR_DISABILITIES = 'for_disabilities';       //Инфраструктура для инвалидов
    const TYPE_RELIEF = 'relief';                           //Рельеф
    const TYPE_WATER = 'water_type';                        //Тип минеральной воды
    const TYPE_DRINKING_WATER = 'drinking_water_type';      //Тип питьевой воды
    const TYPE_BEACH = 'beach_type';                        //Тип пляжа
    const TYPE_RESERVOIR = 'reservoir_type';                //Тип водоёма
    const TYPE_POOL = 'pool_type';                          //Тип бассейна
    const TYPE_SLEEPING_PLACE_EQUIPMENT = 'sleeping_place_equipment'; //Оборудование спальных мест
    const TYPE_RESTROOM_EQUIPMENT = 'restroom_equipment';   //Оборудование туалетной комнаты
    const TYPE_FOODS = 'foods';                             //Питание
    const TYPE_SPORT_SERVICES = 'sport_services';           //Спортивно - оздоровительные услуги
    const TYPE_OTHER_SERVICES = 'other_services';           //Дополнительные услуги
    const TYPE_ETH_AVAIL = 'ethernet_availability';         //Доступность проводного интернета
    const TYPE_WIFI_PLACES = 'wifi_places';                 //Доступность беспроводного интернета

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

        return "Справочник \"{$this->type}\" был {$event}";
    }
}
