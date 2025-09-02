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

function sql_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	$_SESSION['err_msg'] = "SQL Operation failed. Please report to YPS to check using Contact Us page.";
    if (basename($phpfile) == basename($_SERVER['HTTP_REFERER'])) {
        header("Location: /jurypanel/index.php");
    	print("<script>location.href='/jurypanel/index.php'</script>");
    }
    else {
    	header("Location: ".$_SERVER['HTTP_REFERER']);
    	print("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
    }
	die($errmsg);
}

// Log General Errors and Go Home
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

function die_with_error($errmsg, $phpfile, $phpline, $logout = false) {
    $_SESSION['err_msg'] = $errmsg;
	log_error($errmsg, $phpfile, $phpline);
	if ($logout || basename($phpfile) == basename($_SERVER['HTTP_REFERER'])) {
		header("Location: /jurypanel/index.php");
		print("<script>location.href='/jurypanel/index.php'</script>");
	}
	else {
		header("Location: ".$_SERVER['HTTP_REFERER']);
		print("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
	}
	die($errmsg);
}


function http_method() {
	if (isset($_SERVER['HTTPS']) && (! empty($_SERVER['HTTPS'])) && $_SERVER['HTTPS'] != "off")
		return "https://";
	else
		return "http://";
}


?>
