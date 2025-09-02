<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";

function notify_error($context, $errmsg, $file, $line, $updated = false) {
	debug_dump($context, $errmsg, $file, $line);
	debug_dump("SESSION", $_SESSION, __FILE__, __LINE__);
	echo json_encode(array("status" => "ERROR", "context" => $context, "errmsg" => $errmsg, "file" => $file, "line" => $line, "updated" => $updated));
	die();
}

// Formats results related to a section in the form o HTML table with header and separator
function build_message ($name, $title, $picture, $section, $notification, $eseq, $rectify_link) {

	global $contest;
	global $notifications;
	global $admin_yearmonth;
	global $DBCON;

	// Get submission_last_date for the section
	$sql = "SELECT submission_last_date FROM section WHERE yearmonth = '$admin_yearmonth' AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);

	// General Header
	$m = "<html><head></head><body>";
	$m .= "<p>Dear $name,</p>";
	$m .= "<p>Thank you for your submitting the following picture in " . $contest['contest_name'] . ". </p>";
	$m .= "<h3>$title</h3>";
	$m .= "<h4>Section : $section</h4>";
	$m .= "<p><b>Code : $eseq</p>";
	$embed = http_method() . $_SERVER['SERVER_NAME'] . "/salons/" . $admin_yearmonth . "/upload/" . $section . "/tn/" . $picture;
	$m .= "<p><img src='$embed' alt='$title' ></p><br>";
	if ($rectify_link != "") {
		$m .= "<br><p><a href='" . http_method() . $_SERVER['SERVER_NAME'] . "/upload_rectified.php?code=" . $rectify_link . " '>";
		$m .= "Replace Picture</a></p>";
		$m .= "<p>(Valid for upload on or before ";
		$m .= date("Y-m-d", strtotime("+1 day", strtotime($row['submission_last_date'])));
		$m .= " " . $contest['submission_timezone_name'] . " time";
		$m .= ")</p><br>";
	}
	if ( date_tz(date("Y-m-d", strtotime("+1 day", strtotime($row['submission_last_date']))), $contest['submission_timezone']) < date("Y-m-d") )
		$m .= "<p>Review the following notification(s) related to this picture:</p>";
	else
		$m .= "<p>Review the following notification(s) related to this picture and take appropriate actions:</p>";

	$m .= "<ul>";
	foreach($notification as $notification_type)
		$m .= "<li>" . $notifications[$notification_type] . "</li>";

	$m .= "</ul>";

	// if (isset($notification['size_error']) || isset($notification['section_error']) || isset($notification['title_error']) || isset($notification['watermark_error'])) {
	// 	// if ( date_tz(date("Y-m-d", strtotime("+1 day", strtotime($res['submission_last_date']))), $contest['submission_timezone']) < date("Y-m-d") ) {
	// 	if ( date_tz("now", $contest['submission_timezone']) <= date("Y-m-d", strtotime($row['submission_last_date'] . " +1 day")) ) {
	// 		$m .= "<p>Correcting past uploads is now very easy:</p>";
	// 		$m .= "<ol>";
	// 		$m .= "<li>Open <a href='" . http_method() . $_SERVER['SERVER_NAME'] . "'>" . $contest['contest_name'] . "</a> website and login using your email and password.</li>";
	// 		$m .= "<li>Click on the 'Upload Picture' link. You will find thumbnails of all pictures uploaded by you.</li>";
	// 		$m .= "<li>Click on the 'Edit' link above the thumbnail and make changes / upload a new picture. </li>";
	// 		$m .= "</ol>";
	// 		$m .= "<p>If you face any issues please replay to this email with your query.</p>";
	// 	}
	// 	$m .= "<p>Please note that the above information is provided to you only as a matter of suggestion. You may choose not to make any changes to your submission. ";
	// 	$m .= "Also this is just an outcome of physical verification and is not in any way linked to final result which will be determined only by the judges during the judging process.</p>";
	// }

	// $m .= "<br><p>YPS wishes you success.</p>";
	$m .= "<p></p>";
	$m .= "<p>- ". $contest['contest_name'] . " Committee</p>";

	return $m;
}


