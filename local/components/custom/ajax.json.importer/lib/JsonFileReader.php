<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

/**
* класс обеспечивает кусочное чтение json-файлов
*/
class JsonFileReader {
	protected $filename;
	protected $handle;

	/**
	* начальная инициализация ридера
	*/
	function __construct() {
		$this->handle = null;
		$this->filename = "";
	}

	/**
	* восстановить прошлое состояние из сессии
	*/
	public function sessionRead() {
		if(!array_key_exists("JSON_READER", $_SESSION)) {
			$_SESSION["JSON_READER"] = array();
		}

		//восстановление пути к файлу
		if(array_key_exists("FILENAME", $_SESSION["JSON_READER"])) {
			$this->filename = $_SESSION["JSON_READER"]["FILENAME"];
		}

		//открытие прошлого файла
		if($this->filename) {
			$this->open( $this->filename );
		}

		//восстановление позиции в файле
		if(array_key_exists("HANDLE_POS", $_SESSION["JSON_READER"])) {
			$pos = $_SESSION["JSON_READER"]["HANDLE_POS"];
			if($this->handle) {
				$this->seek( $pos );
			}
		}
	}

	/**
	* сохранить состояние в сессию
	*/
	public function sessionWrite() {
		if(!array_key_exists("JSON_READER", $_SESSION)) {
			$_SESSION["JSON_READER"] = array();
		}

		if($this->filename != "") {
			$_SESSION["JSON_READER"]["FILENAME"] = $this->filename;
		}

		if($this->handle) {
			$_SESSION["JSON_READER"]["HANDLE_POS"] = $this->position();
			
		}
	}

	/**
	* устанавливает имя файла для открытия
	*/
	public function setFilename( $filename ) {
		$this->filename = $filename;
	}

	/**
	* открывает указанный файл
	*/
	public function open( $filename ) {


		if($filename == "") return false;

		$this->filename = $filename;
		$this->handle = fopen($this->filename, "r");

		if(!$this->handle) {
			return false;
		}
		return true;
	}

	/**
	* удалить все временные данные
	*/
	public function cleanTemporaryData() {
		if($this->handle) {
			fclose($this->handle);
		}
		unset( $_SESSION["JSON_READER"] );
	}

	/**
	* установить указатель в нужную позицию файла
	*/
	public function seek($pos) {
		fseek( $this->handle, $pos );
	}

	/**
	* запросить позицию в файле
	*/
	public function position() {
		return ftell( $this->handle );
	}

	/**
	* смещает файловый указатель в поиске символа
	*/
	protected function search($sym, $maxPos=-1) {
		$c = "";
		while($c != $sym) {
			$c = fgetc($this->handle);
			//достигли конца файла
			if($c === false) return false;
			//достигли ограничителя
			if($maxPos >0 && $this->position() >= $maxPos) {
				return false;
			}
		}
		return $this->position();
	}

	/**
	* выдаёт позицию нужного символа в файле
	*/
	public function distance($sym) {
		$pos = $this->position();

		$c = "";
		while($c != $sym) {
			$c = fgetc($this->handle);
			//достигли конца файла
			if($c === false) return false;
		}
		$dist = $this->position();

		$this->seek( $pos );

		return $dist;
	}

	/**
	* посимвольное считывание до стоп-символа
	*/
	protected function readUntil($sym) {
		$str = "";
		$c = "";
		do {
			$c = fgetc($this->handle);
			if($c === false) return $str;
			if($c == $sym) return $str;

			$str.= $c;
		} while($c != $sym);

		return $str; //до этой строчки не должно доходить
	}

	public function readKey() {
		$this->search('"');
		$key = $this->readUntil('"');
		return $key;
	}

	/**
	* считывает объект ориентируясь на парность фигурных скобок
	*/
	public function readObj($maxPos = -1) {
		if( $this->search( '{', $maxPos ) === false ) return false;

		$str = "{";
		$balance = 1; //баланс открывающих и закрывающих скобок
		while($balance>0) {
			$c = fgetc($this->handle);
			if($c === false) break;
			if($c == "{") $balance++;
			if($c == "}") $balance--;

			$str.= $c;
		}
		$obj = json_decode($str, true);
		return $obj;
	}

	/**
	* валидация содержимого
	*/
	public function validate() {
		$this->seek(0);

		$balance = 0; //баланс открывающих и закрывающих фигурных скобок
		$balance1 = 0; //баланс открывающих и закрывающих квадратных
		$balance2 = true; //баланс кавычек
		while(1) {
			$c = fgetc($this->handle);
			if($c === false) break; //конец файла
			if($c == "{") $balance++;
			if($c == "}") $balance--;

			if($c == "[") $balance1++;
			if($c == "]") $balance1--;

			if($c == '"') $balance2 = !$balance2;
		}
		$this->seek(0);

		return ( $balance == 0 && $balance1 == 0 && $balance2 == true );
	}

	//заход внутрь основного объекта
	public function startReading() {
		$this->search("{");
	}
}