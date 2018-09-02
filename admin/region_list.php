<? require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');

global $APPLICATION, $USER;


$moduleId = 'opengift.region';

if (!$USER->IsAdmin())
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));


use OpenGift\BitrixRegionManager\CityTable;

\Bitrix\Main\Loader::includeModule($moduleId);


$sTableID = CityTable::getTableName();
$oSort = new \CAdminSorting($sTableID, 'id', 'asc');
$lAdmin = new \CAdminList($sTableID, $oSort);
$arFields = CityTable::getFields();

$arHeaders = [];
foreach ($arFields as $field => $type){
    $arHeader = [
        'id' => $field,
        'content' => $field,
        'default' => true,
    ];

    if($type != 'reference')
        $arHeader['sort'] = $field;

    $arHeaders[] = $arHeader;
}

$lAdmin->AddHeaders($arHeaders);



$FilterArr = [];
foreach ($arFields as $fieldName => $type)
    $FilterArr[] = 'find_'.$fieldName;


$lAdmin->InitFilter($FilterArr);
$arFilter = [];
foreach ($arFields as $fieldName => $type) {

    if($_REQUEST['del_filter'] == 'Y')
        continue;

    if (isset($find_type) && ($find_type == $fieldName) && isset($find) && strlen($find)){

        $arFilter["=%" . $fieldName] = $find;

    }else{
        switch ($type){
            case 'datetime':

                $period = $GLOBALS['find_'.$fieldName.'_from_FILTER_PERIOD'];
                $direction = $GLOBALS['find_'.$fieldName.'_from_FILTER_DIRECTION'];
                $dateFrom = $GLOBALS['find_'.$fieldName.'_from'];
                $dateTo = $GLOBALS['find_'.$fieldName.'_to'];

                if(!$dateFrom && !$dateTo)
                    break;

                if($dateFrom)
                    $arFilter['>='.$fieldName] =  trim($dateFrom);

                if($dateTo)
                    $arFilter['<='.$fieldName] = trim($dateTo);

                break;

            case 'integer':

                $from = $GLOBALS['find_'.$fieldName.'_from'];
                $to = $GLOBALS['find_'.$fieldName.'_to'];

                if(!$from && !$to)
                    break;

                if($from)
                    $arFilter['>='.$fieldName] =  trim($from);

                if($to)
                    $arFilter['<='.$fieldName] = trim($to);

                break;

            default:
                if (isset($GLOBALS['find_'.$fieldName]) && strlen($GLOBALS['find_'.$fieldName]))
                    $arFilter["?".$fieldName] = $GLOBALS["find_".$fieldName];
        }
    }


}


$arActions = [
    'delete' => GetMessage('MAIN_ADMIN_LIST_DELETE'),
];

$lAdmin->AddGroupActionTable($arActions);

if(($arID = $lAdmin->GroupAction()))
{

    if($_REQUEST['action_target'] == 'selected')
    {
        $arID = [];
        $rsData = CityTable::getList(['filter' => $arFilter]);
        while($arRes = $rsData->fetch())
        {
            $arID[] = $arRes['id'];
        }
    }


    switch($_REQUEST['action']) {
        case 'delete':

            foreach ($arID as $id) {
                CityTable::delete($id);
            }

            break;
    }

}


$arResult = [];
$by = $_REQUEST['by']?:'id';
$order = $_REQUEST['order']?:'asc';
$select = [];

foreach ($arFields as $fieldName => $type){

    if($type == 'reference')
        $select[$fieldName.'Name'] = $fieldName.'.name';
    else
        $select[] = $fieldName;

}
$params = [
    'order' => [$by => $order],
    'filter' => $arFilter,
    'select' => $select,
];
$arEnumFieldValues = CityTable::getEnumFieldValues();
$arEnumValToKey = [];
foreach ($arEnumFieldValues as $fieldName => $arFieldValues){
    foreach ($arFieldValues as $key => $val){
        $arEnumValToKey[$fieldName][$val] = $key;
    }
}

