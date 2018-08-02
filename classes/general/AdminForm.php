<?php
/**
 * Created in Heliard.
 * User: gvammer gvammer@rambler.ru
 * Date: 02.08.2018
 * Time: 3:17
 */

namespace OpenGift\Bitrix\Admin;

class AdminForm {

    private $module = '';
    private $file = '';
    private $file_list = '';
    private $formName = '';
    private $title = '';
    private $id = 0;
    private $arTabs = array();
    private $arValues = array();
    private $arHiddenValues = array();
    private $tabControl = null;
    private $message = null;
    private $bVarsFromForm = false;
    private $post = '';
    private $js = '';
    private $generlKey = 'ID';
    private $d7class = false;
    private $defaultFields = array();

    public function __construct($module, $generlKey='ID') {
        if (!defined('ADMIN_MODULE_NAME')) {
            define('ADMIN_MODULE_NAME', $module);
        }
        $debug_backtrace = debug_backtrace();
        $this->id = isset($_REQUEST[$generlKey]) ? htmlspecialcharsbx(trim($_REQUEST[$generlKey])) : false;
        $this->generlKey = $generlKey;
        $this->module = str_replace('.', '_', $module);
        $this->file = basename($debug_backtrace[0]['file']);
        $this->file_list = str_replace('edit.php', 'list.php', $this->file);
        $this->formName = 'form_mango_'.substr($this->file, 0, -4);
        $this->post = isset($_POST) ? $_POST : array();
        if (!isset($this->post['FIELDS'])) {
            $this->post['FIELDS'] = array();
        }
    }

    public function defaultField($k, $v) {
        $this->defaultFields[$k] = $v;
    }

    public function setD7class($class) {
        $this->d7class = $class;
        $this->setTitle(call_user_func(array($class, 'getTableTitle')));
    }

    private function application() {
        return $GLOBALS['APPLICATION'];
    }

    private function user() {
        return $GLOBALS['USER'];
    }

    private function DB() {
        return $GLOBALS['DB'];
    }

    public function setJS($code) {
        $this->js = $code;
    }

    public function setTitle($title, $bAsis=false) {
        $this->title = $title;
        if ($bAsis === false) {
            if ($this->id !== false) {
                $this->title = GetMessage('MAIN_EDIT').' "'.$this->title.'"';
            } else {
                $this->title = GetMessage('MAIN_ADD').' "'.$this->title.'"';
            }
        }
        $this->Application()->SetTitle($this->title);
    }

    public function setRights() {
        if ($this->Application()->GetGroupRight(ADMIN_MODULE_NAME) < 'R') {
            $this->Application()->AuthForm(GetMessage('ACCESS_DENIED'));
            require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
            die();
        }
    }

    public function setVar($k, $v) {
        $this->post['FIELDS'][$k] = $v;
        $this->arValues[$k] = $v;
    }

    public function setHiddenVar($k, $v) {
        $this->arHiddenValues[$k] = $v;
    }

    public function getFromDB($funcById=null) {
        if ($this->d7class !== false) {
            $funcById = array($this->d7class, 'getById');
        }
        if ($this->id !== false) {
            if (!($this->arValues = call_user_func($funcById, $this->id)->Fetch())) {
                $this->id = false;
            } else {
                return $this->arValues;
            }
        }
        return false;
    }

    public function setFields($arFields) {
        $aTabs = array();
        if (!empty($arFields) && is_array($arFields)) {
            foreach ($arFields as $tabCode => $arTabFields) {
                $aTabs[] = array(
                    'DIV' => $tabCode,
                    'TAB' => $arTabFields['NAME'],
                    'TITLE' => isset($arTabFields['TITLE']) ? $arTabFields['TITLE'] : '',
                    'MESSAGE' => isset($arTabFields['MESSAGE']) ? $arTabFields['MESSAGE'] : '',
                    'HTML' => isset($arTabFields['HTML']) ? $arTabFields['HTML'] : '',
                    'FUNC' => isset($arTabFields['FUNC']) ? $arTabFields['FUNC'] : '',
                    'FIELDS' => isset($arTabFields['FIELDS']) ? $arTabFields['FIELDS'] : array()
                );
            }
        }
        $this->arTabs = $aTabs;
        $this->tabControl = new \CAdminTabControl('tabControl', $aTabs);
    }

