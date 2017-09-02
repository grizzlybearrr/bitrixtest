<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Демонстрационная версия продукта «1С-Битрикс: Управление сайтом»");
$APPLICATION->SetPageProperty("NOT_SHOW_NAV_CHAIN", "Y");
$APPLICATION->SetTitle("Загрузчик");
?>



<?$APPLICATION->IncludeComponent("custom:ajax.json.importer","");?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>