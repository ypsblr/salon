<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['discount_code']) && isset($_REQUEST['fee_code']) &&
	isset($_REQUEST['discount_group']) && isset($_REQUEST['participation_code']) && isset($_REQUEST['currency']) && isset($_REQUEST['group_code']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$discount_code = $_REQUEST['discount_code'];
	$fee_code = $_REQUEST['fee_code'];
	$discount_group = $_REQUEST['discount_group'];
	$participation_code = $_REQUEST['participation_code'];
	$currency = $_REQUEST['currency'];
	$group_code = $_REQUEST['group_code'];

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get Contest Information
	$sql  = "DELETE FROM discount  ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND discount_code = '$discount_code' ";
	$sql .= "   AND fee_code = '$fee_code' ";
	$sql .= "   AND discount_group = '$discount_group' ";
	$sql .= "   AND participation_code = '$participation_code' ";
	$sql .= "   AND currency = '$currency' ";
	$sql .= "   AND group_code = '$group_code' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != 1)
		return_error("Error in deleting the discount", __FILE__, __LINE__);

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
