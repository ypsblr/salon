<?php
include_once("connect.php");
include_once("lib.php");

// Default Timezone
date_default_timezone_set("Asia/Kolkata");
define("MAX_RUN_SIZE", 50);
define("DEBUG_TRACE", true);

// Pick up the first open mail queue
$sql = "SELECT * FROM mail_queue WHERE status != 'COMPLETED' AND status != 'DELETED' AND is_test = 0 LIMIT 1 ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
if (mysqli_num_rows($query) == 0)
	die();
$mail_queue = mysqli_fetch_array($query);
$queue_id = $mail_queue['queue_id'];
$yearmonth = $mail_queue['yearmonth'];
$header_image = http_method() . SERVER_ADDRESS . "/salons/" . $yearmonth . "/img/" . $mail_queue['header_img'];
if ($mail_queue['footer_img'] == "")
	$footer_image = "";
else
	$footer_image = http_method() . SERVER_ADDRESS . "/salons/" . $yearmonth . "/img/" . $mail_queue['footer_img'];
$queue_pairs = array("banner-image" => $header_image, "footer-image" => $footer_image);

if (DEBUG_TRACE) echo "Picked up Queue $queue_id for processing<br>";

// Gather Skip List
$sql = "SELECT * FROM mail_skip ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$skip_list = [];
while ($row = mysqli_fetch_array($query)) {
	$skip_list[$row['profile_id']] = $row['reason'];
}
// Change status to PROCESSING
if ($mail_queue['status'] != "PROCESSING") {
	$sql = "UPDATE mail_queue SET status = 'PROCESSING' WHERE queue_id = '$queue_id' ";
	mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
}

if (DEBUG_TRACE) echo "Updated Queue Status to PROCESSING<br>";

// Check for targets and build list
$sql = "SELECT * FROM mail_to WHERE queue_id = '$queue_id' LIMIT " . MAX_RUN_SIZE . " ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$mail_to_list = [];
while ($row = mysqli_fetch_array($query))
	$mail_to_list[] = $row['profile_id'];

if (DEBUG_TRACE) echo "Queue $queue_id contains a list of " . sizeof($mail_to_list) . " profiles<br>";

$salon_pairs = get_salon_values($yearmonth);

$is_salon_archived = $salon_pairs['is-salon-archived'];

// Check for availability of generator & template
if (file_exists($mail_queue['generator'])) {
	include($mail_queue['generator']);
	$gen = new MailGenerator($DBCON, $yearmonth, $is_salon_archived);
}
else
	return_error("Generator file missing", __FILE__, __LINE__);

$template = "../../salons/$yearmonth/blob/" . $mail_queue['template'];

if (! file_exists($template))
	return_error("Template file missing", __FILE__, __LINE__);

$selected = sizeof($mail_to_list);
$sent = 0;
$skipped = 0;
$failed = 0;
$run_start = date("Y-m-d H:i:s");

// Process the queue
foreach ($mail_to_list as $profile_id) {
	// Check Skip List
	if (isset($skip_list[$profile_id])) {
		log_mail_error($queue_id, $profile_id, "skipped", "Skipped for reason " + $skip_list[$profile_id]);
	}

	$gen->initProfile($profile_id);
	list($status, $errmsg) = $gen->getStatus();

	if (DEBUG_TRACE) echo "Status of Init Profile for profile $profile_id is $status ($errmsg)<br>";

	if ($status == "OK") {
		// Generate and send email
		if ($mail_queue['is_test'] == '1')
			$mail_to = $mail_queue['test_emails'];
		else
			$mail_to = $gen->mailTo();

		$pairs = array_merge($salon_pairs, $queue_pairs, $gen->generatorPairs());

		if (DEBUG_TRACE) echo sizeof($pairs) . " pairs generated for profile $profile_id<br>";

		$subject = replace_values($mail_queue['subject'], $pairs);
		$cc_to = ($mail_queue['no_cc'] == '1' ? "nocc" : $mail_queue['cc_to']);
		$email_message = replace_values($gen->generateHeader(), $pairs);
		$email_message .= load_message($template, $pairs);
		$email_message .= replace_values($gen->generateFooter(), $pairs);

		if (DEBUG_TRACE) echo "Email message of " . strlen($email_message) . " bytes created for mailing to $mail_to<br>";

		if ($email_message == "") {
			log_mail_error($queue_id, $profile_id, "failed", "Mail Merge failed");
		}
		else {
			if (send_mail($mail_to, $subject, $email_message, $cc_to)) {
				++ $sent;
			}
			else {
				log_mail_error($queue_id, $profile_id, "failed", "Mail Send failed");
				++ $failed;
			}
		}
	}
	else {
		// Log error
		log_mail_error($queue_id, $profile_id, $status, $errmsg);
		$skipped += ($status == "skipped" ? 1 : 0);
		$failed += ($status == "failed" ? 1 : 0);
	}
	// Delete mail_to irrespective of the outcomes
	$sql = "DELETE FROM mail_to WHERE queue_id = '$queue_id' AND profile_id = '$profile_id' ";
	mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	if (DEBUG_TRACE) echo "Removed profile $profile_id from mailing list<br>";

}

// Insert mail_run log
$run_end = date("Y-m-d H:i:s");
$sql = "SELECT IFNULL(MAX(run_number), 0) AS last_run_number FROM mail_run WHERE queue_id = '$queue_id' ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);
$run_number = $row['last_run_number'] + 1;

$sql  = "INSERT INTO mail_run (queue_id, run_number, run_start, run_end, selected, sent, skipped, failed) ";
$sql .= " VALUES('$queue_id', '$run_number', '$run_start', '$run_end', '$selected', '$sent', '$skipped', '$failed') ";
mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

if (DEBUG_TRACE) echo "Mail Run record created for $queue_id<br>";

$sql  = "SELECT IFNULL(SUM(sent), 0) AS sent, IFNULL(SUM(skipped), 0) AS skipped, IFNULL(SUM(failed), 0) AS failed ";
$sql .= "  FROM mail_run WHERE queue_id = '$queue_id' ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);
$mails_sent = $row['sent'];
$mails_skipped = $row['skipped'];
$mails_failed = $row['failed'];

// Check if anything is left
$sql = "SELECT COUNT(*) AS mails_left FROM mail_to WHERE queue_id = '$queue_id' ";
$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
$row = mysqli_fetch_array($query);
$mails_left = $row['mails_left'];

$sql  = "UPDATE mail_queue ";
$sql .= "   SET sent = '$mails_sent' ";
$sql .= "     , skipped = '$mails_skipped' ";
$sql .= "     , failed = '$mails_failed' ";
if ($mails_left == 0)
	$sql .= "     , status = 'COMPLETED' ";
$sql .= " WHERE queue_id = '$queue_id' ";

mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

if (DEBUG_TRACE) echo "Updated Queue $queue_id. Mails Left $mails_left<br>";

die();

?>
