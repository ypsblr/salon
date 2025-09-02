<?php
// session_start();
include("../inc/session.php");
include("../inc/connect.php");
include("./ajax_lib.php");

function sql_json_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	$result = array(
				"status" => "FAIL",
				"errmsg" => $errmsg,
				"notifications" => "",
				"ratings" => array(-1) );
	echo json_encode($result);
	die;
}

// Fetches rejection text for display
function rejection_text($notifications) {
	global $rejection_reasons;

	$notification_list = explode("|", $notifications);
	$rejection_text = "";
	foreach ($notification_list AS $notification) {
		if ($notification != "") {
			list($notification_date, $notification_code_str) = explode(":", $notification);
			$notification_codes = explode(",", $notification_code_str);
			$rejected = false;
			foreach ($notification_codes as $notification_code)
				if (isset($rejection_reasons[$notification_code])) {
					$rejection_text .= (($rejection_text == "") ? "" : ",") . $rejection_reasons[$notification_code];
				}
		}
	}
	return $rejection_text;
}

// Gather List of Rejection Reasons
// Get Notifications List
$sql  = "SELECT template_code, template_name ";
$sql .= "  FROM email_template ";
$sql .= " WHERE template_type = 'user_notification' ";
$sql .= "   AND will_cause_rejection = '1' ";
$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$rejection_reasons = array();
while ($rntf = mysqli_fetch_array($qntf))
	$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];


if (isset($_POST['jury_yearmonth']) && isset($_POST['profile_id']) && isset($_POST['pic_id'])) {

	$jury_yearmonth = $_POST['jury_yearmonth'];
	$profile_id = $_POST['profile_id'];
	$pic_id = $_POST['pic_id'];
	$sql = "UPDATE ctl SET yearmonth = '$jury_yearmonth', profile_id = '$profile_id', pic_id = '$pic_id'";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get Picture details for the picture
	$sql  = "SELECT pic.section, pic.notifications, pic.print_received ";
	$sql .= "  FROM pic ";
	$sql .= " WHERE pic.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	$qry = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$pic = mysqli_fetch_array($qry);
	$section = $pic['section'];
	$notes = empty($pic['notifications']) ? "" : "*** " . rejection_text($pic['notifications']) . " ***";

	// Get Jury Numbers and Ratings, if present
	$sql  = "SELECT assignment.user_id, assignment.jurynumber, IFNULL(rating.rating, 0) AS rating, IFNULL(rating.tags, '') AS tags ";
	$sql .= "  FROM assignment LEFT JOIN rating ";
	$sql .= "    ON rating.yearmonth = assignment.yearmonth ";
	$sql .= "   AND rating.profile_id = '$profile_id' ";
	$sql .= "   AND rating.pic_id = '$pic_id' ";
	$sql .= "   AND rating.user_id = assignment.user_id ";
	$sql .= " WHERE assignment.yearmonth = '$jury_yearmonth' ";
	$sql .= "   AND assignment.section = '$section' ";
	$sql .= " ORDER BY jurynumber";
	$qry = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get ratings
	$ratingList = array();
	for ($idx = 0; $row = mysqli_fetch_array($qry); $idx++) {
		$ratingList[$idx] = $row['rating'];
		if ($row['tags'] != "")
			$ratingList[$idx] *= -1;
	}

	// Return Result
	$result = array(
				"status" => "SUCCESS",
				"errmsg" => "",
				"notifications" => $notes,
				"ratings" => $ratingList );
	echo json_encode($result);
	die();
}
else {
		$result = array(
				"status" => "FAIL",
				"errmsg" => "Invalid Parameters",
				"notifications" => "",
				"ratings" => array(-1) );
	echo json_encode($result);
	die;
}
?>
