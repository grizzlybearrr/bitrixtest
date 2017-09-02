<?
$module_id = "pricemult";

IncludeModuleLangFile(__FILE__);

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

use Bitrix\Main\Application;


//сохранение полученного id нфоблока
function saveCurrentSelectedId( $module_id, $REQUEST_METHOD ) {
	//установка id инфоблока если изменился
	if($REQUEST_METHOD=="POST" && check_bitrix_sessid() ){
		COption::SetOptionString( 
			$module_id, 
			'SELECTED_IBLOCK_ID',
			intval( $_POST['choose_iblock_id'] )
		);
	}
}

//получаем текущий выбранный id инфоблока
function getCurrentSelected( $module_id ) {
	return COption::GetOptionString( $module_id, 'SELECTED_IBLOCK_ID');
}

//получаем список id инфоблоков являющихся каталогами
function getCatalogIblocksIdList() {
	//Получаем список каталогов
	$tableName = \Bitrix\Catalog\CatalogIblockTable::getTableName();
	$connection = Application::getConnection();
	$query_str = <<<QUERY
		SELECT IBLOCK_ID as id
		FROM $tableName
		ORDER BY  IBLOCK_ID;
QUERY;
	$response = $connection->query($query_str);

	$iblockIdList = [];
	while ($iblock = $response->Fetch()) {
		$iblockIdList[]= $iblock['id'];
	}

	return $iblockIdList;
}


//получение массива инфоблоков в формате удобном для 
//формирования селекта
function getIblocksViewList($iblockIdList) {
	//Получаем список каталогов
	$iblockIdList = getCatalogIblocksIdList();

	//получаем названия каталогов
	$dbResult = CIBlock::GetList( [], ['ID' => $iblockIdList] );

	//подготовка массива данных для селекта
	$refTile = []; //отображаемое значение
	$refValue = []; //применяемое значение
	while($iblock = $dbResult->GetNext()) {
		$refTile[] = '['.$iblock['ID'].'] '.$iblock['NAME'];
		$refValue[] = $iblock['ID'];
	}
	$iblockViewList = [
	    'REFERENCE' => $refTile,
	    'REFERENCE_ID' => $refValue
	];

	return $iblockViewList;
}

function ShowIblockSelector( $formName, $selectedId ) {

	//Получаем список каталогов
	$iblockIdList = getCatalogIblocksIdList();
	
	$iblockViewList = getIblocksViewList($iblockIdList);

	//формируем выпадающий список
	$selectBox = SelectBoxFromArray(
		"choose_iblock_id", 
		$iblockViewList,
		$selectedId,
		'',
		'class="typeselect"',
		true,
		'iblock_selector'
	);

	echo $selectBox;
};



//переменные настройки
$formName = 'iblock_selector';
$formAction = "{$APPLICATION->GetCurPage()}?mid=".urlencode($mid)."&amp;lang=".LANGUAGE_ID;

//при загрузке страницы сохраняем POST-параметры
saveCurrentSelectedId( $module_id, $REQUEST_METHOD );

$selectedId = getCurrentSelected( $module_id );
?>

<form method="post" action="<?=$formAction?>" name="<?=$formName?>">

	<?=bitrix_sessid_post()?>

	<table border="0" cellspacing="0" cellpadding="0" class="internal" align="left">
		<tr class="heading">
			<td><?echo GetMessage('PRICECONV_CONF_IBLOCKS_TITLE') ?></td>
		</tr>

		<tr>
			<td>
				<? ShowIblockSelector( $formName, $selectedId );?>
			</td>
		</tr>
	</table>

</form>
