<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

if (isset($_SESSION['USER_ID']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	$sql = "DELETE FROM pic WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update the Number of Uploads in entry table
	$sql  = "UPDATE entry ";
	$sql .= "   SET uploads = (SELECT COUNT(*) FROM pic ";
	$sql .= "					WHERE pic.yearmonth = entry.yearmonth ";
	$sql .= "					  AND pic.profile_id = entry.profile_id) ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id'";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = array();
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
}
else
	handle_error("Invalid Request");
?>
