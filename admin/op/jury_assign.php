<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("../inc/lib.php");

if ( (! empty($_SESSION['admin_id'])) && (! empty($_SESSION['admin_yearmonth'])) ) {

	// Collect Inputs
	$yearmonth = $_REQUEST['admin_yearmonth'];
	$sections = $_REQUEST['section'];
	$jurynumbers = $_REQUEST['jurynumber'];
	$user_ids = $_REQUEST['user_id'];

	// Truncate Assignments Table
	$sql = "TRUNCATE TABLE assignment";
	mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// INSERT Form Data
	foreach ($sections as $key => $value){
		if ($value != "") {		// Skip the last empty row
			$section = $value;
			$jurynumber = $jurynumbers[$key];
			$user_id = $user_ids[$key];
			$sql = " INSERT INTO assignment (yearmonth, section, user_id, jurynumber) ";
			$sql .= " VALUES ('$yearmonth', '$section', '$user_id', '$jurynumber') ";
			mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		}
	}

	$_SESSION['success_msg'] = "Jury assignments successfully completed !";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
else {
	$_SESSION['err_msg'] = "Invalid Parameters or Nothing to assign !";
	header("Location: ".$_SERVER['HTTP_REFERER']);
	printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
}
?>
