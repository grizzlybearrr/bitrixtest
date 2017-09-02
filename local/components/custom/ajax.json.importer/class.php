<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

require("lib/JsonFileReader.php");

CModule::IncludeModule("highloadblock"); 


use Bitrix\Highloadblock as HL; 
use Bitrix\Main\Entity; 



class JsonUploader {

	protected $step;
	protected $max_step; //максимально возможный шаг
	protected $reader;
	protected $structure;
	protected $filename;
	protected $stat; //текущая статистика чтения объектов
	protected $tables; //идентификаторы основной и вспомогательной таблицы

	function __construct() {
		$this->step=1;
		$this->max_step=6;
		$this->reader = new JsonFileReader();
		$this->structure = null;
		$this->filename = "";
		$this->stat = array(
			"started" => false, //был ли запуск
			"max_pos" => -1, //дистанция окончания блока данных
			"read" => 0,  //количество прочитанных объектов
			"insert" => 0, //количество созданных объектов
			"update" => 0, //количество обновлённых объектов
			"skip" => 0, //количество пропущеных объектов
		);
		$this->tables = array();
	}

	/**
	* восстановить состояние из сессии
	*/
	public function sessionRead() {
		if(!array_key_exists("JSON_UPLOAD", $_SESSION)) {
			$_SESSION["JSON_UPLOAD"] = array();
		}

		//восстановление структуры
		if(array_key_exists("STRUCTURE", $_SESSION["JSON_UPLOAD"])) {
			$this->structure = $_SESSION["JSON_UPLOAD"]["STRUCTURE"];
		}

		//восстановление имени файла
		if(array_key_exists("FILENAME", $_SESSION["JSON_UPLOAD"])) {
			$this->filename = $_SESSION["JSON_UPLOAD"]["FILENAME"];
		}

		//восстановление статуса загрузки
		if(array_key_exists("STAT", $_SESSION["JSON_UPLOAD"])) {
			$this->stat = $_SESSION["JSON_UPLOAD"]["STAT"];
		}

		//восстановление идентификаторов таблиц
		if(array_key_exists("TABLES", $_SESSION["JSON_UPLOAD"])) {
			$this->tables = $_SESSION["JSON_UPLOAD"]["TABLES"];
		}

		$this->reader->sessionRead();
	}

	/**
	* сохранить состояние в сессию
	*/
	public function sessionWrite() {
		if(!array_key_exists("JSON_UPLOAD", $_SESSION)) {
			$_SESSION["JSON_UPLOAD"] = array();
		}

		if($this->step != "") {
			$_SESSION["JSON_UPLOAD"]["STEP"] = $this->step;
		}

		if($this->filename != "") {
			$_SESSION["JSON_UPLOAD"]["FILENAME"] = $this->filename;
		}

		if($this->structure) {
			$_SESSION["JSON_UPLOAD"]["STRUCTURE"] = $this->structure;
		}

		//запись статуса загрузки
		$_SESSION["JSON_UPLOAD"]["STAT"] = $this->stat;

		//Запись идентификаторов таблиц
		$_SESSION["JSON_UPLOAD"]["TABLES"] = $this->tables;

		$this->reader->sessionWrite();
	}

	/**
	* удалить все временные данные
	*/
	public function cleanTemporaryData() {
		unset( $_SESSION["JSON_UPLOAD"] );
		unlink($this->filename);
		$this->reader->cleanTemporaryData();
	}

	/**
	* сохранить только-что загруженый файл
	*/
	public function saveFile($key) {
		$arFile = $_FILES[ $key ];

		$result = array(
			"success" => true,
			"message" => ""
		);
		// проверка типа файла
		if($arFile["type"] != "application/json" ) {
			$result["success"] = false;
			$result["message"] = "Разрешены только JSON-файлы";
		    return $result;
		}
		
		$uploads_dir = $_SERVER["DOCUMENT_ROOT"].'/upload';
		$tmp_name = $arFile["tmp_name"];
        $name = md5($arFile["name"]);
        $new_path = "$uploads_dir/$name";
        if (! move_uploaded_file($tmp_name, $new_path) ) {
        	$result["success"] = false;
			$result["message"] = "Ошибка в процессе загрузки файла";
		    return $result;
        }

        $result["message"] = "Файл успешно загружен";

		//указываем ридеру путь к файлу. он его запомнит
		$this->reader->setFilename( $new_path );
		//запоминаем место хранения файла для последующего удаления
        $this->filename = $new_path;

        return $result;
	}

