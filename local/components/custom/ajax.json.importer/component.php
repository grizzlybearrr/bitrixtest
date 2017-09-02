<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();


if(isset($_POST["step"])) {
	$APPLICATION->RestartBuffer();
	$step=filter_var($_POST["step"], FILTER_SANITIZE_NUMBER_INT);

	$upl = new JsonUploader();

	header('Content-Type: application/json');
	$data = array();


	//шаг 1. загрузка файла
	if($step==1){
		$res = $upl->saveFile('datafile');

		$data["message"] = $res["message"];

		if(!$res["success"]) {
			http_response_code(400);
			$upl->cleanTemporaryData();
		} else {
			$data["next_step"] = 2; //указание на следующий шаг
			$data["next_message"] = "Валидация файла";
			$data["status"] = "success";

			//сохранение состояния до следующего шага
			$upl->sessionWrite();
		}
	}

	//шаг 2. валидация структуры
	if($step==2){
		$upl->sessionRead();
		$res = $upl->fileValidate();

		if(!$res) {
			http_response_code(400);
			$data["message"] = "Ошибка валидации файла";
			$upl->cleanTemporaryData();
		} else {
			$data["message"] = "Валидация файла завершена";
			$data["next_step"] = 3; //указание на следующий шаг
			$data["next_message"] = "Чтение структуры";
			$data["status"] = "success";

			//сохранение состояния до следующего шага
			$upl->sessionWrite();
		}
	}

	//шаг 3. Чтение структуры
	if($step==3){
		$upl->sessionRead();

		$res = $upl->readStructure();

		$data["message"] = $res["message"];

		if(!$res["success"]) {
			http_response_code(400);
			$upl->cleanTemporaryData();
		} else {
			$data["next_step"] = 4; //указание на следующий шаг
			$data["next_message"] = "Создание инфоблоков";
			$data["status"] = "success";

			//сохранение состояния до следующего шага
			$upl->sessionWrite();
		}
	}

	//шаг 4. создание инфоблоков
	if($step==4){
		$upl->sessionRead();

		$res = $upl->buildStructure();

		$data["message"] = $res["message"];
		if(!$res["success"]) {
			http_response_code(400);
			$upl->cleanTemporaryData();
		} else {
			$data["next_step"] = 5; //указание на следующий шаг
			$data["next_message"] = "Импорт данных";
			$data["status"] = "success";

			//сохранение состояния до следующего шага
			$upl->sessionWrite();
		}
	}

	//шаг 5. чтение данных
	if($step==5){
		$upl->sessionRead();
		

		$res = $upl->readData();
		$data["message"] = $res["message"];

		if(!$res["success"]) {
			http_response_code(400);
			$upl->cleanTemporaryData();
		} else {
			if($res["status"] == "continue") {
				$data["next_step"] = 5; //указание на повтор текущего шага
				$data["status"] = "continue";
			}

			if($res["status"] == "success") {
				$data["next_step"] = 6; //указание на следующий шаг
				$data["next_message"] = "Завершение импорта";
				$data["status"] = "success";
			}

			//сохранение состояния до следующего шага
			$upl->sessionWrite();
		}
	}

	//шаг 6. Завершение импорта
	if($step==6){
		$upl->sessionRead();

		$data["message"] = "Импорт завершён";
		$data["next_step"] = 6; //указание на следующий шаг
		$data["next_message"] = "";
		$data["status"] = "success";
	}


	echo json_encode($data);
	die();
}


$this->IncludeComponentTemplate();