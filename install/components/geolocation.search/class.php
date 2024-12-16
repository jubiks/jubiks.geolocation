<?
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ErrorCollection;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

Loc::loadMessages(__FILE__);

class GeoIpSearch extends CBitrixComponent
{
	/** @var ErrorCollection $errors Errors. */
	protected $errors;

	public function __construct($component = \null)
    {
        parent::__construct($component);
        $this->arResult = [];
    }

    protected function addError($message, $code = '')
    {
        $this->errors->setError(new \Bitrix\Main\Error($message, $code));
    }

    protected function getErrors()
    {
        $arErrors = [];
        foreach ($this->errors as $error)
        {
            $arErrors[] = $error->getMessage();
        }

        return $arErrors;
    }

    protected function printErrors()
    {
        foreach ($this->errors as $error)
        {
            ShowError($error);
        }
    }

	protected function checkRequiredParams()
	{
        if(!\Bitrix\Main\Loader::includeModule('jubiks.geolocation')){
            $this->addError(Loc::getMessage('ERROR_GEOLOCATION_MODULE_NOT_INSTALLED'));
            return false;
        }

        return true;
	}

	protected function initParams()
	{

	}

	protected function prepareResult()
	{
		return true;
	}

    private function GetData()
    {
        $this->arResult['USER_IP'] = $_SERVER['REMOTE_ADDR'];
    }

    private function ipSearch($ipAddress)
    {
        $geolocation = new GeoLocationService();
        return $geolocation->getCityByIp($ipAddress);
    }

	public function executeComponent()
	{
	    global $APPLICATION;

        $this->errors = new ErrorCollection();
		$this->initParams();
        
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

        if($this->request->isPost() && check_bitrix_sessid())
        {
            $APPLICATION->RestartBuffer();
            $ipAddress = $this->request->getPost('ip') ?: null;

            // Проверяем, передан ли IP-адрес
            if (empty($ipAddress))
            {
                echo json_encode(['success' => false, 'message' => Loc::getMessage('ERROR_EMPTY_IP_ADDRESS')]);
                exit;
            }

            // Валидация IP-адреса
            if (filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4))
            {
                $result = $this->ipSearch($ipAddress);
                echo json_encode(['success' => true, 'result' => $result, 'ip' => $ipAddress]);
            } else {
                echo json_encode(['success' => false, 'message' => Loc::getMessage('ERROR_IP_ADDRESS')]);
            }
            exit;
        }
        
        $this->GetData();

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}
}