<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Context;


$filter = new FilterManager();

//определяем тип пришедшего запроса
$request = Context::getCurrent()->getRequest();

if( $request->isPost() ) {
	$filter->execFilter();
	//после этого выполнение страницы прекратится
}

$arResult['city_list'] = $filter->getCityList();
$arResult['qualif_list'] = $filter->getQualificationList();
$arResult['user_list'] = $filter->getFullUserList();


$this->IncludeComponentTemplate();