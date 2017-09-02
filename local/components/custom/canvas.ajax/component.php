<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

$APPLICATION->RestartBuffer();
$controller = new CanvasControl();

$id = filter_var( $_REQUEST['id'], FILTER_SANITIZE_NUMBER_INT );
$action = filter_var( $_REQUEST['action'], FILTER_SANITIZE_STRING ); //общее действие
$stage = filter_var( $_REQUEST['stage'], FILTER_SANITIZE_STRING ); //шаг действия
$password = filter_var( $_REQUEST['password'], FILTER_SANITIZE_URL );
$folder = $arParams["LIST_URL"];

$result = array(
	"action" => $action,
);

//открытие нового холста
if($action=="add"){
	$result["stage"] = "redirect";
	$result["url"] = $folder."add/";
}

//сохранение нового холста
if($action=="save"){
	if(!$stage) {
		$result["stage"] = "password";
	}

	if($stage == "password") {
		$postImg = $_REQUEST['image'];

		$result = $controller->save($id, $password, $postImg);

		if($result["status"] == "ok") {
			$result["stage"] = "redirect";
			$result["url"] = $folder.$result["id"]."/";
		} else {
			http_response_code(400);
			$result["stage"] = "error";
		}
	}
}

//обновление холста при редактировании
if($action=="update") {
	if(!$stage) {
		$attempt = $controller->check($id);
		if($attempt){
			$result["stage"] = "save";
		} else {
			$result["stage"] = "password";
		}
	}

	if($stage == "password") {
		$attempt = $controller->auth($id, $password);

		if($attempt){
			$result["stage"] = "save";
		} else {
			$result["stage"] = "password";
		}
	}

	if($stage == "save") {
		$attempt = $controller->check($id);
		if($attempt){
			$postImg = $_REQUEST['image'];

			$result = $controller->save($id, $password, $postImg);

			if($result["status"] == "ok") {
				$result["stage"] = "complete";
			} else {
				http_response_code(400);
				$result["stage"] = "error";
			}
		} else {
			$result["stage"] = "password";
		}


		
	}
}


//открытие холста на редактирование
if($action=="edit") {
	if(!$stage) {
		$attempt = $controller->check($id);
		if($attempt){
			$result["stage"] = "redirect";
			$result["url"] = $folder.$id."/";
		} else {
			$result["stage"] = "password";
		}
	}

	if($stage == "password") {
		$attempt = $controller->auth($id, $password);

		if($attempt){
			$result["stage"] = "redirect";
			$result["url"] = $folder.$id."/";
		} else {
			$result["stage"] = "password";
		}
	}
}


echo json_encode($result);
die();

