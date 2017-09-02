<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	ShowError($arResult['ERROR']);
	return false;

}
$APPLICATION->AddHeadScript($templateFolder."/js/sketchpad.js");
	
?>


<?
$canvasId = "add";
if(!$arResult["NEW_CANVAS"]) {
	$canvasId = $arResult["row"]["ID"];
}
?>

<?if($arResult["NEW_CANVAS"]):?>
	<button 
		class="btn btn-primary js-canvas-button"
		type="button"
		data-action="save"
		data-id="">
		Сохранить изображение
	</button>
<?else:?>
	<button 
		class="btn btn-primary js-canvas-button"
		type="button"
		data-action="update"
		data-id="<?=$canvasId?>">
		Сохранить изменения
	</button>
<?endif;?>

<br/>

<div class="b-editor">
	<canvas class="sketchpad" id="workarea"><canvas>
</div>

<br/>


<script>
	window.cnv_gallery = window.cnv_gallery || {}
	window.cnv_gallery.new_canvas = '<?=$arResult["NEW_CANVAS"]?>';
	window.cnv_gallery.img_src = '<?=$arResult["PICTURE"]["src"]?>';
</script>