if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) ){

	$admin_id = $_SESSION['admin_id'];
	$admin_yearmonth = $_SESSION['admin_yearmonth'];

	// Assemble General Data
	$sql = "SELECT * FROM contest WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);

	// Get a list of User Notification Email names
	$notifications = array();
	$sql = "SELECT * FROM email_template WHERE template_type = 'user_notification' ";
	$notq = mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($notr = mysqli_fetch_array($notq)) {
		$notifications[$notr['template_code']] = $notr['short_html'];
	}

	// Get Reviewer ID
	$sql = "SELECT member_id FROM team WHERE yearmonth = '$admin_yearmonth' AND member_login_id = '$admin_id' ";
	$user_query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$user_row = mysqli_fetch_array($user_query);
	$reviewer_id = $user_row['member_id'];

	$err = "";

	if (isset($_REQUEST['notification']) && sizeof($_REQUEST['notification']) > 0) {
		$notification_str = date("Y-m-d") . ":" . implode(",", $_REQUEST['notification']);
		$sql  = "UPDATE pic ";
		$sql .= "   SET notifications = '$notification_str' ";
		$sql .= "     , reviewed = '1' ";
		$sql .= "     , reviewer_id = '$reviewer_id' ";
		$sql .= " WHERE yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND profile_id = '" . $_REQUEST['profile_id'] . "' ";
		$sql .= "   AND pic_id = '" . $_REQUEST['pic_id'] . "' ";
		mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	else if($_REQUEST['action'] == 'Update') {
		// notifications list is empty
		// Cleaerd all flags
		$sql  = "UPDATE pic ";
		$sql .= "   SET notifications = '' ";
		$sql .= "     , reviewed = '1' ";
		$sql .= "     , reviewer_id = '$reviewer_id' ";
		$sql .= " WHERE yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND profile_id = '" . $_REQUEST['profile_id'] . "' ";
		$sql .= "   AND pic_id = '" . $_REQUEST['pic_id'] . "' ";
		mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	}

	// Update picture received status
	if (isset($_REQUEST['notification']['print_received']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id'])) {
		$sql  = "UPDATE pic, section ";
		$sql .= "   SET print_received = 1 ";
		$sql .= " WHERE pic.yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND pic.profile_id = '" . $_REQUEST['profile_id'] . "' ";
		$sql .= "   AND pic.pic_id = '" . $_REQUEST['pic_id'] . "' ";
		$sql .= "   AND section.yearmonth = pic.yearmonth ";
		$sql .= "   AND section.section = pic.section ";
		$sql .= "   AND section.section_type = 'P' ";
		mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	if (isset($_REQUEST['action']) && $_REQUEST['action'] == "Notify") {
		// Get submission_last_date for the section
		$section = $_REQUEST['section'];
		$sql = "SELECT submission_last_date FROM section WHERE yearmonth = '$admin_yearmonth' AND section = '$section' ";
		$query = mysqli_query($DBCON, $sql)or notify_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);

		// debug_dump("time", time(), __FILE__, __LINE__);
		// debug_dump("last_date+1", strtotime("+1 day", strtotime_tz($row['submission_last_date'], $contest['submission_timezone'])), __FILE__, __LINE__);
		// if ( time() < strtotime("+1 day", strtotime_tz($row['submission_last_date'], $contest['submission_timezone'])) ) {
		// if ( date_tz(date("Y-m-d", strtotime("+1 day", strtotime($row['submission_last_date']))), $contest['submission_timezone']) < date("Y-m-d") ) {
		if ( date_tz("now", $contest['submission_timezone']) > date("Y-m-d", strtotime($row['submission_last_date'] . " +1 day")) ) {
			$rectify_link = "";
		}
		else {
			$rectify_link = encode_string_array(implode("|", [$admin_yearmonth, $_REQUEST['profile_id'], $_REQUEST['pic_id'], $_REQUEST['name']]));
		}
		$msg = build_message($_REQUEST['name'], $_REQUEST['title'], $_REQUEST['picture'], $_REQUEST['section'], $_REQUEST['notification'], $_REQUEST['eseq'], $rectify_link);

		// echo $msg;

		/**** Mailing Function ******/
		if (isset($_REQUEST['email'])) {
			$to = $_REQUEST['email'];

			$subject = "Information on picture submitted by you to " . $contest['contest_name'];

			if(! send_mail($to, $subject, $msg))
				notify_error("Email", "Email Send Failed", __FILE__, __LINE__, true);
		}
		else
			notify_error("Email", "Email Send Failed. No Email specified.", __FILE__, __LINE__, true);
	}
	echo json_encode(array("status" => "OK"));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	notify_error("Input Validation", "Invalid parameters", __FILE__, __LINE__);
}
?>
