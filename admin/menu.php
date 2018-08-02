<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
IncludeModuleLangFile(__FILE__);

$aMenu = array();

if ($APPLICATION->GetGroupRight('opengift.regions')!='D') {
	$aMenu = array(
		'parent_menu' => 'global_menu_services',
		'section' => 'opengift.region',
		'sort' => 85,
		'text' => 'Regions',
		'title' => 'Regions',
		'url' => '',
		'icon' => 'mango_seo_menu_icon',
		'page_icon' => 'mango_seo_page_icon',
		'items_id' => 'menu_mango_seo',
		'items' => array()
	);
	$aMenu['items'][] = array(
		'items_id' => 'menu_opengift_region_update',
		'text' => 'Update regions',
		'title' => 'Update regions',
		'url' => 'opengift_region_update_regions.php?lang='.LANGUAGE_ID,
		'more_url' => array(
			'mango_seo_sef_edit.php'
		),
		'items' => array(
		)
	);
	$aMenu['items'][] = array(
		'items_id' => 'menu_opengift_region_regions',
		'text' => 'Regions',
		'title' => 'Regions',
		'url' => 'opengift_region_region_list.php?lang='.LANGUAGE_ID,
		'more_url' => array(
			'mango_seo_pages_edit.php'
		),
		'items' => array(
		)
	);
}

if (!empty($aMenu)) {
	return $aMenu;
} else {
	return false;
}