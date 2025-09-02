<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");


// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) ) {

	$section = $_REQUEST['section'];
	$yearmonth = $_REQUEST['yearmonth'];
	$new_jury_list = isset($_REQUEST['selected_jury_list']) ? $_REQUEST['selected_jury_list'] : [];

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Delete existing assignments
	$sql  = "DELETE FROM assignment ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND section = '$section' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert new assignments
	for ($i = 0; $i < sizeof($new_jury_list); ++ $i) {
		$jurynumber = $i + 1;
		$user_id = $new_jury_list[$i];
		$sql  = "INSERT INTO assignment (yearmonth, section, user_id, jurynumber, status) ";
		$sql .= "VALUES('$yearmonth', '$section', '$user_id', '$jurynumber', 'OPEN') ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