    public function checkAction($funcAdd=null, $funcUpd=null) {
        if ($this->d7class !== false) {
            $funcAdd = array($this->d7class, 'add');
            $funcUpd = array($this->d7class, 'update');
            $arMap = call_user_func(array($this->d7class, 'getMap'));
        }
        if ((strlen($this->post['save']) || strlen($this->post['apply'])) && check_bitrix_sessid()) {
            $error = '';
            foreach ($this->arTabs as $arTabs) {
                if ($this->d7class !== false) {
                    $arTabs['FIELDS'] = array_flip($arTabs['FIELDS']);
                }
                foreach ($arTabs['FIELDS'] as $name => $arField) {
                    if ($this->d7class !== false) {
                        $arField = array(
                            'TITLE' => $arMap[$name]['title'],
                            'DATA_TYPE' => $arMap[$name]['data_type'],
                            'TYPE' => array_key_exists('admin_type', $arMap[$name]) ? $arMap[$name]['admin_type'] : 'string',
                            'EXTRA' => array_key_exists('admin_extra', $arMap[$name]) ? $arMap[$name]['admin_extra'] : false,
                            'REQUIRED' => array_key_exists('required', $arMap[$name]) ? $arMap[$name]['required'] : false,
                        );
                        //@fixme (move to events)
                        if ($arField['DATA_TYPE'] == 'datetime' && $arField['EXTRA'] == 'current') {
                            $this->post['FIELDS'][$name] = new \Bitrix\Main\Type\DateTime();
                            continue;
                        }
                    }
                    if (isset($arField['TYPE']) && $arField['TYPE']=='view') {
                        unset($this->post['FIELDS'][$name]);
                        continue;
                    }
                    if (isset($arField['REQUIRED']) && $arField['REQUIRED']!==false) {
                        if (!isset($this->post['FIELDS'][$name]) || strlen(trim($this->post['FIELDS'][$name]))<=0) {
                            if ($this->d7class !== false) {
                                $error .= 'Error: '.$arField['TITLE']."\n";
                            } else {
                                $error .= $arField['ERROR']."\n";
                            }
                        }
                    }
                }
            }
            if (strlen($error) > 0) {
                $this->message = new \CAdminMessage(array('TYPE' => 'ERROR', 'MESSAGE' => $error));
                $this->bVarsFromForm = true;
            } else {
                if ($this->id !== false) {
                    call_user_func($funcUpd, $this->id, $this->post['FIELDS']);
                } elseif (is_array($funcAdd) || strlen($funcAdd) > 0) {
                    if ($this->d7class !== false) {
                        $result = call_user_func($funcAdd, $this->post['FIELDS']);
                        $this->id = $result->getId();
                    } else {
                        $this->id = call_user_func($funcAdd, $this->post['FIELDS']);
                    }
                }
                if (strlen($this->post['save'])) {
                    LocalRedirect($this->module.'_'.$this->file_list.'?lang='.LANG);
                } else {
                    LocalRedirect($this->module.'_'.$this->file.'?'.$this->generlKey.'='.$this->id.'&lang='.LANG);
                }
            }
        }
    }

    public function checkDelete($funcDel=null) {
        if ($this->d7class !== false) {
            $funcDel = array($this->d7class, 'delete');
        }
        if ($this->id!==false && $_REQUEST['action']=='delete' && check_bitrix_sessid()) {
            call_user_func($funcDel, $this->id);
            LocalRedirect($this->module.'_'.$this->file_list.'?lang='.LANG);
        }
    }

