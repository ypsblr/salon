<?php
// header('Content-Type: text/csv');
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=yps_member.csv');
include_once('simple_html_dom.php');


function encode_string_array ($stringArray) {
    $s = strtr(base64_encode(addslashes(gzcompress(serialize($stringArray),2))), '+/=', '_,');

    return $s;
}

function decode_string_array ($stringArray) {
    $s = unserialize(gzuncompress(stripslashes(base64_decode(strtr($stringArray, '_,', '+/=')))));
    return $s;
}

function get_img_src($html) {
	$doc = str_get_html($html);
	debug_dump("html_doc", $doc, __FILE__, __LINE__);
	$img_src = "";
	foreach($doc->find('img') as $img_element){
		debug_dump("img_element", $img_element, __FILE__, __LINE__);
		$img_src = $img_element->src;
	}

	return $img_src;
}


function log_error($errmsg, $phpfile, $phpline) {
    $log_file = "errlog.txt";
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

	die();
}

// Write a variable dump to file
function debug_dump($name, $value, $phpfile, $phpline) {
    $log_file = "debug.txt";
	file_put_contents($log_file, date("Y-m-d H:i") .": Dump of '$name' requested in line $phpline of '$phpfile'" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, print_r($value, true) . chr(13) . chr(10), FILE_APPEND);
}

// LOG SQL Errors for debugging
// Usage: $query = mysqli_query($con, $sql) or sql_die($sql, mysqli_error($con), __FILE__, __LINE__);
function log_sql_error($sql, $errmsg, $phpfile, $phpline) {
    $log_file = "sql_errlog.txt";
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

session_start();

/*** LOADING WORDPRESS LIBRARIES ***/
define('WP_USE_THEMES', false);
require_once("../wp-load.php");

if (isset($_REQUEST['magic']) && decode_string_array($_REQUEST['magic']) == "ypsmagic1971onwards") {

	$args = array("fields" => array("user_login","user_pass","user_email"));
	$userlist = get_users($args);
	// var_dump($userlist);

	$data = (object) array();
	if (is_null($userlist) || sizeof($userlist) == 0) {
		die("YPS Member List returned empty !");
		echo json_encode($data);
	}
	else {
		$output = fopen('php://output', 'w');
		$return_list = array();
		foreach ($userlist as $user) {
			$swpm_user = SwpmMemberUtils::get_user_by_user_name($user->user_login);
			$yps_avatar = get_avatar($user->ID);
			if ($yps_avatar != "") {
				// Get avatar file name from the html
				$yps_avatar_url = get_img_src($yps_avatar);
				// debug_dump("yps_avatar_url", $yps_avatar_url, __FILE__, __LINE__);
				}
			}
			if($swpm_user->account_state == "active") {
				fputcsv($output, array($user->user_login, $swpm_user->first_name, $swpm_user->last_name, $user->user_pass, $user->user_email, $swpm_user->phone,
								   $swpm_user->gender, $swpm->account_state, $yps_avatar_url, $swpm_user->member_since, $swpm_user->subscription_starts,
							   		$swpm_user->address_street, $swpm_user->address_city, $swpm_user->address_state, $swpm_user->address_zipcode));
			}
		}
		fclose($output);
	}
}
else {
	$data = (object) array();
	die("Invalid/Unauthorized Request");
}


?>
