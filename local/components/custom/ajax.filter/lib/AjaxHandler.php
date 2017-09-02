<?
use \Bitrix\Main\Context;

//класс обработки ajax запросов
class AjaxHandler {
	protected $context; //данные для вывода
	protected $data; //данные для вывода

	function __construct() {
		$this->context = Context::getCurrent()->getRequest();
	}

	/**
	* получение параметров фильтрации
	*/
	public function getFilterParams() {
		$raw_data = $this->context->getPost('data');
		$data = json_decode($raw_data, true);

		$params = [
			'qualif.qualification_id' => $data['qualif'],
			'city.city_id' => $data['city']
		];
		return $params;
	}

	/**
	* выводит ответ на ajax запрос
	*/
	public function response($data) {
		global $APPLICATION;
		$APPLICATION->RestartBuffer();
		$table = $this->makeTableRows( $data );
		echo $table;
		die();
	}

	//выдаёт данные в виде строк таблицы
	protected function makeTableRows($data) {
		ob_start();
		foreach($data as $row){
			echo '<tr>';
				foreach($row as $val){
					echo "<td>$val</td>";
				}
			echo '</tr>';	
		}
		$tbl = ob_get_clean();
		return $tbl;
	}
}