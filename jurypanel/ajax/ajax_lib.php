<?php

function encode_string_array ($stringArray) {
    $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),2))), '+/=', '_,');

    return $s;
}

function decode_string_array ($stringArray) {
    $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '_,', '+/=')))));
    return $s;
}

function orderSuffix($i) {
	$str = "$i";
	$t = $i > 9 ? substr($str,-2,1) : 0;
	$u = substr($str,-1);
	if ($t==1)
		return $str . 'th';
	else {
		switch ($u) {
			case 1: return $str . '<sup>st</sup>';
			case 2: return $str . '<sup>nd</sup>';
			case 3: return $str . '<sup>rd</sup>';
			default: return $str . '<sup>th</sup>';
	   }
	}
}


// Write a variable dump to file
function debug_dump($name, $value, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/debug.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Dump of '$name' requested in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, print_r($value, true) . chr(13) . chr(10), FILE_APPEND);
}

// LOG SQL Errors for debugging
// Usage: $query = mysqli_query($con, $sql) or sql_die($sql, mysqli_error($con), __FILE__, __LINE__);
function log_sql_error($sql, $errmsg, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
}

function log_error($errmsg, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
    file_put_contents($log_file, date("Y-m-d H:i") . ": Operation failed in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Error Message: '$errmsg'" . chr(13) . chr(10), FILE_APPEND);
    if (!empty($_REQUEST)) {
        file_put_contents($log_file, "Dump of REQUEST:" . chr(13) . chr(10), FILE_APPEND);
        file_put_contents($log_file, print_r($_REQUEST, true) . chr(13) . chr(10), FILE_APPEND);
    }
	if (!empty($_SESSION)) {
        file_put_contents($log_file, "Dump of SESSION:" . chr(13) . chr(10), FILE_APPEND);
        file_put_contents($log_file, print_r($_SESSION, true) . chr(13) . chr(10), FILE_APPEND);
    }
}

function die_with_error($errmsg, $phpfile, $phpline) {
	log_error($errmsg, $phpfile, $phpline);

	// Ajax Call with JSON return value
	$resArray = array();
	$resArray['success'] = FALSE;
	$resArray['msg'] = $errmsg;
	echo json_encode($resArray);

	die();
}

function sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	die_with_error("Data Operation failed. Please report to YPS.", $phpfile, $phpline);
}

function http_method() {
	if (isset($_SERVER['HTTPS']) && (! empty($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] != "off")
		return "https://";
	else
		return "http://";
}


?>
