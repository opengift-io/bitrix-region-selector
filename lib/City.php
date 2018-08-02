<?php
namespace OpenGift\BitrixRegionManager;

use OpenGift\Dev\Models\DataManager;
use \Bitrix\Main\Entity;

class CityTable extends DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_opengift_city';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('id', ['primary' => true, 'autocomplete' => true]),
            new Entity\StringField('name'),
            new Entity\StringField('region'),
            new Entity\StringField('district'),
            new Entity\IntegerField('sort')
        );
    }
}