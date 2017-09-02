<?
$bitrixFile = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/pricemult/handle.php';
$localFile = $_SERVER['DOCUMENT_ROOT'].'/local/modules/pricemult/handle.php';

if(file_exists( $localFile )) {
	require( $localFile );
} elseif (file_exists( $bitrixFile )) {
	require( $bitrixFile );
}