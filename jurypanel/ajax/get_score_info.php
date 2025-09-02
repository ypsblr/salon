<?php
// session_start();
include("../inc/session.php");
include ("../inc/connect.php");
include ("./ajax_lib.php");

function sql_dump($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	echo $errmsg;
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

if (isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];

	// Gather List of Rejection Reasons
	// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	$sql .= "   AND will_cause_rejection = '1' ";
	$qntf = mysqli_query($DBCON, $sql) or sql_dump($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($rntf = mysqli_fetch_array($qntf))
		$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

	$info = "";

	// Get Picture details for the picture
	$sql  = "SELECT pic.yearmonth, pic.profile_id, pic.pic_id, pic.notifications ";
	$sql .= "  FROM pic ";
	$sql .= " WHERE pic.yearmonth = '$yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.pic_id = '$pic_id' ";
	$qry = mysqli_query($DBCON, $sql) or sql_dump($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($qry) == 0) {
		echo "Unable to find the picture!";
		exit();
	}
	$pic = mysqli_fetch_array($qry);
	$info .= "Reviewer Notifications : " . (empty($pic['notifications']) ? "None" : rejection_text($pic['notifications']));
	$info .= chr(10);

	// Get Jury Numbers and Ratings for the picture
	$sql  = "SELECT user.user_id, user_name, rating.rating, rating.tags ";
	$sql .= "  FROM rating, user ";
	$sql .= " WHERE rating.yearmonth = '$yearmonth' ";
	$sql .= "   AND rating.profile_id = '$profile_id' ";
	$sql .= "   AND rating.pic_id = '$pic_id' ";
	$sql .= "   AND user.user_id = rating.user_id ";
	$sql .= " ORDER BY user_name ";
	$qry = mysqli_query($DBCON, $sql) or sql_dump($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Get ratings & notifications
	while ($row = mysqli_fetch_array($qry)) {
		$info .= $row['user_name'] . " [ " . $row['rating'] . " ] " . $row['tags'] . chr(10);
	}

	echo $info;
}
else {
	echo "Invalid Parameters!";
}
?>
