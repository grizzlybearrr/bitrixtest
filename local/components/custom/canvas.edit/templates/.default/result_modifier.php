<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if(!$arResult["NEW_CANVAS"]) {
	$arResult["PICTURE"] = CFile::ResizeImageGet(
		$arResult["row"]["UF_PICTURE"],
		array("width"=>320, "height"=>240),
		BX_RESIZE_IMAGE_EXACT,
		true,
		false,
		false,
		100
	);


	if(empty($arResult["PICTURE"])) {
		$arResult["PICTURE"] = CFile::GetFileArray(
			$arResult["row"]["UF_PICTURE"]
		);
	}
}