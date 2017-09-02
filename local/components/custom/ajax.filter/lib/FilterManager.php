<?
//класс обработки поступающих запросов
class FilterManager {
	protected $db;
	protected $ajax;

	function __construct() {
		$this->db = new DBManager();
		$this->ajax = new AjaxHandler();
	}

	/**
	* запрос списка образований
	*/
	public function getQualificationList() {
		$data = $this->db->getQualificationList();

		return $data;
	}

	/**
	* запрос списка городов
	*/
	public function getCityList() {
		$data = $this->db->getCityList();

		return $data;
	}

	/**
	* запрос данных согласно фильтру
	*/
	public function execFilter() {
		$params = $this->ajax->getFilterParams();

		$data = $this->db->execFilter($params);
		$this->ajax->response( $data );
	}

	/**
	* запрос полного списка пользователей
	*/
	public function getFullUserList() {
		return $this->db->getFullUserList();
	}
}