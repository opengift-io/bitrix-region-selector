<?php
namespace OpenGift\Bitrix\Admin;

class AdminList
{

    public $generalKey = 'ID';
    private $module = '';
    private $file = '';
    private $file_edit = '';
    private $file_edit_params = array();
    private $tableID = '';
    private $oFilter = null;
    private $arFilter = array();
    private $title = '';
    private $lAdmin = null;
    private $oSort = null;
    private $arHedaers = array();
    private $arVisibleHedaers = array();
    private $arEditable = array();
    private $rsRec = null;
    private $d7class = false;

    public $topNote = '';
    public $bottomNote = '';

    public function __construct($module, $by = 'ID')
    {
        \CUtil::JSPostUnescape();
        if (!defined('ADMIN_MODULE_NAME')) {
            define('ADMIN_MODULE_NAME', $module);
        }
        $debug_backtrace = debug_backtrace();

        $this->module = str_replace('.', '_', $module);
        $this->file = basename($debug_backtrace[0]['file']);
        $this->file_edit = str_replace('list.php', 'edit.php', $this->file);
        $this->tableID = 'tbl_mango_' . substr($this->file, 0, -4);

        $this->oSort = new \CAdminSorting($this->tableID, $by, 'DESC');
        $this->lAdmin = new \CAdminList($this->tableID, $this->oSort);
    }

    public function setD7class($class)
    {
        $this->d7class = $class;
        $this->setTitle(call_user_func(array($class, 'getTableTitle')));
    }

    private function application()
    {
        return $GLOBALS['APPLICATION'];
    }

    private function user()
    {
        return $GLOBALS['USER'];
    }

    private function fieldShowed($field)
    {
        return in_array($field, $this->arVisibleHedaers);
    }

    public function getlAdmin()
    {
        return $this->lAdmin;
    }

    public function getTableID()
    {
        return $this->tableID;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        $this->Application()->SetTitle($this->title);
    }

    public function setRights()
    {
        if ($this->Application()->GetGroupRight(ADMIN_MODULE_NAME) < 'R') {
            $this->Application()->AuthForm(GetMessage('ACCESS_DENIED'));
            require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
            die();
        }
    }

    public function setDetailPage($url, $arGet = array())
    {
        $this->file_edit = $url;
        $this->file_edit_params = $arGet;
    }

    public function setGroupAction($arActions)
    {
        if (($arID = $this->lAdmin->GroupAction()) && check_bitrix_sessid()) {
            $request = $_REQUEST;
            if (isset($arActions[$request['action']])) {
                foreach ($arID as $ID) {
                    call_user_func($arActions[$request['action']], $ID);
                }
            }
        }
        if ($this->lAdmin->EditAction() && isset($arActions['edit']) && check_bitrix_sessid()) {
            foreach ($GLOBALS['FIELDS'] as $ID => $arFields) {
                call_user_func($arActions['edit'], $ID, $arFields);
            }
        }
    }

    public function setContextMenu($arAddContext = array())
    {
        $arContext = array();
        if ($arAddContext !== false) {
            $arContext['add'] = array(
                'TEXT' => GetMessage('MAIN_ADD'),
                'ICON' => 'btn_new',
                'LINK' => $this->module . '_' . $this->file_edit . '?lang=' . LANG,
            );
            if (!empty($arAddContext)) {
                $arContext = array_merge($arContext, $arAddContext);
            }
            foreach ($arContext as $k => $v) {
                if (empty($v)) {
                    unset($arContext[$k]);
                }
            }
        }
        $this->lAdmin->AddAdminContextMenu($arContext);
    }

