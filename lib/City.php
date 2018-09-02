<?php
namespace OpenGift\BitrixRegionManager;

use OpenGift\Dev\Models\DataManager;
use \Bitrix\Main\Entity;

class CityTable extends DataManager
{
    public static $fields;
    
    public static function getFields(){

        if(!empty(static::$fields))
            return static::$fields;

        $arFields = [];
        foreach (static::getMap() as $field){
            /**
             * @var $field Entity\ScalarField
             */

            $fieldName = ($field instanceof Entity\ReferenceField)? $field->getName() : $field->getColumnName();
            $arFields[$fieldName] = ($field instanceof Entity\ReferenceField)?'reference':$field->getDataType();
        }

        static::$fields = $arFields;

        return $arFields;
    }


    public static function getEnumFieldValues(){
        $arFieldValues = [];
        foreach (static::getMap() as $field){
            /**
             * @var $field Entity\ScalarField
             */
            if($field->getDataType() == 'enum'){
                /**
                 * @var $field Entity\EnumField
                 */
                $arFieldValues[$field->getColumnName()] = $field->getValues();
            }
        }
        return $arFieldValues;
    }


    /**
     * @param $data
     * @return \Bitrix\Main\Entity\AddResult Contains ID of inserted row
     * @throws \Exception
     */
    public static function add($data){
        $arFields = static::getFields();
        if(array_key_exists('timestamp', $arFields))
            $data['timestamp'] = new \Bitrix\Main\Type\DateTime(ConvertTimeStamp(false, 'FULL'));

        if(array_key_exists('date', $arFields))
            $data['date'] = new \Bitrix\Main\Type\DateTime(ConvertTimeStamp(false, 'SHORT'));


        return parent::add($data);
    }

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
            new Entity\IntegerField('sort'),
            new Entity\StringField('lat'),
            new Entity\StringField('lon')
        );
    }
}