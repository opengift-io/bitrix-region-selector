<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
define('REGION_MANAGER_COOKIE_CONFIRM_NAME', 'REGION_CONFIRMED');
define('REGION_MANAGER_COOKIE_NAME', 'REGION');
/** @var CBitrixSetRegion $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;
if (!\Bitrix\Main\Loader::includeModule('opengift.region')) return;

CJSCore::Init(['jquery']);
$arResult['LIST'] = $this->getRegionsStructured();
$arResult['LIST_RAW'] = $this->getRegionsList();
$this->setCurrentRegion();
$arResult['CURRENT_REGION'] = $this->getCurrentRegion();

if (!$arResult['CURRENT_REGION']) {
    $arResult['CURRENT_REGION'] = $this->getDefaultRegion();
}
if (\Bitrix\Main\Loader::includeModule('iblock')) {
    $arFilials = [];
    if ($arParams['FILIAL_IBLOCK_ID']) {
        $filials = CIBlockElement::GetList([], ['IBLOCK_ID' => $arParams['FILIAL_IBLOCK_ID']]);
        $arFilials = [];
        while ($filial = $filials->GetNextElement()) {
            $arFilial = $filial->GetFields();
            $arFilial['PROPERTIES'] = $filial->GetProperties();

            $filialCity = $arFilial['PROPERTIES'][$arParams['FILIAL_CITY_PROPERTY']]['VALUE'];
            if ($filialCity == $arResult['CURRENT_REGION']['id']) {
                $arResult['CURRENT_FILIAL'] = $arFilial;
            }
            $arFilials[] = $arFilial;
        }
        if (!$arResult['CURRENT_FILIAL']) {
            foreach ($arFilials as $k => $filial) {
                $filialCity = $filial['PROPERTIES'][$arParams['FILIAL_CITY_PROPERTY']]['VALUE'];
                if ($filialCity) {
                    $arFilials[$k]['CITY'] = \OpenGift\BitrixRegionManager\CityTable::getById($filialCity)->fetch();
                }
            }
        }
    }

    if (!$arResult['CURRENT_FILIAL']) {
        $arRange = [];
        foreach ($arFilials as $k => $filial) {
            $arRange[$k] = pow(floatval($filial['CITY']['lat']) - floatval($arResult['CURRENT_REGION']['lat']), 2) +
                pow(floatval($filial['CITY']['lon']) - floatval($arResult['CURRENT_REGION']['lon']), 2);
        }
        asort($arRange);
        $nearestFilialKey = array_keys($arRange)[0];
        $arResult['CURRENT_FILIAL'] = $arFilials[$nearestFilialKey];
    }
}

uksort($arResult['LIST'], function($a, $b) {
    if ($a == $b) {
        return 0;
    }

    return ($a < $b) ? -1 : 1;
});

foreach ($arResult['LIST'] as $district => &$arRegions) {
    uksort($arRegions, function($a, $b) {
        if ($a == $b) {
            return 0;
        }

        return ($a < $b) ? -1 : 1;
    });
}

$arResult['REGION_CONFIRMED'] = $this->isRegionConfirmed();

if (!$arParams['WITHOUT_TEMPLATE'])
    $this->IncludeComponentTemplate();