    private function outputField($name, $type, $arField=array()) {
        if ($this->bVarsFromForm && isset($this->post['FIELDS']) && isset($this->post['FIELDS'][$name])) {
            $this->arValues[$name] = $this->post['FIELDS'][$name];
        } elseif (isset($this->defaultFields[$name])) {
            $this->arValues[$name] = $this->defaultFields[$name];
        }
        $value = isset($this->arValues[$name]) ? $this->arValues[$name] : '';
        if ($type == 'siteselect') {
            $type = 'select';
            $arField['VARIANTS'] = array();
            $rsSite = \CSite::GetList($by='sort', $order='asc');
            while ($arSite = $rsSite->GetNext()) {
                $arField['VARIANTS'][$arSite['ID']] = '['.$arSite['ID'].'] '.$arSite['NAME'];
            }
        }
        switch ($type) {
            case 'modif':
                call_user_func($arField['MODIF_FUNC'], $this->arValues);
                break;
            case 'select':
                $arVariants = isset($arField['VARIANTS']) ? $arField['VARIANTS'] : array();
                ?>
                <select name="FIELDS[<?= $name?>]"<?= isset($arField['ADD_STR']) ? ' '.$arField['ADD_STR'] : ''?>>
                    <?foreach ($arVariants as $k => $v):?>
                        <option value="<?= $k?>"<?if ($value == $k){?> selected="selected"<?}?>><?= $v?></option>
                    <?endforeach;?>
                </select>
                <?
                break;
            case 'checkbox':
                if (!isset($this->arValues[$name])) {
                    $value = 'Y';
                }
                ?><input type="hidden" name="FIELDS[<?= $name?>]" value="N" /><?
                ?><input type="checkbox" name="FIELDS[<?= $name?>]" value="Y"<?if ($value == 'Y'){?> checked="checked"<?}?> /><?
                break;
            case 'view':
                ?><input type="hidden" name="FIELDS[<?= $name?>]" value="<?= htmlspecialcharsbx($value)?>" /><?
                echo htmlspecialcharsbx($value);
                break;
            case 'file_dialog':
                ?><input type="text" id="FIELDS_<?= $name?>" name="FIELDS[<?= $name?>]" value="<?= htmlspecialcharsbx($value)?>" size="50" /><?
                ?><input type="button" value="..." OnClick="BtnClickOpen()" />
                <?\CAdminFileDialog::ShowScript(
                Array(
                    'event' => 'BtnClickOpen',
                    'arResultDest' => array('FORM_NAME' => $this->formName, 'FORM_ELEMENT_NAME' => 'FIELDS_'.$name),
                    'arPath' => array('SITE' => htmlspecialcharsbx($_REQUEST['SITE_ID'])),
                    'select' => 'F',
                    'operation' => 'O',
                    'showUploadTab' => true,
                    'showAddToMenuTab' => false,
                    'fileFilter' => '',
                    'allowAllFiles' => true,
                    'SaveConfig' => true,
                )
            );
                break;
            case 'marker':
                static $bJSshow = false;
                $arMenuMarker = array();
                if (isset($arField['MARKERS']) && is_array($arField['MARKERS'])) {
                    foreach ($arField['MARKERS'] as $code => $title) {
                        $arMenuMarker[] = array(
                            'TEXT' => $title,
                            'TITLE' => $title.': '.$code,
                            'ONCLICK' => "__SetUrlVar('".$code."', 'mnu_".$name."', 'field_".$name."')"
                        );
                    }
                }
                $u = new \CAdminPopupEx('mnu_'.$name, $arMenuMarker, array('zIndex' => 2000));
                $u->Show();
                ?><input type="text" id="field_<?= $name?>" name="FIELDS[<?= $name?>]" value="<?= htmlspecialcharsbx($value)?>" size="50" /><?
                ?><input type="button" id="mnu_<?= $name?>" value="..."><?
                if (!$bJSshow):
                    $bJSshow = true;
                    ?>
                    <script type="text/javascript">
                        function __SetUrlVar(id, mnu_id, el_id) {
                            var obj_ta = BX(el_id);
                            obj_ta.focus();
                            obj_ta.value += id;

                            BX.fireEvent(obj_ta, 'change');
                            obj_ta.focus();
                        }
                    </script>
                    <?
                endif;
                break;
            default:
                $size = isset($arField['SIZE']) ? $arField['SIZE'] : '50';
                $attr = isset($arField['ATTR']) ? $arField['ATTR'] : array();
                if (isset($attr['size'])) {
                    $size = $attr['size'];
                }
                if (isset($arField['MULTIPLE'])) {
                    $arValues = is_array($value) ? $value : unserialize($value);
                    for ($i=0; $i<5; $i++) {
                        ?><input type="text" name="FIELDS[<?= $name?>][]" value="<?= htmlspecialcharsbx($arValues[$i])?>" size="<?= $size?>" /><br/><?
                    }
                } else {
                    ?><input type="text" id="field_<?= $name?>" name="FIELDS[<?= $name?>]" value="<?= htmlspecialcharsbx($value)?>" size="<?= $size?>" /><?
                }
                break;
        }
    }

