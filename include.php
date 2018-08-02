<?php

IncludeModuleLangFile(__FILE__);

if (!function_exists('htmlspecialcharsbx')) {
    function htmlspecialcharsbx($string, $flags=ENT_COMPAT) {
        return htmlspecialchars($string, $flags, (defined('BX_UTF') ? 'UTF-8' : 'ISO-8859-1'));
    }
}

global $DBType;

require_once dirname(__FILE__) . '/vendor/autoload.php';

CModule::AddAutoloadClasses(
    'opengift.region',
    array(
        'OpenGift\Bitrix\Admin\AdminList' => 'classes/general/AdminList.php',
        'OpenGift\Bitrix\Admin\AdminForm' => 'classes/general/AdminForm.php',
        'OpenGift\Bitrix\Composer' => 'classes/general/Composer.php',
        'OpenGift\BitrixRegionManager\RegionManager' => 'classes/general/Region.php',
        'OpenGift\Dev\Models\DataManager' => 'lib/DataManager.php',
        'OpenGift\Dev\PropertyTypes\PropCity' => 'lib/props/PropCity.php',
        'OpenGift\BitrixRegionManager\FilialTable' => 'lib/Filial.php',
        'OpenGift\BitrixRegionManager\CityTable' => 'lib/City.php',
    )
);
