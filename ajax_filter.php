<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Фильтрация");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Фильтрация");
?>

<?$APPLICATION->IncludeComponent("custom:ajax.filter","");?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

