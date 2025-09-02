<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

if ( (! empty($_SESSION['USER_ID'])) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) &&
 		isset($_REQUEST['pic_id']) && isset($_REQUEST['new_title']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	$new_title = mysqli_real_escape_string($DBCON, $_REQUEST['new_title']);

	$sql  = "UPDATE pic SET title = '$new_title' ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = array();
	$resArray['success'] = true;
	$resArray['msg'] = '';
	echo json_encode($resArray);
}
else {
	handle_error("Invalid Update Request", __FILE__, __LINE__);
}
?>
