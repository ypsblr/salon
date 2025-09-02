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

function return_error($errmsg, $phpfile, $phpline, $return_json = true) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Error '$errmsg' reported in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	if (isset($_REQUEST)) {
        file_put_contents($log_file, "Dump of REQUEST:" . chr(13) . chr(10), FILE_APPEND);
		file_put_contents($log_file, print_r($_REQUEST, true) . chr(13) . chr(10), FILE_APPEND);
    }
	if (isset($_SESSION)) {
        file_put_contents($log_file, "Dump of SESSION:" . chr(13) . chr(10), FILE_APPEND);
		file_put_contents($log_file, print_r($_SESSION, true) . chr(13) . chr(10), FILE_APPEND);
    }

	// Ajax Call with JSON return value
    if ($return_json) {
    	$resArray = array();
    	$resArray['success'] = FALSE;
    	$resArray['msg'] = $errmsg;
    	echo json_encode($resArray);
    }
    else {
        echo $errmsg;
    }

	die();
}

// LOG SQL Errors for debugging
// Usage: $query = mysqli_query($con, $sql) or sql_die($sql, mysqli_error($con), __FILE__, __LINE__);
function log_sql_error($sql, $errmsg, $phpfile, $phpline) {
    $log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/sql_errlog.txt";
	file_put_contents($log_file, date("Y-m-d H:i") . ": SQL operation failed with message '$errmsg' in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, "Failing SQL: " . $sql . chr(13) . chr(10), FILE_APPEND);
}

function sql_error($sql, $errmsg, $phpfile, $phpline, $return_json = true) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);

	// Ajax Call with JSON return value
    if ($return_json) {
    	$resArray = array();
    	$resArray['success'] = FALSE;
    	$resArray['msg'] = $errmsg;
    	echo json_encode($resArray);
    }
    else {
        echo "There is an error in fetching data !";
    }

	die();
}


// Sends mail out when run from server
// Otherwise stores in htm file
function send_mail($to, $subject, $message, $cc_to = "") {
	global $counterr;
	global $mail_failed;
	static $mail_suffix = 0;

	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html" . "\r\n";
	$headers .= "From: <salon@ypsbengaluru.in>" . "\r\n";

	if (preg_match("/localhost/i", $_SERVER['SERVER_NAME'])) {
		if (! is_dir("mails"))
			mkdir("mails");
		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $message);
		return true;
	}
    elseif (preg_match("/salontest/i", $_SERVER['SERVER_NAME'])) {
        // Send text email to the member
        if (isset($_SESSION['admin_email']))
            return mail($_SESSION['admin_email'], "Test email : " . $subject, $message, $headers);
        else {
            if (! is_dir("mails"))
    			mkdir("mails");
    		file_put_contents("mails/mail_" . date("Y_m_d_H_i_s") . sprintf("_%04d", ++ $mail_suffix) . ".htm", $message);
    		return true;
        }
    }
	else {
        $headers .= "Cc: <salon@ypsbengaluru.in>" . ($cc_to == "" ? "" : "," . $cc_to) . "\r\n";
		return mail($to, $subject, $message, $headers);
	}
}

// Return date in string format at a specified timezone
function strtotime_tz($time_str, $tz) {
	$cur_tz = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret_time = strtotime($time_str);
	date_default_timezone_set($cur_tz);

	return $ret_time;
}

// Return formated date in different timezone
function date_tz($time_str, $tz) {
	$cur_tz = date_default_timezone_get();
	date_default_timezone_set($tz);
	$ret_time = date("Y-m-d", strtotime($time_str));
	date_default_timezone_set($cur_tz);

	return $ret_time;
}



function http_method() {
	if (isset($_SERVER['HTTPS']) && (! empty($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] != "off")
		return "https://";
	else
		return "http://";
}

//
// Random Password Generator
//
function generate_password($length = 8, $complex=3) {
	$min = "abcdefghijklmnopqrstuvwxyz";
	$num = "0123456789";
	$maj = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	$symb = "!@#$%^&*()_-=+;:,.?";
	$chars = $min;
	if ($complex >= 2) { $chars .= $num; }
	if ($complex >= 3) { $chars .= $maj; }
	if ($complex >= 4) { $chars .= $symb; }
	do {
		$password = substr( str_shuffle( $chars ), 0, $length );
	} while (strspn($password, $min) > 0 && ($complex >= 2 ? strspn($password, $num) > 0 : true) && ($complex >= 3 ? strspn($password, $maj) > 0 : true) && ($complex >= 4 ? strspn($password, $symb) > 0 : true));
	return $password;
}

function set_session_variables($entry_row) {

}

function delete_session_variables() {

}

function set_cookies($row) {

}

function delete_cookies() {

}



?>
