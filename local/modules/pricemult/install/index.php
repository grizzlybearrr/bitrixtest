<?

IncludeModuleLangFile(__FILE__);

use Bitrix\Main\Application;


class pricemult extends CModule
{
	var $MODULE_ID = 'pricemult';
	var $MODULE_NAME;

	var $MODULE_VERSION = '0.0.1';
    var $MODULE_VERSION_DATE;
    var $MODULE_DESCRIPTION;
    var $MODULE_GROUP_RIGHTS = 'Y';

    function __construct() {
    	$this->MODULE_NAME = GetMessage('PRICECONV_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('PRICECONV_MODULE_DESCRIPT');
		$this->MODULE_VERSION_DATE = "2017-08-28 14:03:00";
    }

	function DoInstall() {
		global $DB, $APPLICATION, $step;

		$this->createHistoryTable();
		$this->installAdminFiles();

		RegisterModule( $this->MODULE_ID );
		$APPLICATION->IncludeAdminFile("Установка модуля наценок", __DIR__."/step.php");
	}

	function DoUninstall() {
		global $DB, $APPLICATION, $step;

		//таблицу истории вызовов по умолчанию удаляем
		$this->removeHistoryTable();
		$this->removeAdminFiles();

		UnRegisterModule( $this->MODULE_ID );
		$APPLICATION->IncludeAdminFile("Удаление модуля наценок", __DIR__."/unstep.php");
	}

	/**
	* создание таблицы истории запросов
	*/
	protected function createHistoryTable() {
		//создаём таблицу в базе данных
		$connection = Application::getConnection();
		$query_str = <<<QUERY
			CREATE TABLE IF NOT EXISTS pricemult_history (
			   `id` INT(11) NOT NULL AUTO_INCREMENT,
			    `iblock_id` INT(11) NOT NULL,
			    `section_id` INT(11) NOT NULL,
			    `percent` FLOAT(5,2) NOT NULL,
			    `time` DATETIME NOT NULL,
			    PRIMARY KEY(`id`)
			)
			CHARACTER SET utf8 COLLATE utf8_unicode_ci
			ENGINE=InnoDB;
QUERY;
		$response = $connection->query($query_str);
	}

	/**
	* удаление таблицы истории запросов
	*/
	protected function removeHistoryTable() {
		//создаём таблицу в базе данных
		$connection = Application::getConnection();
		$query_str = <<<QUERY
			DROP TABLE pricemult_history;
QUERY;
		$response = $connection->query($query_str);
	}

	/**
	* размещение административных файлов
	*/
	protected function installAdminFiles() {
		$sourceFile = __DIR__.'/pricemult_handle.php';
		$destFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/pricemult_handle.php';
		copy($sourceFile, $destFile);
	}

	/**
	* удаление административных файлов
	*/
	protected function removeAdminFiles() {
		$destFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/pricemult_handle.php';
		unlink($destFile);
	}
}