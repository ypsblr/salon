<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['operation']) && isset($_REQUEST['profiles']) ) {

	$mail_operation = $_REQUEST['operation'];
	$profile_list = explode(",", $_REQUEST['profiles']);
	$operation_date = date("Y-m-d");

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	foreach ($profile_list as $profile_id) {
		if ($mail_operation == "RESUME") {
			// Delete entry from mail_skip - ignore if there are no rows
			$sql = "DELETE FROM mail_skip WHERE profile_id = '$profile_id' ";
			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
		else {
			// Check if the profile is in mail_skip
			$sql = "SELECT * FROM mail_skip WHERE profile_id = '$profile_id' ";
			$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) == 0) {
				// INSERT
				$sql  = "INSERT INTO mail_skip (profile_id, reason, operation_date) ";
				$sql .= "VALUES ('$profile_id', '$mail_operation', '$operation_date')";
				mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if (mysqli_affected_rows($DBCON) != 1)
					return_error("Unable to " . $mail_operation . " the profile " . $profile_id, __FILE__, __LINE__, true);
			}
			else {
				// UPDATE reason
				$sql  = "UPDATE mail_skip ";
				$sql .= "   SET reason = '$mail_operation' ";
				$sql .= "     , operation_date = '$operation_date' ";
				$sql .= " WHERE profile_id = '$profile_id' ";
				mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
				if (mysqli_affected_rows($DBCON) != 1)
					return_error("Unable to " . $mail_operation . " the profile " . $profile_id, __FILE__, __LINE__, true);
			}
		}
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