    public function getMapHeaders($fieldsMap = array())
    {
        $headers = array();
        foreach ($fieldsMap as $key => $item) {
            if (array_key_exists('admin_editable', $item) && $item['admin_editable'] === true) {
                $this->arEditable[] = $key;
            }
            if (array_key_exists('admin_page', $item) && $item['admin_page'] === true) {
                $headers[$key] = array(
                    'id' => $key,
                    'sort' => $key,
                    'content' => array_key_exists('title', $item) ? $item['title'] : $key,
                    'default' => array_key_exists('admin_page_default', $item) && $item['admin_page'] === true,
                );
            }
        }
        return $headers;
    }

    public function setHeaders($arHeaders = array())
    {
        if ($this->d7class !== false) {
            $arHeaders = $this->getMapHeaders(call_user_func(array($this->d7class, 'getMap')));
        }
        if (!empty($arHeaders) && is_array($arHeaders)) {
            foreach ($arHeaders as $code => $header) {
                if (is_array($header)) {
                    $header['id'] = $code;
                    $this->arHedaers[$code] = $header;
                } else {
                    $this->arHedaers[$code] = array(
                        'id' => $code,
                        'content' => $header,
                        'sort' => $code,
                        'default' => true,
                    );
                }
            }
            $this->lAdmin->AddHeaders(array_values($this->arHedaers));
            $this->arVisibleHedaers = $this->lAdmin->GetVisibleHeaderColumns();
        }
    }

    private function getOrderBy()
    {
        $by = $GLOBALS['by'];
        if (array_key_exists($by, $this->arHedaers)) {
            return is_array($this->arHedaers[$by]) ? $this->arHedaers[$by]['sort'] : $this->arHedaers[$by];
        } else {
            return $this->generalKey;
        }
    }

    private function getOrderOrd()
    {
        $order = strtolower($GLOBALS['order']);
        return $order == 'desc' || $order == 'asc' ? $order : 'desc';
    }

