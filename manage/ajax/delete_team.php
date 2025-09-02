<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['member_id']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$member_id = $_REQUEST['member_id'];

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get Contest Information
	$sql  = "DELETE FROM team ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND member_id = '$member_id' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != 1)
		return_error("Error in deleting the fee", __FILE__, __LINE__);

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>