    public function output($bNotForm=false, $arMenu=array()) {
        global $APPLICATION, $adminPage, $USER, $adminMenu, $adminChain;
        require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

        if ($bNotForm === false) {
            $arContext = array();
            $arContext['btn_list'] = array(
                'TEXT'	=> GetMessage('MAIN_ADMIN_MENU_LIST'),
                'LINK'	=> $this->module.'_'.$this->file_list.'?lang='.LANG,
                'ICON'	=> 'btn_list');
            if ($this->id !== false) {
                $arContext['btn_new'] = array(
                    'TEXT'	=> GetMessage('MAIN_ADMIN_MENU_ADD'),
                    'LINK'	=> $this->module.'_'.$this->file.'?lang='.LANG,
                    'ICON'	=> 'btn_new',
                );
                $arContext['btn_delete'] = array(
                    'TEXT'	=> GetMessage('MAIN_ADMIN_MENU_DELETE'),
                    'LINK'	=> 'javascript:if(confirm(\''.GetMessage('MAIN_ADMIN_MENU_DELETE').'?\'))'.
                        'window.location=\''.$this->module.'_'.$this->file.'?'.$this->generlKey.'='.$this->id.
                        '&amp;action=delete&amp;'.bitrix_sessid_get().'&amp;lang='.LANG.'\';',
                    'ICON'	=> 'btn_delete',
                );
            }
            if (!empty($arMenu)) {
                $arContext = array_merge($arContext, $arMenu);
                foreach ($arContext as $k => $arItem) {
                    if (empty($arItem) || $arItem===false) {
                        unset($arContext[$k]);
                    }
                }
            }
            $context = new \CAdminContextMenu($arContext);
            $context->Show();
        }

        if ($this->message !== null) {
            echo $this->message->Show();
        }
        echo $this->js;
        ?>
        <form action="<?= $this->Application()->GetCurPage()?>" enctype="multipart/form-data" method="post" name="<?= $this->formName?>">
            <?php
            echo bitrix_sessid_post();
            $this->tabControl->Begin();
            foreach ($this->arTabs as $arTabs) {
                $this->tabControl->BeginNextTab();
                if (strlen($arTabs['MESSAGE'])) {
                    ?>
                    <tr>
                        <td></td>
                        <td>
                            <?
                            echo BeginNote();
                            echo $arTabs['MESSAGE'];
                            echo EndNote();
                            ?>
                        </td>
                    </tr>
                    <?
                }
                if (strlen($arTabs['HTML'])) {
                    echo '<tr><td colspan="2">';
                    echo $arTabs['HTML'];
                    echo '</td></tr>';
                } elseif (strlen($arTabs['FUNC'])) {
                    call_user_func($arTabs['FUNC']);
                } else {
                    if ($this->d7class !== false) {
                        $arMap = call_user_func(array($this->d7class, 'getMap'));
                        $arTabs['FIELDS'] = array_flip($arTabs['FIELDS']);
                    }
                    foreach ($arTabs['FIELDS'] as $name => $arField):
                        if ($this->d7class !== false) {
                            $arField = array(
                                'TYPE' => array_key_exists('admin_type', $arMap[$name]) ? $arMap[$name]['admin_type'] : 'string',
                                'NAME' => $arMap[$name]['title'],
                                'HINT' => array_key_exists('admin_hint', $arMap[$name]) ? $arMap[$name]['admin_hint'] : '',
                                'REQUIRED' => array_key_exists('required', $arMap[$name]) ? $arMap[$name]['required'] : false,
                                'ATTR' => array_key_exists('admin_attr', $arMap[$name]) ? $arMap[$name]['admin_attr'] : array(),
                            );
                        }
                        if ($arField['TYPE'] == 'view' && (!isset($this->arValues[$name]) || !strlen($this->arValues[$name]))) {
                            continue;
                        }
                        if ($arField['TYPE'] == 'hidden') {
                            ?><input type="hidden" name="FIELDS[<?= $name?>]" value="<?= $this->arValues[$name]?>" /><?
                            continue;
                        }
                        if ($arField['TYPE'] == 'heading') {
                            ?>
                            <tr class="heading">
                                <td colspan="2"><?= $arField['NAME']?></td>
                            </tr>
                            <?
                            continue;
                        }
                        ?>
                        <tr valign="top">
                            <td width="30%" class="adm-detail-content-cell-l">
                                <?= isset($arField['HINT'])&&strlen($arField['HINT']) ? ShowJSHint($arField['HINT'], array('return' => true)) : ''?>
                                <?if ($arField['REQUIRED']):?>
                                    <span class="adm-required-field"><?= $arField['NAME']?>:</span>
                                <?else:?>
                                    <?= $arField['NAME']?>:
                                <?endif;?>
                            </td>
                            <td width="70%" class="adm-detail-content-cell-r">
                                <?$this->OutputField($name, $arField['TYPE'], $arField)?>
                            </td>
                        </tr>
                        <?
                    endforeach;
                }
            }
            if ($bNotForm === false) {
                $this->tabControl->Buttons(
                    array(
                        'disabled' => false,
                        'back_url' => $this->module.'_'.$this->file_list.'?lang='.LANG,
                    )
                );
            }
            $this->tabControl->End();
            if (!empty($this->arHiddenValues)):
                foreach ($this->arHiddenValues as $k => $v):
                    ?><input type="hidden" name="<?= htmlspecialcharsbx($k)?>" value="<?= htmlspecialcharsbx($v)?>" /><?
                endforeach;
            endif;?>
            <input type="hidden" name="lang" value="<?=LANG?>" />
            <input type="hidden" name="<?= $this->generlKey?>" value="<?=$this->id?>" />
        </form>
        <?
        require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
    }
}