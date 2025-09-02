<?php
// Update Member Data based on Reconciliation
// session_start();
include("../inc/session.php");

include("../inc/connect.php");
include("ajax_lib.php");


// if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) && preg_match("/localhost/i", $_SERVER['SERVER_NAME']) &&
if( isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) &&
	isset($_REQUEST['profile_id']) && isset($_REQUEST['email']) && isset($_REQUEST['yps_login_id']) ) {

	$profile_id = $_REQUEST['profile_id'];
	$email = $_REQUEST['email'];
	$yps_login_id = $_REQUEST['yps_login_id'];

	// Start Transaction
	$sql = "START TRANSACTION";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	if ($yps_login_id == "NONE") {
		// No more a YPS Member. Set a password and make yps_login_id blank
		$sql  = "UPDATE profile ";
		$sql .= "   SET yps_login_id = '' ";
		$sql .= "     , password = 'YPS2GNRL' ";
		$sql .= " WHERE profile_id = '$profile_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Update Failed", __FILE__, __LINE__);
	}
	else {
		// Update with new login_id and email. Clear any password field values
		$sql  = "UPDATE profile ";
		$sql .= "   SET email = '$email' ";
		$sql .= "     , yps_login_id = '$yps_login_id' ";
		$sql .= "     , password = '' ";
		$sql .= " WHERE profile_id = '$profile_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Update Failed", __FILE__, __LINE__);
	}

	// COMMIT
	$sql = "COMMIT";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Return success
	$resArray = array();
	$resArray['success'] = TRUE;
	$resArray['msg'] = "Contest Data has been archived. Take a backup.";
	echo json_encode($resArray);

}
else
	return_error( "Invalid Parameters !", __FILE__, __LINE__);
?>
