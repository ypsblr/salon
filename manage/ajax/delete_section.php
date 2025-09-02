<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['section'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];

	// Check if there are pictures under the section
	$sql  = "SELECT COUNT(*) AS num_pics FROM pic ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['num_pics'] > 0)
		return_error("Cannot remove the section. Pictures have been uploaded under the section.", __FILE__, __LINE__);

	// Check if there are awards under the section
	$sql  = "SELECT COUNT(*) AS num_awards FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['num_awards'] > 0)
		return_error("Cannot remove the section. Awards have been created under the section.", __FILE__, __LINE__);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get Contest Information
	$sql  = "DELETE FROM section  ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND section = '$section' ";
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
