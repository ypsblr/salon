<?php
// session_start();
include("../inc/session.php");
// Insert a rating or tag into ratings table

include("../inc/connect.php");
include("ajax_lib.php");


// A function to return an error message and stop
function return_error($errmsg) {
	$resArray = array();
	$resArray['status'] = "ERR";
	$resArray['errmsg'] = $errmsg;
	echo json_encode($resArray);
	die;
}

if(isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id']) &&
	isset($_REQUEST['jury_id']) && isset($_REQUEST['rating']) && isset($_REQUEST['tags'])  &&
    isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) ) {

	if ($_REQUEST['jury_id'] != $_SESSION['jury_id'] || $_REQUEST['yearmonth'] != $_SESSION['jury_yearmonth']) {
		return_error("Access Denied. Try after logging in again.");
	}
	$jury_id = $_REQUEST['jury_id'];
	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	$rating = $_REQUEST['rating'];
	$tags = mysqli_real_escape_string($DBCON, $_REQUEST['tags']);

	// Get the section that is open
	$sql = "SELECT * FROM jury_session WHERE yearmonth = '$yearmonth' AND section = '$section' AND session_open = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Section not open for judging");

	$updates = 0;
	$inserts = 0;

	// Check if there is a rating record
	$sql  = "SELECT * FROM rating ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$sql .= "   AND pic_id = '$pic_id' ";
	$sql .= "   AND user_id = '$jury_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0) {	// Insert New Rating
		$sql  = "INSERT INTO rating(yearmonth, profile_id, pic_id, user_id, rating, tags) ";
		$sql .= "VALUES ('$yearmonth', '$profile_id', '$pic_id', '$jury_id', '$rating', '$tags')";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (($inserts = mysqli_affected_rows($DBCON)) == 0)
			return_error("Unable to create a new rating record");
	}
	else {	// Update rating or tag
		$sql  = "UPDATE rating ";
		$sql .= "   SET rating = '$rating', ";
		$sql .= "       tags = '$tags' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND profile_id = '$profile_id' ";
		$sql .= "   AND pic_id = '$pic_id' ";
		$sql .= "   AND user_id = '$jury_id' ";
		mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$updates = mysqli_affected_rows($DBCON);
	}
	$retval = array('status' => 'OK',
					'errmsg' => "",
					'rating' => $rating,
					'updates' => $updates,
					'inserts' => $inserts
				   );
	echo json_encode($retval);
}
else
	return_error("Invalid access");
?>