	/**
	* валидация файла с данными
	*/
	public function fileValidate() {
		return $this->reader->validate();
	}

	public function readStructure() {
		$result = array(
			"success" => true,
			"message" => ""
		);

		$this->reader->startReading();
		$key = $this->reader->readKey();

		if($key != "settings") {
			$result["success"] = false;
			$result["message"] = "Не найдено описание структуры";
		    return $result;
		} 

		$struct = $this->reader->readObj();
		if(!$struct) {
			$result["success"] = false;
			$result["message"] = "Ошибка чтения структуры";
		    return $result;
		}

		$this->structure = $struct;

		$result["message"] = "Чтение структуры успешно завершено";
		return $result;

	}

	protected function readingStatusString() {
		$message = "Прочитано: {$this->stat["read"]}. ".
			   "Создано: {$this->stat["insert"]}. ".
			   "Обновлено: {$this->stat["update"]}. ".
			   "Пропущено: {$this->stat["skip"]}. ";
		return $message;
	}

	public function readData($exec_time=5) {
		$result = array(
			"success" => true,
			"status" => "ok", //используется при повторных запусках
			"message" => ""
		);

		if(!$this->stat["started"]) {
			$this->stat["started"] = true;

			$key = $this->reader->readKey();


			if($key !=="items") {
				$result["success"] = false;
				$result["message"] = "Не найден блок данных";
			    return $result;
			}

			$this->stat["max_pos"] = $this->reader->distance("]");
		}

		$data_tbl_id = $this->tables["data"];
        $dataClass = $this->getDataClass( $data_tbl_id );

		$value_tbl_id = $this->tables["value"];
        $valueClass = $this->getDataClass( $value_tbl_id );

        $timeout = false;


        $start_time = microtime(true);
		do {
			//чтение очередного объекта
			$data = $this->reader->readObj( $this->stat["max_pos"] );

			if($data) {
				//запись данных
				$res = $this->saveData($data, $dataClass, $valueClass);

				$this->stat["read"]++;
				if($res !== false) {
					$this->stat[ $res ]++;
				}

				$result["status"] = "continue"; //используется при повторных запусках
				$result["message"] = $this->readingStatusString();
			} else {
				$result["status"] = "success"; //прочитан почледний объект
				$result["message"] = $this->readingStatusString();
				break;
			};

			$step_time = microtime(true) - $start_time;

			//sleep(3);

		} while($step_time < $exec_time);

		return $result;

	}

	/**
	* получение хайлоад-объекта для вставки записей
	*/
	protected function getDataClass( $id ) {
		$hlblock = HL\HighloadBlockTable::getById( $id )->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $dataClass = $entity->getDataClass();

        return $dataClass;
	}

	/**
	* сохранение сложного значения
	*/
	protected function saveComplexValue($obj, $valueClass) {
		$data = array();
		foreach($obj as $key=>$field) {
			$propName = $this->getPropName( $key );
			if(!is_array($field)) {
				$data[ $propName ] = $field;
			} 
		}
		$result = $valueClass::add( $data );

		if ($result->isSuccess()) 
        {         
			return $result->getId();         
        } else { 
			return false;
        }       
	}

	/**
	* вставка нового значения
	*/
	protected function insertData( $obj, $dataClass, $valueClass ) {
		$data = array();
		foreach($obj as $key=>$field) {
			$propName = $this->getPropName( $key );
			if(!is_array($field)) {
				$data[ $propName ] = $field;
			} else {
				$id = $this->saveComplexValue($field, $valueClass);
				if($id) {
					$data[ $propName ] = $id;
				}
			}
		}

		$result = $dataClass::add( $data );
		if ($result->isSuccess()) {         
			return "insert";         
		}
		return false;

	}


	/**
	* сравнение старого и нового значения
	*/
	protected function equal($dbval, $field) {
		foreach ($field as $key => $value) {
			$propName = $this->getPropName( $key );
			if(!array_key_exists($propName, $dbval)) return false;
			if($dbval[ $propName ] != $value) return false;
		}
		return true;
	}

