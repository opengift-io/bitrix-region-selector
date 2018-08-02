<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$arComponentDescription = array(
	"NAME" => 'Определение региона',
	"DESCRIPTION" => 'Определение региона по IP',
	"ICON" => "/images/icon.jpg",
	"SORT" => 10,
	"CACHE_PATH" => "Y",
	"PATH" => array(
		"ID" => "mango",
		"CHILD" => array(
			"ID" => "region_set",
			"NAME" => 'Определение региона',
			"SORT" => 10,
		),
	),
);