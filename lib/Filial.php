<?php
namespace OpenGift\BitrixRegionManager;

use OpenGift\Dev\Models\DataManager;
use \Bitrix\Main\Entity;

class FilialTable extends DataManager
{
    public static function getFilePath()
    {
        return __FILE__;
    }

    public static function getTableName()
    {
        return 'b_opengift_filial';
    }

    public static function getMap()
    {
        return array(
            new Entity\IntegerField('id', ['primary' => true, 'autocomplete' => true]),
            new Entity\DatetimeField('timestamp'),
            new Entity\IntegerField('city'),
            new Entity\StringField('phone'),
            new Entity\StringField('contacts'),
            new Entity\StringField('description'),
        );
    }
}