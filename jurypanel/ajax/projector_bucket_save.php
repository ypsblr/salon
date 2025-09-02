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
   isset($_REQUEST['bucket1_list']) && isset($_REQUEST['bucket2_list']) && isset($_REQUEST['bucket3_list']) &&
   isset($_REQUEST['bucket4_list']) && isset($_REQUEST['bucket5_list']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];
	$award_group = $_REQUEST['award_group'];
	$bucket1_list = $_REQUEST['bucket1_list'];
	$bucket2_list = $_REQUEST['bucket2_list'];
	$bucket3_list = $_REQUEST['bucket3_list'];
	$bucket4_list = $_REQUEST['bucket4_list'];
	$bucket5_list = $_REQUEST['bucket5_list'];

	$sql  = "UPDATE jury_session ";
	$sql .= "   SET bucket1_list = '$bucket1_list' ";
	$sql .= "     , bucket2_list = '$bucket2_list' ";
	$sql .= "     , bucket3_list = '$bucket3_list' ";
	$sql .= "     , bucket4_list = '$bucket4_list' ";
	$sql .= "     , bucket5_list = '$bucket5_list' ";
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
