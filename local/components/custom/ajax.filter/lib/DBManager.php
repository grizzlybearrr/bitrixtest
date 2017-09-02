<?
use Bitrix\Main\Application;

//класс управления запросами к бд
class DBManager {
	protected $connection;

	function __construct() {
		$this->connection = Application::getConnection();
	}

	/**
	* запрос списка вариантов образования
	*/
	public function getQualificationList() {
		$query_str = <<<QUERY
			SELECT qualif.qualification_id as id, qualif.name as name
			FROM rns_qualification AS qualif
			ORDER BY  qualif.qualification_id;
QUERY;
		$result = $this->connection->query($query_str);
		return $result->FetchAll();

	}

	/**
	* запрос списка городов
	*/
	public function getCityList() {
		$query_str = <<<QUERY
			SELECT city.city_id as id, city.name as name
			FROM rns_city AS city
			ORDER BY  city.name;
QUERY;
		$result = $this->connection->query($query_str);
		return $result->FetchAll();
	}

	/**
	* запрос фильтрованного списка пользователей
	* получает на вход массив вида ['qualif'=>[], 'city'=>[]]
	* готовит условие и делает запрос с фильтрацией
	*/
	public function execFilter($param) {
		$where_condition = $this->getQueryConditionString($param);
		$data = $this->getFilteredUserList($where_condition);
		return $data;
	}

	/**
	* создаёт строку из одного из условий  фильтраций
	*/
	protected function getQueryConditionSingle($key, $key_param) {
		$cond_list =[];
		foreach ($key_param as $val) {
			$cond_list[]= "$key=$val";
		}
		return implode(' OR ', $cond_list);
	}

	/**
	* получает на вход двумерный массив условий фильтраций
	* с ключами вида city.city_id
	* возвращает строку фильтрации по всем условиям
	*/
	protected function getQueryConditionString($params) {
		$condition_list=[];
		foreach ($params as $key => $values) {
			$condition = $this->getQueryConditionSingle($key, $values);
			if($condition) {
				$condition_list[]='( '.$condition.' )';
			}
		}
		if(!empty($condition_list)) {
			return 'WHERE '.implode(' AND ', $condition_list);
		}
		
		return '';
	}

	/**
	* возвращает отфильтрованный список пользователей
	*/
	protected function getFilteredUserList($where_condition) {
		$query_str =<<<QUERY
		SELECT user.user_id as id, user.name as name, qualif.name as qualification, city.name as city
		FROM rns_users AS user
		  INNER JOIN (
		    rns_qualification AS qualif,
		    rns_user_city AS u_city,
		    rns_city AS city) 
		  ON (
		    user.qualification_id = qualif.qualification_id AND
		    user.user_id = u_city.user_id AND
		    u_city.city_id = city.city_id
		  )
		$where_condition
		ORDER BY user.name, user.user_id;
QUERY;

		$result = $this->connection->query($query_str);

		//склейка городов
		$list = [];
		while ($row = $result->fetch()){
			$id = $row['id'];
			if(!isset( $list[ $id ] )) {
				$list[ $id ] = $row;
			} else {
				$list[ $id ]['city'].= ', '.$row['city'];
			}
		}

		//удаляем столбец id
		foreach($list as &$user) {
			unset($user['id']);
		}

		return $list;
	}

	/**
	* возвращает полный список пользователей
	*/
	public function getFullUserList() {
		return $this->getFilteredUserList();
	}
}