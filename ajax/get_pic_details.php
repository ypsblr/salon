<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

function rejection_text($notifications) {
	global $DBCON;

	// Gather List of Rejection Reasons
	// Get Notifications List
	$sql  = "SELECT template_code, template_name ";
	$sql .= "  FROM email_template ";
	$sql .= " WHERE template_type = 'user_notification' ";
	$qntf = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$rejection_reasons = array();
	while ($rntf = mysqli_fetch_array($qntf))
		$rejection_reasons[$rntf['template_code']] = $rntf['template_name'];

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

if (isset($_SESSION['USER_ID']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['profile_id']) && isset($_REQUEST['pic_id'])) {
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	$profile_id = $_REQUEST['profile_id'];
	$pic_id = $_REQUEST['pic_id'];
	if ($contest_archived)
		$sql  = "SELECT * FROM ar_pic pic ";
	else
		$sql  = "SELECT * FROM pic ";
	$sql .= " WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' AND pic_id = '$pic_id' ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$pic = mysqli_fetch_array($query, MYSQLI_ASSOC);

		// Add Notifications
		if ($pic['notifications'] == "")
			$pic['rejection_text'] = "";
		else
			$pic['rejection_text'] = rejection_text($pic['notifications']);

		// File Size & Dimensions
		$pic['file_size'] = filesize("../salons/$yearmonth/upload/" . $pic['section'] . ($contest_archived ? "/ar/" : "/") . $pic['picfile']);
		list($pic['width'], $pic['height']) = getimagesize("../salons/$yearmonth/upload/" . $pic['section'] . ($contest_archived ? "/ar/" : "/") . $pic['picfile']);

		$resArray = array();
		$resArray['success'] = true;
		$resArray['msg'] = '';
		$resArray['pic'] = $pic;
		echo json_encode($resArray);
	}
	else {
		handle_error("Picture not found in the database!", __FILE__, __LINE__);
	}
}
else {
	handle_error("Invalid Update Request", __FILE__, __LINE__);
}
?>