    public function setList($rsRec = null, $arVisual = array(), $arAddActions = array(), $arEditable = array())
    {
        if ($rsRec === null && $this->d7class !== false) {
            $rsRec = call_user_func(array($this->d7class, 'getList'), array('order' => array($this->getOrderBy() => $this->getOrderOrd())));
            $arEditable = $this->arEditable;
        }
        $this->rsRec = new \CAdminResult($rsRec, $this->tableID);
        $this->rsRec->NavStart();
        $this->lAdmin->NavText($this->rsRec->GetNavPrint($this->title));
        while ($arRes = $this->rsRec->Fetch()) {
            $f_ID = $arRes[$this->generalKey];
            if ($this->file_edit !== false) {
                if (!empty($this->file_edit_params)) {
                    $editFile = $this->module . '_' . $this->file_edit . '?';
                    foreach ($this->file_edit_params as $get) {
                        $editFile .= $get . '=' . $arRes[$get] . '&amp;';
                    }
                    $editFile .= 'lang=' . LANG;
                } else {
                    $editFile = $this->module . '_' . $this->file_edit . '?' . $this->generalKey . '=' . $f_ID . '&amp;lang=' . LANG;
                }
            } else {
                $editFile = false;
            }

            $row =& $this->lAdmin->AddRow($f_ID, $arRes, $editFile);

            if ($this->FieldShowed('ID')) {
                $row->AddViewField('ID', '<a href="' . $editFile . '" title="' . GetMessage('MAIN_EDIT') . '">' . $f_ID . '</a>');
            }
            if ($this->FieldShowed('NAME')) {
                $row->AddViewField('NAME', '<a href="' . $editFile . '" title="' . GetMessage('MAIN_EDIT') . '">' . htmlspecialcharsbx($arRes['NAME']) . '</a>');
            }
            if ($this->FieldShowed('ACTIVE')) {
                $row->AddViewField('ACTIVE', GetMessage('MAIN_' . ($arRes['ACTIVE'] == 'Y' ? 'YES' : 'NO')));
            }

            if (!empty($arEditable)) {
                foreach ($arEditable as $editCode) {
                    if (is_array($editCode)) {
                        if ($editCode['TYPE'] == 'textarea') {
                            //$row->AddTextField($editCode['CODE']);
                        } else {
                            $row->AddInputField($editCode['CODE'], array('size' => 30));
                        }
                    } else {
                        $row->AddInputField($editCode, array('size' => 30));
                    }
                }
            }

            if (!empty($arVisual)) {
                foreach ($arVisual as $code => $action) {
                    if ($this->FieldShowed($code)) {
                        $row->AddViewField($code, call_user_func($action, $arRes[$code], $arRes));
                    }
                }
            }

            if ($arAddActions !== false) {
                $arActions = array();
                $arActions['edit'] = array(
                    'ICON' => 'edit',
                    'DEFAULT' => true,
                    'TEXT' => GetMessage('MAIN_ADMIN_MENU_EDIT'),
                    'ACTION' => $this->lAdmin->ActionRedirect($editFile)
                );
                $arActions['delete'] = array(
                    'ICON' => 'delete',
                    'TEXT' => GetMessage('MAIN_ADMIN_MENU_DELETE'),
                    'ACTION' => "if(confirm('" . GetMessage('MAIN_ADMIN_MENU_DELETE') . "?')) " . $this->lAdmin->ActionDoGroup($f_ID, 'delete')
                );
                if (!empty($arAddActions)) {
                    foreach ($arAddActions as $k => $arAction) {
                        if ($arAction === false && isset($arActions[$k])) {
                            unset($arActions[$k]);
                            continue;
                        }
                        if (isset($arAction['LINK'])) {
                            $arAction['LINK'] = str_replace(array('#EDIT_URL#', '#ID#'), array(htmlspecialcharsback($editFile), $f_ID), $arAction['LINK']);
                        } elseif ($arAction['ACTION'] == 'group') {
                            if (isset($arAction['ACTION_ALERT'])) {
                                $arAction['ACTION'] = "if(confirm('" . $arAction['ACTION_ALERT'] . "')) " . $this->lAdmin->ActionDoGroup($f_ID, $arAction['ACTION_VAR']);
                            } else {
                                $arAction['ACTION'] = $this->lAdmin->ActionDoGroup($f_ID, $arAction['ACTION_VAR']);
                            }
                        } elseif (isset($arAction['ACTION'])) {
                            $arAction['ACTION'] = str_replace('#ID#', $arRes['ID'], $arAction['ACTION']);
                        }
                        $arActions[$k] = $arAction;
                    }
                    //$arActions = array_merge($arActions, $arAddActions);
                }
                $row->AddActions($arActions);
            }
        }
    }

    public function setFooter($arActions = array('delete' => ''))
    {
        $this->lAdmin->AddFooter(
            array(
                array('title' => GetMessage('MAIN_ADMIN_LIST_SELECTED'), 'value' => $this->rsRec->SelectedRowsCount()),
                array('counter' => true, 'title' => GetMessage('MAIN_ADMIN_LIST_CHECKED'), 'value' => '0'),
            )
        );
        $this->lAdmin->AddGroupActionTable($arActions, array('disable_action_target' => true));
    }

    public function makeFilter()
    {
        $arFilter = array();
        foreach ($this->arFilter as $k => $arItem) {
            if ($arItem['TYPE'] == 'calendar') {
                if (strlen($arItem['VALUE1'])) {
                    $arFilter[strtoupper($k) . '1'] = $arItem['VALUE1'];
                }
                if (strlen($arItem['VALUE2'])) {
                    $arFilter[strtoupper($k) . '2'] = $arItem['VALUE2'];
                }
            } else {
                if (strlen($arItem['VALUE'])) {
                    $arFilter[(isset($arItem['OPER']) ? $arItem['OPER'] : '') . strtoupper($k)] = $arItem['VALUE'];
                }
            }
        }
        return $arFilter;
    }

