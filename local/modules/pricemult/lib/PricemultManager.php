<?

use \Bitrix\Main\Context;

\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('catalog');

class PricemultManager
{
	protected $moduleId;
	protected $iblockId;
	protected $sectionId; //текущий выбранный раздел
	protected $percent; //текущий процент наценки
	protected $percentInputName; //название поля ввода процента
	protected $sectionInputName; //название поля ввода раздела
	protected $context; //текущий контекст
	protected $historyTable; //история запросов

	function __construct() {
		$this->context = Context::getCurrent()->getRequest();
		$this->moduleId = 'pricemult';
		$this->iblockId = $this->getCurrentSelectedIblock();
		$this->percent = 0;

		$this->sectionInputName = 'IBLOCK_SECTION';
		$this->percentInputName = 'PERCENT';
		$this->historyTable = new PricemultHistoryTable();
	}

	//получаем текущий выбранный id инфоблока
	protected function getCurrentSelectedIblock() {
		return COption::GetOptionString( $this->moduleId, 'SELECTED_IBLOCK_ID');
	}

	//готова ли страница к работе
	public function isReady() {
		if(intval($this->iblockId)>0) {
			return true;
		}
		return false;
	}

	//обработка всех post событий
	public function processPostAction() {
		//заполучить нужные данные
		$this->getInputData();

		if(!$this->isValidInput()) {
			return;
		}

		//если данных достаточно,
		//то сделать наценку
		$this->makeMultiply();
		//записать в историю
		$this->storeHistoryAction();
	}

	//получает данные из post. процент и раздел
	protected function getInputData() {
		$this->sectionId = $this->context->getPost($this->sectionInputName);
		$this->percent = $this->context->getPost($this->percentInputName);
		$this->percent = floatval($this->percent);
	}

	//получает список разделов из базы
	protected function getSectionsList() {
		if(!$this->iblockId) return [];

		$dbResult = CIBlockSection::GetTreeList(
			['IBLOCK_ID'=>$this->iblockId], 
			['ID', 'NAME', 'DEPTH_LEVEL']
		);
		$sectionList = [];
		while($section = $dbResult->GetNext()) {
			$sectId = $section['ID'];
			if($sectId == $this->sectionId) {
				$section['SELECTED'] = 'Y';
			}
			$sectionList[ $sectId ] = $section;
		}
		return $sectionList;
	}

	//вывод селектора раздела
	public function showSectionSelector() {
		$list = $this->getSectionsList();
		?>
		<select name="<?=$this->sectionInputName?>" size="14">
			<?
				$isSelected = !empty($list) && $this->sectionId==0;
				$selected = $isSelected? 'selected': '';
			?>
			<option value="0" <?=$selected?> >
				<?=GetMessage('IBLOCK_UPPER_LEVEL')?>	
			</option>

			<?foreach($list as $section):?>
				<?$selected = ($section['SELECTED'] == 'Y')? 'selected': '';?>
				<option value="<?=$section['ID']?>" <?=$selected?> >
					<?=str_repeat(' . ', $section['DEPTH_LEVEL'])?>
					<?=$section['NAME']?>
				</option>
			<?endforeach;?>
		</select>
		<?
	}

	//отобразить поле ввода процента
	public function showPercentInput() {
		?>
		<input type="text" 
			size="1" 
			name="<?=$this->percentInputName?>" 
			maxlength="10" 
			value="<?=$this->percent?>" />
		<?
	}

	

	//отобразить историю наценок
	public function showHistoryTable( $title="" ) {
		$this->historyTable->show($title);
	}

	//сделать пометку в журнале
	protected function storeHistoryAction() {
		$action = [
			'IBLOCK_ID' => $this->iblockId,
			'SECTION_ID' => $this->sectionId,
			'PERCENT' => $this->percent
		];
		$this->historyTable->storeAction( $action );
	}

	//делает наценку группе товаров
	protected function makeMultiply() {
		$list = $this->getFilteredProductList();
		$this->updateListPrices($list);
	}

	//получение списка товаров, подходящих под условие
	protected function getFilteredProductList() {
		$dbResult = CIBlockElement::GetList(
		[],
		[
			'IBLOCK_ID'=>$this->iblockId,
			'SECTION_ID'=>$this->sectionId,
			'INCLUDE_SUBSECTIONS'=>'Y'
		],
		false,
		false,
		['ID','NAME','CATALOG_GROUP_1']);

		$list = [];
		while($product = $dbResult->GetNext()) {
			$id = $product['ID'];
			$price = $product['CATALOG_PRICE_1'];
			$priceId = $product['CATALOG_PRICE_ID_1'];
			$currency = $product['CATALOG_CURRENCY_1'];

			if($price) {
				$list[$id] = [
					'ID' => $id,
					'PRICE' => $price,
					'PRICE_ID' => $priceId,
					'CURRENCY' => $currency
				];
			}
		}
		return $list;
	}

	//делает наценку группе товаров
	//получает массив вида ID => базовая_цена
	protected function updateListPrices($list) {
		foreach ($list as $id => $product) {
			$price = $product['PRICE'];
			$product['PRICE'] = $price + $price*$this->percent/100;
			$this->updateItemPrice($product);
		}
	}

	protected function updateItemPrice($product) {
		$result = CPrice::Update(
			$product['PRICE_ID'],
			[
				'PRODUCT_ID' => $product['ID'],
				'PRICE' => $product['PRICE'],
				'CATALOG_GROUP_ID' => 1,
				'CURRENCY' => $product['CURRENCY']
			]
		);
	}

	//проверяет достаточность и корректность данных перед наценкой
	protected function isValidInput() {
		if(!$this->iblockId) return false;
		if(!$this->percent) return false;
		return true;
	}
}