<?php
$module = 'opengift.region';
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
\Bitrix\Main\Loader::includeModule($module);
IncludeModuleLangFile(__FILE__);
use OpenGift\Bitrix\Admin\AdminForm;

if ($_SERVER['REQUEST_METHOD']=='POST' && check_bitrix_sessid() && $_POST['action']=='update') {

    $APPLICATION->RestartBuffer();
    header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

    $bDone = true;
    $percent = 100;
    $f = fopen(realpath(dirname(__FILE__) . '/../') . '/db/cities.csv', 'r');
    $arData = [];
    while ($arRow = fgets($f)) {
        $row = explode(',', $arRow);
        $arData[] = [
            'name' => $row[1],
            'region' => $row[2],
            'district' => $row[3],
            'sort' => 100,
            'lat' => $row[4],
            'lon' => $row[5]
        ];
    }
    fclose($f);

    foreach ($arData as $data) {
        \OpenGift\BitrixRegionManager\CityTable::add(
            $data
        );
    }

    die(CUtil::PhpToJsObject(array(
        'last' => $lastID,
        'percent' => round($percent),
        'all_done' => $bDone ? 'Y' : 'N'
    )));
}

$form = new AdminForm($module);
$form->SetRights();
$form->SetTitle('Update regions', true);
CJSCore::Init(['jquery']);
ob_start();
?>
    <table>
        <tr>
            <td>Interval</td>
            <td><input type="text" id="scan-limit" value="<?= round(COption::GetOptionString($module, 'scan_limit', 30));?>" size="4" /></td>
        </tr>
    </table>
    <br/>
    <div id="resultcode_container"></div>
    <div id="start_container">
        <div id="first_start" class="adm-security-text-block">
            <?if (($time = COption::GetOptionString($module, 'last_scan_time')) > 0):?>
                <?=GetMessage('MANGO_SEO_LAST_SCAN')?><?= FormatDate('d.m.Y H:i:s', COption::GetOptionString($module, 'last_scan_time'))?>
            <?else:?>
                <?=GetMessage('MANGO_SEO_NO_FULL_SCAN')?><?endif;?>
        </div>
        <span id="start_button" class="adm-btn adm-btn-green" onclick="startUpdate();">Update</span>
    </div>
    <div id="status_bar" style="display:none;">
        <div id="progress_bar" style="width: 500px;" class="adm-progress-bar-outer">
            <div id="progress_bar_inner" style="width: 0px;" class="adm-progress-bar-inner"></div>
            <div id="progress_text" style="width: 500px;" class="adm-progress-bar-inner-text">0%</div>
        </div>
        <div id="current_test"></div>
        <span id="stop_button" class="adm-btn stop-button" onclick="JCSeoScaner.startStop()" style="margin-top: 10px;"><?=GetMessage('MANGO_SEO_STOP_SCAN')?></span>
    </div>
<script>
    function startUpdate() {
        $.post(
            location.href,
            {
                'action': 'update',
                'sessid': BX.bitrix_sessid()
            },
            function () {
                alert('Done');
            }
        )
    }
</script>
<?
$html = ob_get_contents();
ob_end_clean();
$arFields = array(
    'common' => array(
        'NAME' => "Update region list",
        'TITLE' => "",
        'FIELDS' => array(),
        'HTML' => $html,
    )
);
$form->SetFields($arFields);

$form->Output(true);