    public function setFilter($arFilter)
    {
        $this->arFilter = $arFilter;
        $arTitles = array();
        $arInit = array();
        foreach ($arFilter as $k => $arItem) {
            $arTitles[$k] = $arItem['TITLE'];
            if ($arItem['TYPE'] == 'calendar') {
                $arInit[] = 'find_' . $k . '1';
                $arInit[] = 'find_' . $k . '2';
            } else {
                $arInit[] = 'find_' . $k;
            }
        }
        $this->lAdmin->InitFilter($arInit);
        $this->oFilter = new \CAdminFilter($this->tableID . '_filter', $arTitles);

        $arSessionVars = $_SESSION['SESS_ADMIN'][$this->tableID];
        foreach ($this->arFilter as $k => &$arItem) {
            if ($arItem['TYPE'] == 'calendar') {
                $value1 = isset($_REQUEST['find_' . $k . '1']) && !isset($_REQUEST['del_filter']) ? $_REQUEST['find_' . $k . '1'] :
                    (isset($arSessionVars['find_' . $k . '1']) ? $arSessionVars['find_' . $k . '1'] : '');
                $arItem['VALUE1'] = $value1;
                $value2 = isset($_REQUEST['find_' . $k . '2']) && !isset($_REQUEST['del_filter']) ? $_REQUEST['find_' . $k . '2'] :
                    (isset($arSessionVars['find_' . $k . '2']) ? $arSessionVars['find_' . $k . '2'] : '');
                $arItem['VALUE2'] = $value2;
            } else {
                $value = isset($_REQUEST['find_' . $k]) && !isset($_REQUEST['del_filter']) ? $_REQUEST['find_' . $k] :
                    (isset($arSessionVars['find_' . $k]) ? $arSessionVars['find_' . $k] : '');
                $arItem['VALUE'] = $value;
            }
        }
    }

    private function outputFilter()
    {
        $url = $this->Application()->GetCurPage();
        ?>
        <form name="find_form" method="get" action="<?= $url; ?>"><?
        $this->oFilter->Begin();
        foreach ($this->arFilter as $k => $arItem) {
            if (isset($arItem['TYPE'])) {
                $type = $arItem['TYPE'];
            } else {
                $type = 'text';
            }
            if ($type == 'select' && !isset($arItem['VARIANTS']) && is_array($arItem['VARIANTS'])) {
                $type = 'text';
            }
            ?>
            <tr>
                <td><?= $arItem['TITLE'] ?>:</td>
                <td>
                    <? if ($type == 'select'): ?>
                        <select name="find_<?= $k ?>">
                            <option value="">(not set)</option>
                            <? foreach ($arItem['VARIANTS'] as $kv => $vv): ?>
                                <option value="<?= $kv ?>"<? if ($arItem['VALUE'] == $kv) { ?> selected="selected"<?
                                } ?>><?= $vv ?></option>
                            <? endforeach; ?>
                        </select>
                        <?
                    elseif ($type == 'calendar'): ?>
                        <?= CalendarPeriod('find_' . $k . '1', $arItem['VALUE1'], 'find_' . $k . '2', $arItem['VALUE2'], 'find_form', 'Y'); ?>
                        <?
                    else: ?>
                        <input type="text" name="find_<?= $k ?>"
                               value="<? echo htmlspecialcharsbx($arItem['VALUE']) ?>"/>
                    <? endif; ?>
                </td>
            </tr>
            <?
        }
        $this->oFilter->Buttons(array('table_id' => $this->tableID, 'url' => $url, 'form' => 'find_form'));
        $this->oFilter->End();
        ?></form><?
    }

    public function output()
    {
        global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;
        $this->lAdmin->CheckListMode();
        require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
        if ($this->oFilter !== null) {
            $this->OutputFilter();
        }
        echo $this->topNote;
        $this->lAdmin->DisplayList();
        if (strlen($this->bottomNote)) {
            echo BeginNote();
            echo $this->bottomNote;
            echo EndNote();
        }
        require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
    }
}