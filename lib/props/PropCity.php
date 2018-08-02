<?php
/**
 * Created in Heliard.
 * User: gvammer gvammer@rambler.ru
 * Date: 02.08.2018
 * Time: 20:01
 */

namespace OpenGift\Dev\PropertyTypes;

use OpenGift\BitrixRegionManager\CityTable;

if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();


\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

/**
 * Тип свойства привязка к разделам текущего инфоблока
 * Class propLinkToSections
 * @package Mango\Dev\PropertyTypes
 */
class PropCity
{
    public static function GetUserTypeDescription()
    {
        return array(
            'PROPERTY_TYPE' => 'N',
            'USER_TYPE' => 'opengift_prop_city',
            'DESCRIPTION' => "Property city list",
            'GetPropertyFieldHtml' => array(__CLASS__, 'GetPropertyFieldHtml'),
            'GetPropertyFieldHtmlMulty' => array(__CLASS__, 'GetPropertyFieldHtmlMulty'),
        );
    }

    public static function GetPropertyFieldHtml($arProperty, $arValue, $strHTMLControlName)
    {

        $resSections = CityTable::getList([
            'order' => ['name' => 'asc']
        ]);

        $strResult = '<select name="'.$strHTMLControlName['VALUE'].'">';
        $strResult .= '<option value="">--</option>';
        while($arSection = $resSections->Fetch()){
            $strResult .= '<option value="'.$arSection['id'].'"';
            if($arValue['VALUE'] == $arSection['id'])
                $strResult .= ' selected';

            $strResult .= '>'.$arSection['name'].'</option>';
        }
        $strResult .= '</select>';

        return $strResult;
    }


    public static function GetPropertyFieldHtmlMulty($arProperty, $arValue, $strHTMLControlName)
    {
        $resSections = CityTable::getList([
            'order' => ['name' => 'asc']
        ]);

        $strResult = '<select name="'.$strHTMLControlName['VALUE'].'" multiple size="'.$arProperty['MULTIPLE_CNT'].'">';
        $strResult .= '<option value="">--</option>';
        while($arSection = $resSections->Fetch()){
            $strResult .= '<option value="'.$arSection['id'].'"';
            if($arValue['VALUE'] == $arSection['id'])
                $strResult .= ' selected';

            $strResult .= '>'.$arSection['name'].'</option>';
        }
        $strResult .= '</select>';

        return $strResult;
    }
}