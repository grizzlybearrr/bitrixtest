<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$requiredModules = array('highloadblock');
foreach ($requiredModules as $requiredModule)
{
	if (!CModule::IncludeModule($requiredModule))
	{
		ShowError(GetMessage("F_NO_MODULE"));
		return 0;
	}
}

use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;

// hlblock info
$hlblock_id = $arParams['BLOCK_ID'];
if (empty($hlblock_id))
{
	ShowError(GetMessage('HLBLOCK_LIST_NO_ID'));
	return 0;
}
$hlblock = HL\HighloadBlockTable::getById($hlblock_id)->fetch();
if (empty($hlblock))
{
	ShowError(GetMessage('HLBLOCK_LIST_404'));
	return 0;
}

// check rights
if (isset($arParams['CHECK_PERMISSIONS']) && $arParams['CHECK_PERMISSIONS'] == 'Y' && !$USER->isAdmin())
{
	$operations = HL\HighloadBlockRightsTable::getOperationsName($hlblock_id);
	if (empty($operations))
	{
		ShowError(GetMessage('HLBLOCK_LIST_404'));
		return 0;
	}
}

$entity = HL\HighloadBlockTable::compileEntity($hlblock);

// uf info
$fields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_'.$hlblock['ID'], 0, LANGUAGE_ID);

// sort
$sort_id = 'ID';
$sort_type = 'DESC';
if (!empty($_GET['sort_id']) && (isset($fields[$_GET['sort_id']])))
{
	$sort_id = $_GET['sort_id'];
}
if (!empty($_GET['sort_type']) && in_array($_GET['sort_type'], array('ASC', 'DESC'), true))
{
	$sort_type = $_GET['sort_type'];
}

// pagen
if (isset($arParams['ROWS_PER_PAGE']) && $arParams['ROWS_PER_PAGE']>0)
{
	$pagenId = isset($arParams['PAGEN_ID']) && trim($arParams['PAGEN_ID']) != '' ? trim($arParams['PAGEN_ID']) : 'page';
	$perPage = intval($arParams['ROWS_PER_PAGE']);
	$nav = new \Bitrix\Main\UI\PageNavigation($pagenId);
	$nav->allowAllRecords(true)
		->setPageSize($perPage)
		->initFromUri();
}
else
{
	$arParams['ROWS_PER_PAGE'] = 0;
}

// start query
$mainQuery = new Entity\Query($entity);
$mainQuery->setSelect(array('*'));
$mainQuery->setOrder(array($sort_id => $sort_type));

// filter
if (
	isset($arParams['FILTER_NAME']) &&
	!empty($arParams['FILTER_NAME']) &&
	preg_match('/^[A-Za-z_][A-Za-z01-9_]*$/', $arParams['FILTER_NAME']))
{
	global ${$arParams['FILTER_NAME']};
	$filter = ${$arParams['FILTER_NAME']};
	if (is_array($filter))
	{
		$mainQuery->setFilter($filter);
	}
}

// pagen
if ($perPage > 0)
{
	$mainQueryCnt = $mainQuery;
	$result = $mainQueryCnt->exec();
	$result = new CDBResult($result);
	$nav->setRecordCount($result->selectedRowsCount());
	$arResult['nav_object'] = $nav;
	unset($mainQueryCnt, $result);

	$mainQuery->setLimit($nav->getLimit());
	$mainQuery->setOffset($nav->getOffset());
}

// execute query
//	->setGroup($group)
//	->setOptions($options);
$result = $mainQuery->exec();
$result = new CDBResult($result);

// build results
$rows = array();
$tableColumns = array();
while ($row = $result->fetch())
{
	$rows[] = $row;
}

$arResult['rows'] = $rows;
$arResult['fields'] = $fields;
$arResult['tableColumns'] = $tableColumns;
$arResult['sort_id'] = $sort_id;
$arResult['sort_type'] = $sort_type;

// for compatibility
$arResult['NAV_STRING'] = '';
$arResult['NAV_PARAMS'] = '';
$arResult['NAV_NUM'] = 0;

$this->IncludeComponentTemplate();