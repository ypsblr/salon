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
	return_error("Server Error " . $sql);
	die;
}

if(isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) &&
   isset($_REQUEST['yearmonth']) && isset($_REQUEST['award_id']) && isset($_REQUEST['pic_list']) && isset($_REQUEST['action']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$award_id = $_REQUEST['award_id'];
	$pic_list = explode(",", $_REQUEST['pic_list']);
	$action = $_REQUEST['action'];

	$return_list = [];
	foreach ($pic_list as $pic) {
		list($profile_id, $pic_id) = explode("|", $pic);

        // Check if other pics for the user is already awarded
		$sql = "SELECT section FROM award WHERE award_id = '$award_id' and yearmonth = '$yearmonth'";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$section = mysqli_fetch_assoc($query)['section'];

		$sql  = "SELECT * FROM pic_result ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= " AND profile_id = '$profile_id' ";
		$sql .= " AND award_id in (select award_id from award where yearmonth = '$yearmonth' and section = '$section')";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$already_assigned = (mysqli_num_rows($query) != 0);
		
        if ($action == "add" && $already_assigned) {
    // 		$sql = "SELECT * from pic WHERE pic_id = '$pic_id'";
		  //  $query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    // 		$eseq = mysqli_fetch_assoc($query)['eseq'];
    // 		sql_json_error($eseq, "", __FILE__, __LINE__);
            // return_error("The author of this picture '$eseq' has already been awarded in this section");
            return_error("The author of this picture has already been awarded in this section");
        }
        else 
        {
    		// Check if result has already been assigned
    		$sql  = "SELECT * FROM pic_result ";
    		$sql .= " WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
    		$sql .= "   AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
    		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    		$already_assigned = (mysqli_num_rows($query) != 0);
    		if ($action == "add" && (! $already_assigned)) {
    			$sql  = "INSERT INTO pic_result (yearmonth, award_id, profile_id, pic_id, ranking) VALUES ";
    			$sql .= " ('$yearmonth', '$award_id', '$profile_id', '$pic_id', '0') ";
    			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    			$return_list[] = array("profile_id" => $profile_id, "pic_id" => $pic_id, "action_completed" => true);
    		}
    		if ($action == "del" && $already_assigned) {
    			$sql  = "DELETE FROM pic_result ";
    			$sql .= " WHERE yearmonth = '$yearmonth' AND award_id = '$award_id' ";
    			$sql .= "   AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
    			mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    			$return_list[] = array("profile_id" => $profile_id, "pic_id" => $pic_id, "action_completed" => true);
    		}
        }
	}

	$retval = array('status' => 'OK', 'errmsg' => "", "pics" => $return_list);
	echo json_encode($retval);
}
else {
	return_error("Invalid Request");
}
?>
