<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function expand_array(&$target, $rows = 3, $val = "") {
	while (sizeof($target) < $rows)
		array_push($target, $val);
}

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['dignitory_index'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$index = $_REQUEST['dignitory_index'];

	// Check if there are pictures under the section
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Exhibition for $yearmonth is yet to be set up.", __FILE__, __LINE__);
	$exhibition = mysqli_fetch_array($query);
	$dignitory_roles = explode("|", $exhibition['dignitory_roles']);
	expand_array($dignitory_roles);
	$dignitory_names = explode("|", $exhibition['dignitory_names']);
	expand_array($dignitory_names);
	$dignitory_positions = explode("|", $exhibition['dignitory_positions']);
	expand_array($dignitory_positions);
	$dignitory_avatars = explode("|", $exhibition['dignitory_avatars']);
	expand_array($dignitory_avatars);
	$dignitory_blobs = explode("|", $exhibition['dignitory_profile_blobs']);
	expand_array($dignitory_blobs);

	// Set respective index to blanks
	if ($index < sizeof($dignitory_names)) {
		$dignitory_roles[$index] = "";
		$dignitory_names[$index] = "";
		$dignitory_positions[$index] = "";
		$dignitory_avatars[$index] = "";
		$dignitory_blobs[$index] = "";
	}
	$dignitory_roles_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_roles));
	$dignitory_names_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_names));
	$dignitory_positions_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_positions));
	$dignitory_avatars_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_avatars));
	$dignitory_blobs_txt = mysqli_real_escape_string($DBCON, implode("|", $dignitory_blobs));

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update the Exhibition
	$sql  = "UPDATE exhibition ";
	$sql .= "   SET dignitory_roles = '$dignitory_roles_txt' ";
	$sql .= "     , dignitory_names = '$dignitory_names_txt' ";
	$sql .= "     , dignitory_positions = '$dignitory_positions_txt' ";
	$sql .= "     , dignitory_avatars = '$dignitory_avatars_txt' ";
	$sql .= "     , dignitory_profile_blobs = '$dignitory_blobs_txt' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
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
