<?
IncludeModuleLangFile(__FILE__);
/** @global CMain $APPLICATION */
global $APPLICATION;

$aMenu = array(
	"parent_menu" => "global_menu_settings",
	"section" => "search",
	"sort" => 100,
	"text" => GetMessage("PRICEMULT_ADM_MENU_TITLE"),
	"title" => GetMessage("PRICEMULT_ADM_MENU_TITLE"),
	"icon" => "currency_menu_icon",
	"page_icon" => "currency_menu_icon",
	"items_id" => "menu_pricemult",
	"items" => array(
		array(
			"text" => GetMessage("PRICEMULT_ADM_MENU_MAKE"),
			"url" => "pricemult_handle.php?lang=".LANGUAGE_ID,
			//"more_url" => Array("search_reindex.php"),
			"title" => GetMessage("PRICEMULT_ADM_MENU_MAKE"),
		),
	)
);
return $aMenu;

?>
