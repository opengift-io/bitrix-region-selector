<?
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class opengift_region extends CModule
{
    var $MODULE_ID = "opengift.region";

    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $MODULE_GROUP_RIGHTS = 'N';
    public $MODULE_REQUIRED = [];

    public function __construct()
    {
        $arModuleVersion = [];

        include(dirname(__FILE__) . '/version.php');

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->PARTNER_NAME = 'OpenGift';
        $this->MODULE_NAME = 'Region Selector';
        $this->MODULE_DESCRIPTION = 'Region selector';
    }

    public function InstallDB()
    {
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/DataManager.php';
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/City.php';
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/Filial.php';

        RegisterModule($this->MODULE_ID);

        \OpenGift\BitrixRegionManager\FilialTable::reinstallTable(false);
        \OpenGift\BitrixRegionManager\CityTable::reinstallTable(false);


        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/themes/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/images/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/images', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/js/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js', true, true);
        CopyDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/components/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components', true, true);

        RegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'OpenGift\Dev\PropertyTypes\PropCity', 'GetUserTypeDescription');
        RegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, 'OpenGift\RegionManager\RegionManager', 'OnEpilog', 100500);

        return true;
    }

    public function UnInstallDB()
    {
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/DataManager.php';
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/City.php';
        require_once realpath(dirname(__FILE__) . '/../') . '/lib/Filial.php';

        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/themes/.default/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/');
        DeleteDirFiles($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $this->MODULE_ID . '/install/admin/', $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/');
        DeleteDirFilesEx('/bitrix/images/' . $this->MODULE_ID . '/');
        DeleteDirFilesEx('/bitrix/js/' . $this->MODULE_ID . '/');
        DeleteDirFilesEx('/bitrix/components/' . $this->MODULE_ID . '/');

        UnRegisterModuleDependences('iblock', 'OnIBlockPropertyBuildList', $this->MODULE_ID, 'OpenGift\Dev\PropertyTypes\PropCity', 'GetUserTypeDescription');
        UnRegisterModuleDependences('main', 'OnEpilog', $this->MODULE_ID, 'OpenGift\RegionManager\RegionManager', 'OnEpilog');

        \OpenGift\BitrixRegionManager\FilialTable::dropTableIfExist();
        \OpenGift\BitrixRegionManager\CityTable::dropTableIfExist();

        CAgent::RemoveModuleAgents($this->MODULE_ID);

        UnRegisterModule($this->MODULE_ID);

        return true;
    }

    public function DoInstall()
    {
        if ($GLOBALS['APPLICATION']->GetGroupRight('main') < 'W') {
            return;
        }

        if (is_array($this->MODULE_REQUIRED) && !empty($this->MODULE_REQUIRED)) {
            foreach ($this->MODULE_REQUIRED as $moduleName) {
                if (!IsModuleInstalled($moduleName)) {
                    die();
                }
            }
        }

        $this->InstallDB();
        return true;
    }

    public function DoUninstall()
    {
        if ($GLOBALS['APPLICATION']->GetGroupRight('main') < 'W') {
            return;
        }

        $this->UnInstallDB();
        return true;
    }

    public function GetModuleRightList()
    {
        return array(
            'reference_id' => array('D', 'W'),
            'reference' => array('[D] ' . Loc::getMessage('MANGO_SEO_DENIED'), '[W] ' . Loc::getMessage('MANGO_SEO_WRITE'),),
        );
    }
}