<?php
/**
 * Created in Heliard.
 * User: gvammer gvammer@rambler.ru
 * Date: 02.08.2018
 * Time: 17:44
 */
error_reporting(1);
define('NO_KEEP_STATISTIC', true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
if (!check_bitrix_sessid()) die();

if ($_REQUEST['action']) {
    \Bitrix\Main\Loader::includeModule('opengift.region');

    $result = ['result' => null];

    $APPLICATION->IncludeComponent('opengift.region:region.set', '', ['WITHOUT_TEMPLATE' => 'Y']);
    switch ($_REQUEST['action']) {
        case "regionSet":
            $cityId = intval($_REQUEST['city']);
            $city = \OpenGift\BitrixRegionManager\CityTable::getById($cityId)->fetch();

            if ($city) {
                CBitrixSetRegion::saveRegion($city);
                $result = ['result' => 1];
            } else {
                $result['error'] = 'City doesn\'t exist';
            }
            break;
    }

    echo json_encode($result);
}