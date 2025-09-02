<?php
// session_start();
include("../inc/session.php");
include_once("ajax_lib.php");

function return_session_status($status, $errmsg = "") {
	$retval = [];
	$retval['success'] = ($errmsg == "");
	$retval['msg'] = $errmsg;
	$retval['status'] = $status;
	echo json_encode($retval);
	die;
}

if ( isset($_REQUEST['jury_id']) && isset($_REQUEST['jury_type']) && isset($_REQUEST['jury_yearmonth']) && isset($_REQUEST['session']) ) {

	$_SESSION['jury_id'] = $_REQUEST['jury_id'];
	$_SESSION['jury_type'] = $_REQUEST['jury_type'];
	$_SESSION['jury_yearmonth'] = $_REQUEST['jury_yearmonth'];
	$session = json_decode($_REQUEST['session'], true);

	// Populate other session variables session only with logged-out session variables
	foreach($session as $session_key => $session_value) {
		if (! in_array($session_key, array('jury_id', 'jury_type', 'jury_yearmonth')))
			$_SESSION[$session_key] = $session_value;
	}
	return_session_status("OK");
}
else
	return_session_status("ERROR", "Error keeping session alive");
?>
