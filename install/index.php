<?
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

Class jubiks_geolocation extends CModule
{
    var $MODULE_ID = "jubiks.geolocation";
    var $MODULE_GROUP_RIGHTS = "Y";
    public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $PARTNER_NAME;
	public $PARTNER_URI;
    
    function jubiks_geolocation()
    {
        $arModuleVersion = array();
        
        include($this->GetModInstPath()."/version.php");
    
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        }

        $this->MODULE_NAME = Loc::getMessage("JUBIKS_GEOLOCATION_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("JUBIKS_GEOLOCATION_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("JUBIKS_GEOLOCATION_PARTNER_NAME");
		$this->PARTNER_URI = Loc::getMessage("JUBIKS_GEOLOCATION_PARTNER_URI");
    }
    
    function GetModInstPath(){
        return dirname(__FILE__);
    }

    function InstallDB(){
        global $DB, $APPLICATION;
        $this->errors = false;

        $this->errors = $DB->RunSQLBatch($this->GetModInstPath()."/db/mysql/install.sql");

        if($this->errors !== false)
        {
            $APPLICATION->ThrowException(implode("", $this->errors));
            return false;
        }

        return true;
    }


    function UnInstallDB($arParams = array()){
        global $DB;

        if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
        {
            $DB->RunSQLBatch($this->GetModInstPath()."/db/mysql/uninstall.sql");
        }

        return true;
    }

    function InstallAgents(){
        $curDate = new \Bitrix\Main\Type\DateTime();
        $curDate->add('1 minutes');

        \CAgent::RemoveAgent("\GeoLocationService::baseUpdater();", $this->MODULE_ID);
        \CAgent::AddAgent(
            "\GeoLocationService::baseUpdater();",
            $this->MODULE_ID,
            "N",
            86400,
            $curDate->toString(),
            "Y",
            $curDate->toString(),
            10
        );

        return true;
    }

    function UnInstallAgents(){
        \CAgent::RemoveModuleAgents($this->MODULE_ID);
    }

    function InstallFiles() {}

    function UnInstallFiles() {
        DeleteDirFilesEx('upload/SxGeo');
    }
    
    function DoInstall()
    {
        global $APPLICATION;

        $this->InstallDB();
        $this->InstallAgents();
        $this->InstallFiles();
        
        RegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(GetMessage("JUBIKS_GEOLOCATION_INSTALL_MODULE"), $this->GetModInstPath()."/step.php");
    }
    
    function DoUninstall()
    {
        global $APPLICATION, $step;
        
        if($step == 2){

            if($_REQUEST["savedata"] != 'Y')
                COption::RemoveOption($this->MODULE_ID);

            $this->UnInstallDB(["savedata" => $_REQUEST["savedata"]]);
            $this->UnInstallAgents();
            $this->UnInstallFiles();
            
            UnRegisterModule($this->MODULE_ID);
            
            $APPLICATION->IncludeAdminFile(GetMessage("JUBIKS_GEOLOCATION_UNINSTALL_MODULE"), $this->GetModInstPath()."/unstep2.php");
            
        }else{
			$APPLICATION->IncludeAdminFile(GetMessage("JUBIKS_GEOLOCATION_UNINSTALL_MODULE"), $this->GetModInstPath()."/unstep1.php");
		}
    }
}
?>