	/**
	* обновление такой же записи
	*/
	protected function updateData($obj, $dataClass, $valueClass) {
		$propName = $this->getPropName( "id" );
		$propVal = $obj["id"];
		$result = $dataClass::getList(
			array("filter"=>array( $propName => $propVal ))
		)->fetch();

		if(!$result) {
			return false; //говорим, что нет похожей записи и обновление не произошло
		}

		$data = array(); //вставляемые значения
		foreach($obj as $key=>$field) {
			$propName = $this->getPropName( $key );
			if(!is_array($field)) {
				continue;
			}

			//в базе ещё нет такого значения
			if(!$result[$propName]) {
				$id = $this->saveComplexValue($field, $valueClass);
				if($id) {
					$data[ $propName ] = $id;
				}
			} else {
				//получаем значение из базы
				$dbval = $valueClass::getById( $result[$propName] )->fetch();
				if(!$this->equal($dbval, $field)) {
					//удаляем старое значение
					$valueClass::delete( $result[$propName] ); 
					//создаём новое
					$id = $this->saveComplexValue($field, $valueClass);
					if($id) {
						$data[ $propName ] = $id;
					}
				}
			}
		}

		if(!empty($data)) {
			$id = $result["ID"];
			$result = $dataClass::update( $id, $data );

			if ($result->isSuccess()) {         
				return "update";         
	        }     
		}
		return "skip";
	}


	/**
	* сохранение загруженых данных
	*/
	protected function saveData( $obj, $dataClass, $valueClass ) {
		$status = $this->updateData($obj, $dataClass, $valueClass);
		if( $status !== false ) {
			return $status;
		};

		//обновление не произошло - делаем вставку
		return $this->insertData( $obj, $dataClass, $valueClass );
	}


	public function buildStructure() {
		$result = array(
			"success" => true,
			"message" => ""
		);

		$data_tbl = null;
		$value_tbl = null;

		//выясняем основную и вспомогательную таблицу
		foreach ($this->structure as $key => $data) {
			//из простыл или составных свойств состоит таблица
			//если в поле будет массив, то это таблица простых значений
			$is_complex_val = true; 
			foreach($data as $field) {
				if(is_array($field)) {
					$is_complex_val = false;
					break;
				}
			}

			if($is_complex_val) {
				$data_tbl = array(
					"name" => $key,
					"structure" => $data,
				);
			} else {
				$value_tbl = array(
					"name" => $key,
					"structure" => $data,
				);
			}
		}

		//создаём таблицы
		//сначала вспомогательную
		$value_tbl_id = 0;
		$data_tbl_id = 0;
		if($value_tbl) {
			$value_tbl_id = $this->composeValuesIblock($value_tbl);
		}

		if($data_tbl && $value_tbl_id) {
			$data_tbl_id = $this->composeValuesIblock($data_tbl, $value_tbl_id);
		}

		//сохраняем id инфоблоков
		if($data_tbl_id && $value_tbl_id) {

			$this->tables["data"] = $data_tbl_id;
			$this->tables["value"] = $value_tbl_id;
		}

		$result["message"] = "Инфоблоки успешно созданы";

		return $result;
	}

	//Возвращает подходящее имя для сущности
	protected function getEntityName( $name ) {
		//должно начинаться с заглавной буквы и состоять только из латинских букв и цифр
		return ucfirst( $name );
	}

	//Возвращает подходящее имя для таблицы
	protected function getTableName( $name ) {
    	//должно состоять только из строчных латинских букв, цифр и знака подчеркивания
    	return strtolower( $name );
	}

	//Возвращает подходящее имя для свойства
	protected function getPropName( $name ) {
    	return "UF_KEY_".strtoupper( $name );
	}

	//Возвращает подходящее имя для свойства
	protected function getPropXmlId( $name ) {
    	return "XML_ID_KEY_".strtoupper( $name );
	}

	//возвращает ID существующей таблицы или false
	protected function getTableExist( $tableName ) {
		// select data
		$rsData = HL\HighloadBlockTable::getList(array(
			"filter" => array("TABLE_NAME" => $tableName)
		));

		$data = $rsData->fetch();
		if(!$data) return false;

		return $data["ID"];
	}

	//создать пустую таблицу или вернуть значение
	protected function createHLIblock( $tbl ) {
		$entityName = $this->getEntityName( $tbl["name"]."Data" );
		$tableName = $this->getTableName( $tbl["name"]."_data" );

		$tbl_id = $this->getTableExist( $tableName );
		if($tbl_id !== false) return $tbl_id;

		//вставка новой таблицы
		$entity = HL\HighloadBlockTable::add(
			array(
				"NAME" => $entityName , 
				"TABLE_NAME" => $tableName
			)
		);

		return $entity->getId();
	}

	//возвращает ID существующего свойства или false
	protected function isPropExist( $name , $entity_id ) {
		// select data
		$propName = $this->getPropName($name);
		$dbRes = CUserTypeEntity::GetList(array(),array("FIELD_NAME"=>$propName, "ENTITY_ID"=>$entity_id));
		$res = $dbRes->Fetch();

		if( $res ) return true;
		else return false;
	}

