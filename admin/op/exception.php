<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

// debug_dump("POST", $_POST, __FILE__, __LINE__);

if(isset($_SESSION['admin_yearmonth']) && isset($_SESSION['admin_id']) ) {
	if (isset($_REQUEST['add'])) {
		$profile_id = $_REQUEST['add'];
		$match = $_REQUEST['match'];

		$approval_date = date("Y-m-d");
		$approved_by = $_SESSION['admin_id'];

		// Create blacklist_exception record
		$sql  = "INSERT INTO blacklist_exception ";
		$sql .= "SELECT email, profile_name, '$approval_date', '$approved_by' ";
		$sql .= "  FROM profile WHERE profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) < 1) {
			$_SESSION['err_msg']="Unable to create an exception.";
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}

		// Update profile with blacklist info
		$sql = "UPDATE profile SET blacklist_match = '$match', blacklist_exception = '1' WHERE profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) < 1) {
			$_SESSION['err_msg']="Unable to set exception in profile.";
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}
	}

	if (isset($_REQUEST['del'])) {
		$profile_id = $_REQUEST['del'];

		// Create blacklist_exception record
		$sql  = "DELETE FROM blacklist_exception ";
		$sql .= " WHERE email = (SELECT email FROM profile WHERE profile_id = '$profile_id') ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) < 1) {
			$_SESSION['err_msg']="Unable to delete the exception.";
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}

		// Update profile with blacklist info
		$sql = "UPDATE profile SET blacklist_exception = '0' WHERE profile_id = '$profile_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) < 1) {
			$_SESSION['err_msg']="Unable to reset exception in profile.";
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}
	}


	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Request";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>
