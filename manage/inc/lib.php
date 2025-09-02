<?php

function toconsole($data) {
    $output = $data;
    if (is_array($output))
        $output = implode(',', $output);

    echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
}

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

// Log error and exit
function log_error($errmsg, $phpfile, $phpline) {
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
}

function return_error($errmsg, $phpfile, $phpline) {

    $_SESSION['err_msg'] = $errmsg;

    log_error($errmsg, $phpfile, $phpline);

	header("Location: admin_home.php");
	printf("<script>location.href='admin_home.php'</script>");
	// header("Location: " . $_SERVER['HTTP_REFERER']);
	// printf("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
	die();
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
	header("Location: ".$_SERVER['HTTP_REFERER']);
	print("<script>location.href='" . $_SERVER['HTTP_REFERER'] . "'</script>");
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
        if ($cc_to != "nocc")
    	   $headers .= "Cc: <salon@ypsbengaluru.in>" . ($cc_to == "" ? "" : "," . $cc_to) . "\r\n";
		return mail($to, $subject, $message, $headers);
	}
}

// Check Permissions
function has_permission($for_list, $permission_list = ["viewer", "reviewer", "receiver", "treasurer", "secretary", "chairman", "admin", "manager"] ) {

	foreach($for_list as $for)
		if (in_array($for, $permission_list))
			return true;

	return false;
}

// Check access for section
function can_review($section, $section_list, $permission_list) {
    if (in_array("admin", $permission_list))
        return true;
    elseif (in_array($section, $section_list))
        return true;
    else
        return false;
}

// Return Exif Data stored in pic table as string
function exif_str($exif_json) {
    if ($exif_json == "")
		return "NO EXIF";

    try {
        $exif = json_decode($exif_json, true);
        $exif_strings = [];
        if (! empty($exif["camera"]))
            $exif_strings[] = $exif["camera"];
        if (! empty($exif["iso"]))
            $exif_strings[] = "ISO " . $exif["iso"];
        if (! empty($exif["program"]))
            $exif_strings[] = $exif["program"];
        if (! empty($exif["aperture"]))
            $exif_strings[] = $exif["aperture"];
        if (! empty($exif["speed"]))
            $exif_strings[] = $exif["speed"];

        if (sizeof($exif_strings) > 0)
            return implode(", ", $exif_strings);
        else
            return "";
    }
    catch (Exception $e) {
        // Not a proper exif
        return "";
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

function pdate($date_str) {
	return date("F j,Y", strtotime($date_str));
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


//
// Function to format name
// Split into words and if the word has more than 3 letters or has a vowel after the first letter then capitalize
//
function format_name($name) {
    $parts = preg_split("/\s+/", $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    for ($i = 0; $i < sizeof($parts); ++ $i) {
        if (strlen($parts[$i]) == 1 || strlen($parts[$i]) > 3)
            $parts[$i] = ucfirst(strtolower($parts[$i]));
        else {
            // If the name comtains vowel after the first letter, it could be a proper name
            if (preg_match("/[aeiouy]/i", $parts[$i]))
                $parts[$i] = ucfirst(strtolower($parts[$i]));
            else
                $parts[$i] = strtoupper($parts[$i]);
        }
    }
    return implode(" ", $parts);
}

function format_title($title) {
    return ucwords(strtolower($title));
}

function format_place($place) {
    return ucwords(strtolower($place));
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
