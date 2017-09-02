<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!empty($arResult['ERROR']))
{
	echo $arResult['ERROR'];
	return false;
}

$APPLICATION->AddHeadScript($templateFolder."/colorbox-master/jquery.colorbox-min.js");
$APPLICATION->SetAdditionalCss($templateFolder."/colorbox-master/colorbox.css");
?>

<div class="row">
	<button class="btn btn-primary js-canvas-button"
	type="button"
		data-action="add">
		Создать рисунок
	</button>
</div>

<br/>

<!-- data -->
<div class="b-gallery">
	<div class="row">
	  
		<?foreach ($arResult['rows'] as $row): ?>
		    <div class="col-3">
				<div class="b-gallery__item">
					<a class="b-gallery__picture b-picture js-colorbox" href="<?=$row["PICTURE"]["SRC"]?>" rel="gallery">
						<img class="b-picture__image" src="<?=$row["PICTURE"]["SRC"]?>" />
					</a>
					<div class="b-gallery__handle">
						<button class="btn btn-secondary btn-block btn-sm js-canvas-button"
							type="button"
							data-action="edit"
							data-id="<?=$row["ID"]?>">
							Редактировать
						</button>
					</div>
				</div>
			</div>
		<? endforeach; ?>
	</div>
</div>

<script>
	window.cnv_gallery = window.cnv_gallery || {}
	window.cnv_gallery.root = '<?=$arParams["SEF_FOLDER"]?>';
</script>

<?php
if ($arParams['ROWS_PER_PAGE'] > 0):
	$APPLICATION->IncludeComponent(
		'bitrix:main.pagenavigation',
		'',
		array(
			'NAV_OBJECT' => $arResult['nav_object'],
			'SEF_MODE' => 'N',
		),
		false
	);
endif;
?>
