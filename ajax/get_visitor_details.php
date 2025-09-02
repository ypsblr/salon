<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

function die_with_error($errmsg) {
	$resArray = array();
	$resArray['success'] = false;
	$resArray['msg'] = $errmsg;
	echo json_encode($resArray);
	die();
}

if (isset($_REQUEST['yearmonth']) && (isset($_REQUEST['find_email']) || isset($_REQUEST['visitor_id']) || isset($_SESSION['VISITOR_ID'])) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$find_email = mysqli_real_escape_string($DBCON, $_REQUEST['find_email']);

	if (isset($_REQUEST['find_email']))
		$sql = "SELECT * FROM visitor_book WHERE yearmonth = '$yearmonth' AND visitor_email = '$find_email' ";
	else if (isset($_REQUEST['visitor_id']) || isset($_SESSION['VISITOR_ID'])) {
		$visitor_id = isset($_REQUEST['visitor_id']) ? $_REQUEST['visitor_id'] : (isset($_SESSION['VISITOR_ID']) ? $_SESSION['VISITOR_ID'] : 0);
		$sql = "SELECT * FROM visitor_book WHERE yearmonth = '$yearmonth' AND visitor_id = '$visitor_id' ";
	}
	else
		die_with_error("Invalid inputs");

	$query = mysqli_query($DBCON, $sql) or die_with_error_error("Data operation failed.");
	if (mysqli_num_rows($query) > 0) {
		$visitor = mysqli_fetch_array($query, MYSQLI_ASSOC);
		$resArray = array();
		$resArray['success'] = true;
		$resArray['msg'] = '';
		$resArray['visitor'] = $visitor;
		echo json_encode($resArray);
	}
	else {
		die_with_error("Visitor not found!", __FILE__, __LINE__);
	}
}
else {
	die_with_error("Invalid Update Request", __FILE__, __LINE__);
}
?>
