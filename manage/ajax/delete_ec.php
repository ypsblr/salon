<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['entrant_category'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$entrant_category = $_REQUEST['entrant_category'];

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get Contest Information
	$sql  = "DELETE FROM entrant_category  ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND entrant_category = '$entrant_category' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

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