$resData = CityTable::getList($params);
$resAdminData = new \CAdminResult($resData, $sTableID);
$resAdminData->NavStart();
$lAdmin->NavText($resAdminData->GetNavPrint('Записи'));
while ($arRowData = $resAdminData->Fetch()){
    foreach ($arFields as $fieldName => $type){
        if($type == 'reference'){
            $arRowData[$fieldName] = $arRowData[$fieldName.'Name'];
            unset($arRowData[$fieldName.'Name']);
        }

        if($type == 'enum'){
            $arRowData[$fieldName] = $arEnumValToKey[$fieldName][$arRowData[$fieldName]];
        }
    }

    $row = $lAdmin->AddRow($arRowData['id'], $arRowData);
}



$lAdmin->CheckListMode();
$APPLICATION->SetTitle('Лиды');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');


$arFilter = [];
foreach ($arHeaders as $arHeader)
    $arFilter[$arHeader['id']] = $arHeader['content'];

$oFilter = new CAdminFilter($sTableID."_filter", $arFilter);
?>
    <form name="find_form" method="get" action="<? echo $APPLICATION->GetCurPage(); ?>">
        <input type="hidden" value="<? echo htmlspecialcharsbx($sTableID) ?>" name="table_name">
        <? $oFilter->Begin(); ?>
        <tr>
            <td><b>Найти:</b></td>
            <td>
                <input type="text" size="25" name="find" value="<? echo htmlspecialcharsbx($find) ?>"
                       title="<?=GetMessage("PERFMON_TABLE_FIND")?>">
                <?
                $arr = array(
                    "reference" => array_keys($arFilter),
                    "reference_id" => array_keys($arFilter),
                );
                echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
                ?>
            </td>
        </tr>
        <? foreach ($arFields as $fieldName => $fieldType){

            switch ($fieldType){


                case 'datetime':
                    ?>

                    <tr>
                        <td><? echo htmlspecialcharsbx($fieldName) ?></td>
                        <td><?= \CAdminCalendar::CalendarPeriod('find_'.$fieldName.'_from', 'find_'.$fieldName.'_to', ${'find_'.$fieldName.'_from'}, ${'find_'.$fieldName.'_to'}, true, 10, true) ?></td>
                    </tr>
                    <?
                    break;

                case 'integer':
                    ?>
                    <tr>
                        <td><? echo htmlspecialcharsbx($fieldName) ?>:</td>
                        <td>

                            <input type="text" name="<?='find_'.$fieldName.'_from'?>" value="<?=(intval(${'find_'.$fieldName.'_from'})>0)?intval(${'find_'.$fieldName.'_from'}):''?>" placeholder="от">


                            <input type="text" name="<?='find_'.$fieldName.'_to'?>" value="<?=(intval(${'find_'.$fieldName.'_to'})>0)?intval(${'find_'.$fieldName.'_to'}):''?>" placeholder="до">
                        </td>
                    </tr>
                    <?
                    break;

                case 'enum':
                    ?>
                    <tr>
                        <td><? echo htmlspecialcharsbx($fieldName) ?></td>
                        <td>
                            <select name="find_<?= $fieldName ?>">
                                <option value="">--</option>
                                <? foreach ($arEnumFieldValues[$fieldName] as $key => $value){ ?>
                                    <option value="<?=$value?>"<?=($value == ${'find_'.$fieldName})? ' selected':''?>><?=is_int($key)? $value : $key?></option>
                                <? } ?>
                            </select>
                    </tr>
                    <?
                    break;

                default:
                    ?>
                    <tr>
                        <td><? echo htmlspecialcharsbx($fieldName) ?></td>
                        <td><input type="text" name="find_<?= $fieldName ?>" size="47"
                                   value="<?= ${'find_'.$fieldName} ?>">&nbsp;<?=ShowFilterLogicHelp()?></td>
                    </tr>
                <? }
        }

        $oFilter->Buttons([
            'table_id' => $sTableID,
            'url' => $APPLICATION->GetCurPage(),
            'form' => 'find_form',
        ]);
        $oFilter->End();
        ?>
    </form>


<?
$lAdmin->DisplayList();


require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');