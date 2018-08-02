<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class CBitrixSetRegion extends CBitrixComponent
{

    const DEFAULT_REGION_CODE = 'Москва';
    static $regions = [];
    static $alphabet = [];
    static $cntIsMango = 0;
    static $cntFull = 0;
    static $currentRegion;

    public function onPrepareComponentParams($arParams) {
        return $arParams;
    }

    public static function getGeoIpLocation($sIP)
    {
        return \OpenGift\BitrixRegionManager\RegionManager::getRegionByIp($sIP);
    }

    public static function getRealIp()
    {
        if ($_REQUEST['ip']) {
            return $_REQUEST['ip'];
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ($_SERVER['HTTP_X_FORWARDED_FOR']) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    public static function saveRegion($region)
    {
        global $APPLICATION;
        $APPLICATION->set_cookie(REGION_MANAGER_COOKIE_NAME, json_encode($region), strtotime( '+365 days' ), "/");
        $APPLICATION->set_cookie(REGION_MANAGER_COOKIE_CONFIRM_NAME, 'Y', strtotime( '+365 days' ), "/");
    }

    public static function getSavedRegion()
    {
        global $APPLICATION;
        $cookie = $APPLICATION->get_cookie(REGION_MANAGER_COOKIE_NAME);
        if ($cookie) $cookie = json_decode($cookie);
        return $cookie ?: [];
    }

    public static function setCurrentRegion($region = false) {
        if (!$region) {
            $region = self::getSavedRegion();

            if (!$region) {
                $loc = self::getGeoIpLocation(self::getRealIp());
                $region = self::getRegionByName($loc->city->name);
            }
        }
        self::$currentRegion = $region ? (array)$region : null;
    }

    public static function getCurrentRegion() {
        return self::$currentRegion;
    }

    public static function getRegionsList() {
        $arList = [];
        $rs = \OpenGift\BitrixRegionManager\CityTable::getList([
            'order' => ['sort' => 'asc']
        ]);
        while ($ar = $rs->fetch()) {
            $arList[] = $ar;
        }
        return $arList;
    }

    public static function getDefaultRegion() {
        return \OpenGift\BitrixRegionManager\CityTable::getList([
            'order' => ['sort' => 'asc']
        ])->fetch();
    }

    public static function getRegionByName($name) {
        return \OpenGift\BitrixRegionManager\CityTable::getList([
            'order' => ['sort' => 'asc'],
            'filter' => ['name' => $name]
        ])->fetch();
    }

    public static function getRegionsStructured() {
        $arList = static::getRegionsList();
        $arDistricts = [];
        foreach ($arList as $city) {
            if (!$arDistricts[$city['district']]) {
                $arDistricts[$city['district']] = [];
            }
            if (!$arDistricts[$city['district']][$city['region']]) {
                $arDistricts[$city['district']][$city['region']] = [];
            }
            $arDistricts[$city['district']][$city['region']][] = $city;
        }
        return $arDistricts;
    }
}