	//получить массив настроек для свойства
	protected function getIblockPropertySettings($data, $val_entity_id=-1) {
		$settings = array(
	        /* Значение по умолчанию */
	        'DEFAULT_VALUE' => '',
	        'SIZE'          => '20', //Размер поля ввода для отображения 
	        'ROWS'          => '1', //Количество строчек поля ввода
	        'MIN_LENGTH'    => '0',
	        'MAX_LENGTH'    => '0',
	        'REGEXP'        => '',
		);

		//если у нас составное свойство
		if(!is_array($data)) {
			$settings["HLBLOCK_ID"] = $val_entity_id;
			$settings["HLFIELD_ID"] = 0; //привязка к полю ID
		} else {
			$settings["DEFAULT_VALUE"] = $data["default"];
		}

		return $settings;
	}

	//получить тип для свойства
	protected function getIblockPropertyType($data) {
		//если у нас составное свойство
		if(!is_array($data)) {
			return "hlblock";
		} else {
			return gettype($data["default"]);
		}
	}

	//создать новое свойство в указанном инфоблоке
	protected function createIblockProperty( $key, $data, $entity_id, $val_entity_id=-1 ) {

		if( $this->isPropExist( $key,  $entity_id ) ) {
			return true;
		}

		//создание нового свойства
		$oUserTypeEntity    = new CUserTypeEntity();

		$propName = $this->getPropName($key); //код свойства
		$propSettings = $this->getIblockPropertySettings($data, $val_entity_id); //настройки св-ва
		$viewName = is_array($data)? $data["name"]: $data; //видимое название свойства
		 
		$aUserFields    = array(
			"ENTITY_ID"         => "HLBLOCK_".$entity_id,//Идентификатор сущности, к которой будет привязано свойство.
		    "FIELD_NAME"        => $propName, // Код поля. Всегда должно начинаться с UF_ 
		    "USER_TYPE_ID"      => $this->getIblockPropertyType($data), // Указываем тип нового пользовательского свойства
			"XML_ID"            => $this->getPropXmlId($key),
			"SORT"              => 500,
			"MULTIPLE"          => "N",
			"MANDATORY"         => "N", //Обязательное или нет свойство
			"SHOW_FILTER"       => "N", //Показывать в фильтре списка.
			"SHOW_IN_LIST"      => "", //Не показывать в списке
		    "EDIT_IN_LIST"      => "", //Не разрешать редактирование пользователем.
		    "IS_SEARCHABLE"     => "N", //Значения поля участвуют в поиске

		    /*
			* Дополнительные настройки поля (зависят от типа).
			* В нашем случае для типа string
			*/
		    "SETTINGS"          => $propSettings,

		    //Подпись в форме редактирования
		    "EDIT_FORM_LABEL"   => array(
		        "ru"    => $viewName,//"Пользовательское свойство",
		        "en"    => "User field",
		    ),
			// Заголовок в списке 
		    "LIST_COLUMN_LABEL" => array(
		        "ru"    => $viewName,//"Пользовательское свойство",
		        "en"    => "User field",
		    ),
			// Подпись фильтра в списке 
		    "LIST_FILTER_LABEL" => array(
		        "ru"    => $viewName,//"Пользовательское свойство",
			"en"    => "User field",
			),
			// Сообщение об ошибке (не обязательное) 
		    "ERROR_MESSAGE"     => array(
		        "ru"    => "Ошибка при заполнении свойства ".$viewName,
		        "en"    => "An error in completing field ".$viewName,
			),
			// Помощь 
		    "HELP_MESSAGE"      => array(
		        "ru"    => "",
				"en"    => "",
	    	),
		);
		 
		$iUserFieldId   = $oUserTypeEntity->Add( $aUserFields ); 
	}

	//Скомпоновать таблицу, создать свойства или вернуть значение
	protected function composeValuesIblock( $data, $val_entity_id = -1) {
		$entity_id = $this->createHLIblock( $data );

		foreach ($data["structure"] as $key => $value) {
			$this->createIblockProperty( $key, $value, $entity_id, $val_entity_id );
		}

		//инфоблоку данных понадобится ещё одно поле
		if($val_entity_id > -1) {
			$codeField = array(
				"name" => "Symbol code", 
				"default" => "xml-"
			);

			$this->createIblockProperty( "id", $codeField, $entity_id );
		}

		return $entity_id;
	}
}




