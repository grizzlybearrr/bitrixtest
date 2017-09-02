<?
use Bitrix\Main\Entity;

//описание сущности холстов
class CanvasTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'canvas';
    }
    
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
			    'primary' => true,
			)),
            new Entity\IntegerField('UF_PICTURE'),
            new Entity\StringField('UF_PASSWORD')
        );
    }
}


//класс, для работы с холстами
class CanvasControl
{
	function __construct(){
		if( !array_key_exists("CANVAS_ALLOWED", $_SESSION )) {
			$_SESSION["CANVAS_ALLOWED"] = array();
		}
	}

	/**
	* сохраняет или обновляет элемент
	*
	* @id integer id сущности
	* @password string пароль редактирования сущности
	* @rawImage string изображение в формате DataURL
	*/
	public function save($id, $password, $rawImage){
		$data = explode(',', $rawImage);
		$data = str_replace(' ', '+', $data[1]);

		$fname = md5($data).".png";
		$path = $_SERVER["DOCUMENT_ROOT"]."/upload/save/";
		if(!file_exists ( path )) {
			mkdir( $path ); 
		}
		file_put_contents($path.$fname, base64_decode($data));
		$arFile = Array(
		    "name" => $fname,
		    "tmp_name" => $path.$fname,
		    "del" => "Y",
		);
		$fileId = CFile::SaveFile($arFile);

		//новый элемент
		if(!$id) {
			$dbResult = CanvasTable::add(array(
			    'UF_PICTURE' => $fileId,
			    'UF_PASSWORD' => md5( $password ),
			));

			if ($dbResult->isSuccess())
			{
			    $id = $dbResult->getId();
			}
			
		} else {
			//обновление
			$id = intval($id);

			$dbResult = CanvasTable::update($id, array(
			    'UF_PICTURE' => $fileId,
			));
		}
		//удаляем временный файл
		unlink( $path.$fname );

		//открываем доступ
		$this->writeId( $id );

		$result = array();
		$result["status"] = "ok";
		$result["id"] = $id;

		return $result;
	}

	/**
	* пытается открыть доступ к определённому холсту
	*
	* @id integer id сущности
	* @password string пароль редактирования сущности
	*/
	public function auth($id, $password) {
		$res = CanvasTable::getById( $id );
		$row = $res->fetch();

		if( $row["UF_PASSWORD"] == md5($password) ) {
			$this->writeId( $id );
			return true;
		} else {
			return false;
		}
	}

	/**
	* проверяет, есть ли у текущей сессии доступ к данному холсту
	*
	* @id integer id сущности
	*/
	public function check( $id ){
		return $this->checkId( intval($id) );
	}

	/**
	* проверяет разрешение из сессии
	*
	* @id integer id сущности
	*/
	protected function checkId($id) {
		if( !array_key_exists( $id, $_SESSION["CANVAS_ALLOWED"] )){
			return false;
		}
		return true;
	}

	/**
	* записывает разрешение в сессию
	*
	* @id integer id сущности
	*/
	protected function writeId($id) {
		$_SESSION["CANVAS_ALLOWED"][ $id ] = "Y";
	}
}