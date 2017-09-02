<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult['rows'] as &$row){
	$row["PICTURE"] = CFile::GetFileArray($row["UF_PICTURE"]);
}
