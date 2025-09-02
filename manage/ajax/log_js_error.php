<?php
// include_once("op_session.php");
date_default_timezone_set("Asia/Kolkata");

function json_or_string($string) {
	$data = json_decode($string);
	if (json_last_error() == JSON_ERROR_NONE)
		return $data;
	else
		return $string;
}

// A function to return an error message and stop
if (isset($_REQUEST['rurobot']) && $_REQUEST['rurobot'] == 'IamHuman' && isset($_REQUEST['source']) &&
	isset($_REQUEST['status']) && isset($_REQUEST['error']) && isset($_REQUEST['text']) && isset($_REQUEST['xml']) ) {
	$email = isset($_REQUEST['context']) ? $_REQUEST['context'] : "";
	$source = $_REQUEST['source'];
	$status = $_REQUEST['status'];
	$error = json_or_string($_REQUEST['error']); 
	$responseText = $_REQUEST['text'];
	$responseXML = $_REQUEST['xml'];
	
	file_put_contents("js_errlog.txt", "====< START >==================================" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents("js_errlog.txt", date("Y-m-d H:i") .": Javascript Errors reported by '$source'. Context : '$email' " . chr(13) . chr(10), FILE_APPEND);
	file_put_contents("js_errlog.txt", "Server Error : status/name = '$status'; error/value = " . print_r($error, true) . chr(13) . chr(10), FILE_APPEND);
	file_put_contents("js_errlog.txt", "Response Text : " . print_r($responseText, true) . chr(13) . chr(10), FILE_APPEND);
	file_put_contents("js_errlog.txt", "Response XML : " . print_r($responseXML, true) . chr(13) . chr(10), FILE_APPEND);

}

// Quietly exit

?>