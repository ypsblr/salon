<?php
// session_start();
include("../inc/session.php");
include_once("../inc/connect.php");
include_once("ajax_lib.php");


if ( isset($_SESSION['jury_id']) && isset($_SESSION['jury_type']) &&
	 isset($_REQUEST['yearmonth']) && isset($_REQUEST['award_group']) && isset($_REQUEST['section']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$award_group = $_REQUEST['award_group'];
	$section = $_REQUEST['section'];

	// In case of "show_display" turn off the flag for all existing jury_sessions
	// The show_display will be turned on for the specific section & award_group in subsequent code
	// blocking the following code to allow multiple display sessions
	// if (isset($_REQUEST['show_display']) && $_REQUEST['show_display'] == '1') {
	// 	$sql  = "UPDATE jury_session ";
	// 	$sql .= "   SET show_display = '0' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// }

	// Get preset Jury Session Data
	$sql = "SELECT * FROM jury_session WHERE yearmonth = '$yearmonth' AND section = '$section' AND award_group = '$award_group' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$session_exists = (mysqli_num_rows($query) > 0);
	if ($session_exists) {
		$session = mysqli_fetch_array($query);
		$session_open = isset($_REQUEST['session_open']) ? $_REQUEST['session_open'] : $session['session_open'];
		$show_display = isset($_REQUEST['show_display']) ? $_REQUEST['show_display'] : $session['show_display'];
		$result_ready = isset($_REQUEST['result_ready']) ? $_REQUEST['result_ready'] : $session['result_ready'];

		// Update Jury Session
		$sql  = "UPDATE jury_session ";
		$sql .= "   SET session_open = '$session_open' ";
		$sql .= "     , show_display = '$show_display' ";
		$sql .= "     , result_ready = '$result_ready' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section = '$section' ";
		$sql .= "   AND award_group = '$award_group' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	else {
		$session_open = isset($_REQUEST['session_open']) ? $_REQUEST['session_open'] : '0';
		$show_display = isset($_REQUEST['show_display']) ? $_REQUEST['show_display'] : '0';
		$result_ready = isset($_REQUEST['result_ready']) ? $_REQUEST['result_ready'] : '0';

		// Create Jury Session
		$sql  = "INSERT INTO jury_session ( yearmonth, section, award_group, session_open, show_display, result_ready ) ";
		$sql .= " VALUES ( '$yearmonth', '$section', '$award_group', '$session_open', '$show_display', '$result_ready' ) ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}


	$retval = [];
	$retval['success'] = TRUE;
	$retval['msg'] = "";
	$retval['session_open'] = $session_open;
	$retval['show_display'] = $show_display;
	$retval['result_ready'] = $result_ready;
	echo json_encode($retval);
}
else
	die_with_error("Invalid Request to set Jury Session status", __FILE__, __LINE__);
?>
