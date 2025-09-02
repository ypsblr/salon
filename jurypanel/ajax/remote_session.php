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

if (isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) &&
   	isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];

	$sql = "SELECT * FROM jury_session WHERE yearmonth = '$yearmonth' AND section = '$section' AND session_open = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("No Jury Sessions are open for this section");
	$session = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Add pic_list for awards
	if ($session['filter_criteria'] == "AWARD") {
		$award_id = $session['bucket'];

		// Get Award Name
		$sql = "SELECT award_name FROM award WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$session['award_name'] = $row['award_name'];

		// Get list of pictures assigned to the award
		$sql = "SELECT * FROM pic_result WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$pic_list = [];
		while ($row = mysqli_fetch_array($query)) {
			$pic_list[] = $row['profile_id'] . "|" . $row['pic_id'];
		}
		$session['award_pic_list'] = implode(",", $pic_list);
	}

	$retval = array('status' => 'OK',
					'errmsg' => "",
					'session' => $session,
				   );
	echo json_encode($retval);
}
else {
	return_error("Invalid Request");
}
?>
