<?php
$module = 'opengift.region';
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use \OpenGift\Bitrix\Admin\AdminList;

Loc::loadMessages(__FILE__);
\Bitrix\Main\Loader::includeModule($module);

$list = new AdminList($module, 'id');
$list->generalKey = 'id';
$list->setRights();

$list->setD7class('\OpenGift\BitrixRegionManager\CityTable');
$list->setGroupAction(array(
    'delete' => function ($id) {
        \OpenGift\BitrixRegionManager\CityTable::delete($id);
    }
));
$list->setDetailPage('region_edit.php');
$list->setContextMenu(false);
$list->setHeaders(
    [
        'region' => 'Region',
        'name' => 'Name',
    ]
);
$list->setFilter(array(
    'region' => array('TITLE' => 'Region'),
    'name' => array('TITLE' => 'Name'),
));

$arFilter = $list->makeFilter();
$list->setList(
    \OpenGift\BitrixRegionManager\CityTable::getList(
        array(
            'filter' => $arFilter,
            'select' => array('name', 'region'),
            'order' => array(
                $by => $order
            )
        )
    ),
    array(
        'name' => function ($val) {
            return '<a href="opengift_region_region_edit.php?HASH=' . $val . '&amp;lang=' . LANG . '">' . $val . '</a>';
        },
        'region' => function ($val) {
            return $val;
        }
    )
);
$list->setFooter(array(
    'delete' => '',
));
$list->output();