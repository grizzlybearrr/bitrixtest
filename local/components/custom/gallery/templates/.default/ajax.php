<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->IncludeComponent(
	"custom:canvas.ajax",
	"",
	Array(
		"BLOCK_ID" => $arParams["IBLOCK_ID"],
		"LIST_URL" => $arParams["SEF_FOLDER"],
	),
	$component
);?>