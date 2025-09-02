<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("./ajax_lib.php");

// A function to return an error message and stop
function return_error($errmsg) {
	$_SESSION['err_msg'] = $errmsg;
	$resArray = array();
	$resArray['status'] = "ERR";
	$resArray['errmsg'] = $errmsg;
	echo json_encode($resArray);
	die;
}

// Usage: $query = mysql_query($sql) or sql_json_error($sql, mysql_error(), __FILE__, __LINE__);
function sql_json_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	return_error("Server Error");
	die;
}

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) &&
   isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) && isset($_REQUEST['award_group']) &&
   isset($_REQUEST['categories']) && isset($_REQUEST['filter_criteria']) && isset($_REQUEST['bucket']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];
	$award_group = $_REQUEST['award_group'];
	$entrant_categories = $_REQUEST['categories'];
	$filter_criteria = $_REQUEST['filter_criteria'];
	$bucket = $_REQUEST['bucket'];

	$sql  = "UPDATE jury_session ";
	$sql .= "   SET command_index = command_index + 1 ";
	$sql .= "     , entrant_categories = '$entrant_categories' ";
	$sql .= "     , filter_criteria = '$filter_criteria' ";
	$sql .= "     , bucket = '$bucket' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND section = '$section' ";
	$sql .= "   AND award_group = '$award_group' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$retval = array('status' => 'OK', 'errmsg' => "");
	echo json_encode($retval);
}
else {
	return_error("Invalid Request");
}
?>
