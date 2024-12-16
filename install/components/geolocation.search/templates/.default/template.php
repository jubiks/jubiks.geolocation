<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var CAllMain $APPLICATION */
/** @var array $arParams */
/** @var array $arResult */
/** @var \CBitrixComponentTemplate $this */

use Bitrix\Main\Localization\Loc;

CJSCore::Init(array("jquery"));
$this->addExternalCss('https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css');
$this->addExternalJs('https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js');
?>
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-5"><?=Loc::getMessage('TITLE');?></h1>
            <form id="ipSearch" action="<?=$APPLICATION->GetCurPageParam('',[],false)?>" method="post">
                <?=bitrix_sessid_post()?>
                <div class="mb-4">
                    <label for="InputIPv4" class="form-label form-required"><?=Loc::getMessage('INPUT_IP');?></label>
                    <input type="text" name="ip" class="form-control" id="InputIPv4" value="<?=$arResult['USER_IP']?>" placeholder="192.168.1.1" required>
                    <span id="error-message" style="color: red; display: none;">Введите корректный IPv4-адрес</span>
                </div>

                <div class="form-action">
                    <button type="submit" class="btn btn-success btn-sm-block"><?=Loc::getMessage('SUBMIT_BUTTON_TEXT');?></button>
                </div>
            </form>

            <div id="result" style="white-space: pre-wrap; display: none; background-color: #f4f4f4; padding: 10px; border: 1px solid #ccc; margin-top: 20px;"></div>
        </div>
    </div>
</div>

