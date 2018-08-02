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
p($arResult['CURRENT_REGION']);
if (!$arResult['CURRENT_REGION']) {
    $arResult['CURRENT_REGION'] = $this->getDefaultRegion();
}
if (!$arParams['WITHOUT_TEMPLATE'])
    $this->IncludeComponentTemplate();