<?php
// session_start();
include("../inc/session.php");
include_once("../inc/connect.php");
include_once("ajax_lib.php");


if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) &&
	 isset($_REQUEST['yearmonth']) && isset($_REQUEST['judging_in_progress']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$judging_in_progress = $_REQUEST['judging_in_progress'];

	// Update the Status
	$sql = "UPDATE contest SET judging_in_progress = '$judging_in_progress' WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// While turning on judging_in_progress turn off the status for all other contests to be sure
	if ($judging_in_progress == '1') {
		$sql = "UPDATE contest SET judging_in_progress = '0' WHERE yearmonth != '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$retval = [];
	$retval['success'] = TRUE;
	$retval['msg'] = "";
	$retval['judging_in_progress'] = $judging_in_progress;
	echo json_encode($retval);
}
else
	die_with_error("Invalid Request to set Jury Session status", __FILE__, __LINE__);
?>
