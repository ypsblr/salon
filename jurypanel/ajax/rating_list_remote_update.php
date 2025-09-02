<?php
// session_start();
include("../inc/session.php");
// Insert a rating or tag into ratings table

include("../inc/connect.php");
include("ajax_lib.php");


// A function to return an error message and stop
function return_error($errmsg) {
	// Save the request in log file
	$log_file = $_SERVER["DOCUMENT_ROOT"] . "/logs/rating_update_failed-" . $_REQUEST['yearmonth'] . "-" . $_REQUEST['section'] . "-" . $_REQUEST['jury_id'] . ".txt";
	// file_put_contents($log_file, date("Y-m-d H:i") .": Rating Update failed with message '$errmsg'. Dump of Data:" . chr(13) . chr(10), FILE_APPEND);
	file_put_contents($log_file, $_REQUEST['ratings']);
	$resArray = array();
	$resArray['status'] = "ERR";
	$resArray['errmsg'] = $errmsg;
	echo json_encode($resArray);
	die;
}

if( isset($_REQUEST['yearmonth']) && isset($_REQUEST['section']) && isset($_REQUEST['ratings']) && isset($_REQUEST['jury_id']) ) {
    // isset($_SESSION['jury_id']) && isset($_SESSION['jury_yearmonth']) ) {

	// if ($_REQUEST['jury_id'] != $_SESSION['jury_id'] || $_REQUEST['yearmonth'] != $_SESSION['jury_yearmonth']) {
		// return_error("Access Denied. Try after logging in again.");
	// }
	$jury_id = $_REQUEST['jury_id'];
	$yearmonth = $_REQUEST['yearmonth'];
	$section = $_REQUEST['section'];
	$ratings = json_decode($_REQUEST['ratings'], true);

	// Get the section that is open
	$sql = "SELECT * FROM jury_session WHERE yearmonth = '$yearmonth' AND section = '$section' AND session_open = '1' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("Section not open for judging");

	$updates = 0;
	$inserts = 0;

	// Start a transaction so that either all ratings are updated or none is updated
	$sql = "START TRANSACTION ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	for ($i = 0; $i < sizeof($ratings); ++ $i) {

		$profile_id = $ratings[$i]['profile_id'];
		$pic_id = $ratings[$i]['pic_id'];
		$rating = $ratings[$i]['rating'];
		$tags = mysqli_real_escape_string($DBCON, $ratings[$i]['rejection_text']);

		// Check if there is a rating record
		$sql  = "SELECT * FROM rating ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND profile_id = '$profile_id' ";
		$sql .= "   AND pic_id = '$pic_id' ";
		$sql .= "   AND user_id = '$jury_id' ";
		$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		if (mysqli_num_rows($query) == 0) {
			// Insert New Rating
			$sql  = "INSERT INTO rating(yearmonth, profile_id, pic_id, user_id, rating, tags) ";
			$sql .= "VALUES ('$yearmonth', '$profile_id', '$pic_id', '$jury_id', '$rating', '$tags')";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_affected_rows($DBCON) == 0)
				return_error("Unable to create a new rating record");
			++ $inserts;
		}
		else {
			// Update rating or tag
			$sql  = "UPDATE rating ";
			$sql .= "   SET rating = '$rating', ";
			$sql .= "       tags = '$tags' ";
			$sql .= " WHERE yearmonth = '$yearmonth' ";
			$sql .= "   AND profile_id = '$profile_id' ";
			$sql .= "   AND pic_id = '$pic_id' ";
			$sql .= "   AND user_id = '$jury_id' ";
			mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$updates += mysqli_affected_rows($DBCON);
		}
	}

	// Commit the updates
	$sql = "COMMIT ";
	mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$retval = array('status' => 'OK',
					'errmsg' => "",
					'ratings' => $ratings,
					'updates' => $updates,
					'inserts' => $inserts
				   );
	echo json_encode($retval);
}
else
	return_error("Invalid access");
?>
