<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
?>

<?$APPLICATION->IncludeComponent(
	"custom:canvas.list", 
	"", 
	array(
		"COMPONENT_TEMPLATE" => ".default",
		"BLOCK_ID" => $arParams["IBLOCK_ID"],
		"DETAIL_URL" => "",
		"PAGEN_ID" => "page",
		"FILTER_NAME" => "",
		"CHECK_PERMISSIONS" => "N"
	),
	$component
);?>

<script>
	window.cnv_gallery = window.cnv_gallery || {}
	window.cnv_gallery.root = '<?=$arParams["SEF_FOLDER"]?>';
</script>