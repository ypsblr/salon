<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

// function notify_error($context, $errmsg, $file, $line, $updated = false) {
// 	debug_dump($context, $errmsg, $file, $line);
// 	echo json_encode(array("status" => "ERROR", "context" => $context, "errmsg" => $errmsg, "file" => $file, "line" => $line, "updated" => $updated));
// 	die();
// }

// MAIN
if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) &&
 	isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) && isset($_REQUEST['pic_list']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];
	$pic_list = $_REQUEST['pic_list'];

	// Get contest details
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$dimension_list = [];
	foreach ($pic_list as $pic_ref) {
		list($profile_id, $pic_id, $picfile) = explode("|", $pic_ref);
		list($width, $height) = getimagesize("../../salons/$yearmonth/upload/$section" . ($contest_archived ? "/ar/" : "/") . $picfile);
		$dimension_list[] = array("profile_id" => $profile_id, "pic_id" => $pic_id, "width" => $width, "height" => $height);
	}

	echo json_encode(array("status" => "OK", "dimension_list" => $dimension_list));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
