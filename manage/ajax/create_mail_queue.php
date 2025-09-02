<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");



// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$report_date = date("Y-m-d");
	$report_name = $_REQUEST['report_name'];
	$subject = $_REQUEST['subject'];
	$header_image = $_REQUEST['header_image'];
	$footer_image = $_REQUEST['footer_image'];
	$template = $_REQUEST['template'];
	$generator = $_REQUEST['generator'];
	$is_test = $_REQUEST['is_test'];
	$test_emails = $_REQUEST['test_emails'];
	$no_cc = $_REQUEST['no_cc'];
	$cc_to = $_REQUEST['cc_to'];
	$profile_id_list = explode(",", $_REQUEST['profile_id_list']);
	$booked = sizeof($profile_id_list);

	// Validations
	// Check if Template file exists
	if (! file_exists("../../salons/$yearmonth/blob/$template"))
		return_error("Mail Text file missing. Edit and save Mail Text.", __FILE__, __LINE__, true);
	// Check if Header Image file exists
	if ($header_image != "" && (! file_exists("../../salons/$yearmonth/img/$header_image")) )
		return_error("Mail Header Image file missing. Upload Mail Header Image.", __FILE__, __LINE__, true);
	// Check if Footer Image file exists
	if ($footer_image != "" && (! file_exists("../../salons/$yearmonth/img/$footer_image")) )
		return_error("Mail Footer Image file missing. Upload Mail Footer Image.", __FILE__, __LINE__, true);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Generate Queue ID
	$sql = "SELECT IFNULL(MAX(queue_id), 0) AS last_queue_id FROM mail_queue ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	$queue_id = $row['last_queue_id'] + 1;

	// Create Queue
	$sql  = "INSERT INTO mail_queue (queue_id, yearmonth, report_date, report_name, subject, header_img, footer_img, generator, template, ";
	$sql .= "       is_test, test_emails, no_cc, cc_to, booked, sent, skipped, failed, status) ";
	$sql .= "     VALUES('$queue_id', '$yearmonth', '$report_date', '$report_name', '$subject', '$header_image', '$footer_image', '$generator', '$template', ";
	$sql .= "     '$is_test', '$test_emails', '$no_cc', '$cc_to', $booked, 0, 0, 0, 'WAITING' ) ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_affected_rows($DBCON) != 1)
		return_error("Unable to create Mail Queue", __FILE__, __LINE__, true);

	// Create Email Queue
	foreach ($profile_id_list as $profile_id) {
		$sql = "INSERT INTO mail_to (queue_id, profile_id) VALUES('$queue_id', '$profile_id') ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add profile to queue", __FILE__, __LINE__, true);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Return Mail Queue for update
	$sql  = "SELECT * FROM mail_queue WHERE status != 'COMPLETED' AND status != 'DELETED' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$mail_queue = [];
	while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC))
		$mail_queue[] = $row;

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['mail_queue'] = $mail_queue;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
