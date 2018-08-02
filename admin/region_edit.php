<?php
$module = 'opengift.region';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/local/modules/'.$module.'/include.php');
IncludeModuleLangFile(__FILE__);
use OpenGift\Bitrix\Admin\AdminForm;

$id = $_REQUEST['id'];
$form = new AdminForm($module, 'id');
$form->SetRights();
$arValues = $form->GetFromDB('CASDSeoPages::GetByHash');
$APPLICATION->SetTitle(GetMessage('MANGO_SEO_TITLE').$arValues['REAL_TITLE']);

if ($arValues === false) {
	LocalRedirect('mango_seo_pages_list.php?lang='.LANGUAGE_ID);
}

$arFields = array(
	'common' => array(
		'NAME' => GetMessage('MANGO_SEO_PAGE'),
		'TITLE' => '',
		'FIELDS' => array(
			'SITE_ID' => array('NAME' => GetMessage('MANGO_SEO_SITE'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				echo '<a href="/bitrix/admin/site_edit.php?LID='.$arValues['SITE_ID'].'&amp;lang='.LANGUAGE_ID.'">'.$arValues['SITE_ID'].'</a>';
			}),
			'TIMESTAMP_FORMATED' => array('NAME' => GetMessage('MANGO_SEO_TIMESTAMP'), 'REQUIRED' => false, 'TYPE' => 'view', 'HINT' => GetMessage('MANGO_SEO_TIMESTAMP_HINT')),
			'WAIT_TIME' => array('NAME' => GetMessage('MANGO_SEO_WAIT_TIME'), 'REQUIRED' => false, 'TYPE' => 'view'),
			'REAL_TITLE' => array('NAME' => GetMessage('MANGO_SEO_REAL_TITLE'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
                $request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
                $https = $request->isHttps() ? 's' : '';
				if (strlen($arValues['URL'])) {
					echo '<a href="http'.$https.'://'.CASDSeoScaner::GetURLbySiteID($arValues['SITE_ID']).$arValues['URL'].'" target="_blank">'.htmlspecialcharsbx($arValues['REAL_TITLE']).'</a>';
				} else {
					echo htmlspecialcharsbx($arValues['REAL_TITLE']);
				}
			}),
			'STATUS' => array('NAME' => GetMessage('MANGO_SEO_STATUS'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				if ($arValues['STATUS'] == '404') {
					if (strlen(trim($arValues['REDIRECT'])) > 0) {
						echo $arValues['STATUS'];
						echo ' ('.GetMessage('MANGO_SEO_REDIRECT_ISSET').')';
					} else {
						echo '<font color="red">'.$arValues['STATUS'].'</font>';
					}
				} else {
					echo $arValues['STATUS'];
				}
			}),
			'H1' => array('NAME' => GetMessage('MANGO_SEO_H1'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				if (strlen($arValues['H1'])) {
					echo htmlspecialcharsbx($arValues['H1']);
				} else {
					echo '<font color="red">'.GetMessage('MANGO_SEO_NO_SET').'</font>';
				}
			}),
			'DESCRIPTION' => array('NAME' => GetMessage('MANGO_SEO_DESCRIPTION'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				if (strlen($arValues['DESCRIPTION'])) {
					echo htmlspecialcharsbx($arValues['DESCRIPTION']);
				} else {
					echo '<font color="red">'.GetMessage('MANGO_SEO_NO_SET').'</font>';
				}
			}),
			'KEYWORDS' => array('NAME' => GetMessage('MANGO_SEO_KEYWORDS'), 'REQUIRED' => false, 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				if (strlen($arValues['KEYWORDS'])) {
					echo htmlspecialcharsbx($arValues['KEYWORDS']);
				} else {
					echo '<font color="red">'.GetMessage('MANGO_SEO_NO_SET').'</font>';
				}
			}),
			'G_CNT' => array('NAME' => GetMessage('MANGO_SEO_POSESENIY_GOOGLE'), 'REQUIRED' => false, 'TYPE' => 'view'),
			'Y_CNT' => array('NAME' => GetMessage('MANGO_SEO_POSESENIY_YANDEX'), 'REQUIRED' => false, 'TYPE' => 'view'),
			'OG_TAGS_DETAIL' => array('NAME' => GetMessage('MANGO_SEO_OG_TAGS'), 'REQUIRED' => false, 'HINT' => GetMessage('MANGO_SEO_OG_TAGS_HINT'), 'TYPE' => 'modif', 'MODIF_FUNC' => function($arValues){
				$arOGtasgs = explode(',', $arValues['OG_TAGS_DETAIL']);
				?>
				<b>title</b> &mdash; <?=GetMessage('MANGO_SEO_OG_TITLE')?> (<?if (!in_array('title', $arOGtasgs)){?><font color="red"><?=GetMessage('MANGO_SEO_NO_SET')?></font><?} else {?><font color="green"><?=GetMessage('MANGO_SEO_ISSET')?></font><?}?>)<br/>
				<b>image</b> &mdash; <?=GetMessage('MANGO_SEO_OG_IMG')?> (<?if (!in_array('image', $arOGtasgs)){?><font color="red"><?=GetMessage('MANGO_SEO_NO_SET')?></font><?} else {?><font color="green"><?=GetMessage('MANGO_SEO_ISSET')?></font><?}?>)<br/>
				<b>url</b> &mdash; <?=GetMessage('MANGO_SEO_OG_URL')?> (<?if (!in_array('url', $arOGtasgs)){?><font color="red"><?=GetMessage('MANGO_SEO_NO_SET')?></font><?} else {?><font color="green"><?=GetMessage('MANGO_SEO_ISSET')?></font><?}?>)<br/>
				<b>description</b> &mdash; <?=GetMessage('MANGO_SEO_OG_DESC')?> (<?if (!in_array('description', $arOGtasgs)){?><font color="red"><?=GetMessage('MANGO_SEO_NO_SET')?></font><?} else {?><font color="green"><?=GetMessage('MANGO_SEO_ISSET')?></font><?}?>)<br/>
				<?
			}),
			'OBJ_TITLE' => array('NAME' => GetMessage('MANGO_SEO_OBJ_TITLE'), 'ERROR' => GetMessage('MANGO_SEO_ERROR_OBJ_TITLE'), 'REQUIRED' => false, 'TYPE' => 'view', 'HINT' => GetMessage('MANGO_SEO_OBJ_TITLE_HINT')),
			'NEW_TITLE' => array('NAME' => GetMessage('MANGO_SEO_RENAME_TITLE'), 'REQUIRED' => false, 'TYPE' => 'string'),
			'NEW_DESCRIPTION' => array('NAME' => GetMessage('MANGO_SEO_RENAME_DESC'), 'REQUIRED' => false, 'TYPE' => 'string'),
			'NEW_KEYWORDS' => array('NAME' => GetMessage('MANGO_SEO_RENAME_KEYWORDS'), 'REQUIRED' => false, 'TYPE' => 'string'),
			'REDIRECT' => array('NAME' => GetMessage('MANGO_SEO_REDIRECT'), 'REQUIRED' => false, 'TYPE' => 'string'),
			'REDIRECT_STATUS' => array('NAME' => GetMessage('MANGO_SEO_REDIRECT_STATUS'), 'REQUIRED' => false, 'TYPE' => 'select', 'VARIANTS' => array(
				'301' => GetMessage('MANGO_SEO_STATUS_301'),
				'302' => GetMessage('MANGO_SEO_STATUS_302'),
			)),
		),
	),
	'referers' => array(
		'NAME' => GetMessage('MANGO_SEO_VNESNIE_SSYLKI'),
		'TITLE' => GetMessage('MANGO_SEO_VNESNIE_SSYLKI_NA_DA'),
		'HTML' => $referers
	)
);

$form->SetFields($arFields);
$form->CheckAction('', '\OpenGift\BitrixRegionManager\CityTable::Update');
$form->CheckDelete('\OpenGift\BitrixRegionManager\CityTable::Delete');

$form->Output(false, array('btn_new' => false));