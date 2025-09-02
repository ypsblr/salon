<?php
// session_start();
include("inc/session.php");
include("inc/connect.php");
include("inc/lib.php");

// Labels & Values
// Lists
$couriers = [
			array("id" => "india_post", "name" => "India Posts", "tracking_site" => "https://www.indiapost.gov.in",
							"in_person" => "no", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes"),
			array("id" => "award_ceremony", "name" => "Award Ceremony", "tracking_site" => "",
							"in_person" => "yes", "award" => "yes", "catalog" => "no", "domestic" => "yes", "international" => "no"),
			array("id" => "handed_over", "name" => "Handed Over", "tracking_site" => "",
							"in_person" => "yes", "award" => "yes", "catalog" => "yes", "domestic" => "yes", "international" => "yes")
			];
$remitters = [
			array("id" => "sbi", "name" => "State Bank of India", "domestic" => "yes", "international" => "no"),
			array("id" => "amazon", "name" => "Amazon India Gift Voucher", "domestic" => "yes", "international" => "no"),
			array("id" => "paypal", "name" => "Paypal", "domestic" => "no", "international" => "yes"),
			array("id" => "cash", "name" => "Paid in Cash", "domestic" => "yes", "international" => "no")
			];
$status_codes = [
			array("id" => "not_sent", "name" => "Yet to send"),
			array("id" => "sent", "name" => "Sent"),
			array("id" => "received", "name" => "Received")
		];


function get_name($array, $id) {
	foreach ($array as $row) {
		if ($row['id'] == $id)
			return $row['name'];
	}
	return $id;
}

function get_row($array, $val) {
	foreach ($array as $row) {
		if ($row['id'] == $val || $row['name'] == $val)
			return $row;
	}
	return false;
}

// Since there are many columns with Null value, here is a safe way to show null
function safe($str, $default = "") {
	if (is_null($str))
		return $default;
	else
		return $str;
}

function email_filter_from_data ($list) {
	$email_list = [];
	foreach ($list as $item) {
		list ($email, $items, $mailing_date, $tracking_no, $notes) = $item;
		$email_list[] = "'" . $email . "'";
	}
	return implode(",", $email_list);
}

function get_club_name($club_id) {
	global $DBCON;

	if ($club_id != 0 && $club_id != "") {
		$sql = "SELECT club_name FROM club WHERE club_id = '$club_id' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query, MYSQLI_ASSOC);
			return $row['club_name'];
		}
	}
	return "";
}

function get_num_uploads($yearmonth, $profile_id) {
	global $DBCON;
	global $contest_archived;

	$sql  = "SELECT IFNULL(COUNT(*), 0) AS num_pics FROM " . ($contest_archived ? "ar_pic" : "pic") . " ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return $row['num_pics'];
}

function get_catalog_dues($yearmonth, $profile_id) {
	global $DBCON;

	$sql  = "SELECT IFNULL(SUM(number_of_copies), 0) AS number_of_copies, IFNULL(SUM(order_value), 0) AS order_value, ";
	$sql .= "       IFNULL(SUM(payment_received), 0) AS catalog_payment_received, MAX(currency) AS currency ";
	$sql .= "  FROM catalog_order WHERE yearmonth = '$yearmonth' AND profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	return [$row['order_value'] - $row['catalog_payment_received'], $row['currency']];
}

function mail_options() {
	global $yearmonth;
	global $cr_details;

	// Header Image
	if ($cr_details['header_image']) {
		$m = <<<HTML
			<!-- Inputs for salon_opening mail -->
			<div class="col-sm-4"  style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
				<div class="row">
					<div class="col-sm-4 form-group">
						<img id="mail-banner-disp" src="/salons/{$yearmonth}/img/{$cr_details['header_image']}" style="width: 100%;" >
					</div>
					<div class="col-sm-8">
						<label>Upload Header Image</label>
						<input type="file" name="mail_banner" id="mail-banner" class="form-control" ><br>
						<button id="upload-mail-banner" class="btn btn-info pull-right" data-file="{$cr_details['header_image']}">
							<i class="fa fa-upload"></i> Upload
						</button>
					</div>
				</div>
HTML;
	}
	// Footer Image
	if ($cr_details['footer_image'] != '') {
		$m .= <<<HTML
			<div class="row">
				<div class="col-sm-4 form-group">
					<img id="mail-footer-disp" src="/salons/{$yearmonth}/img/{$cr_details['footer_image']}" style="width: 100%;" >
				</div>
				<div class="col-sm-8">
					<label>Upload Footer Image</label>
					<input type="file" name="mail_footer" id="mail-footer" class="form-control" ><br>
					<button id="upload-mail-footer" class="btn btn-info pull-right" data-file="{$cr_details['footer_image']}">
						<i class="fa fa-upload"></i> Upload
					</button>
				</div>
			</div>
HTML;
	}
	if ($cr_details['template'] != "") {
		$m .= <<<HTML
				<div class="row">
					<div class="col-sm-12">
						<label>Edit Mail Text</label>
						<div class="input-group" style="padding-bottom: 15px;">
							<input type="text" class="form-control" value="{$cr_details['template']}" readonly aria-label="...">
							<span class="input-group-btn">
								<a class="btn btn-info pull-right edit-blob"
									data-yearmonth="$yearmonth"
									data-blob-type="salon_mail"
									data-custom-tags="{$cr_details['custom_tags']}"
									data-blob="{$cr_details['template']}" ><i class="fa fa-edit"></i> EDIT</a>
							</span>
						</div>
					</div>
				</div>
			</div>
HTML;
	}

	return $m;

}


if (isset($_SESSION['admin_id']) ) {
	// Set up user and contest information
	include ("inc/load_common_data.php");

	// Initialize Empty Salon
	$salon = array(
			"yearmonth" => "", "contest_name" => "",
			"is_international" => "0",
			"registration_start_date" => NULL, "registration_last_date" => NULL, "submission_timezone" => "", "submission_timezone_name" => "",
			"judging_start_date" => NULL, "judging_end_date" => NULL, "results_date" => NULL,
			"has_exhibition" => "0", "exhibition_start_date" => NULL, "exhibition_end_date" => NULL,
			"archived" => 0, "results_ready" => 0, "update_start_date" => NULL, "update_end_date" => NULL,
			"catalog_release_date" => NULL, "catalog_ready" => 0, "catalog_order_last_date" => NULL, "judging_venue" => "",
	);
	$yearmonth = 0;
	$contest_open = false;
	$contest_archived = false;
	$contest_closed = false;
	$replacement_closed = false;
	$results_ready = false;
	$in_update_period = false;
	$is_catalog_released = false;
	$entry_table = "entry";
	$pic_table = "pic";
	$pic_result_table = "pic_result";
	$rating_table = "rating";

	// Skipped Profiles
	$sql = "SELECT * FROM mail_skip ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$mail_skip_list = [];
	while($row = mysqli_fetch_array($query)) {
		$mail_skip_list[$row['profile_id']] = $row['reason'];
	}

	if (isset($_REQUEST['yearmonth'])) {
		$yearmonth = $_REQUEST['yearmonth'];
		$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			$_SESSION['err_msg'] = "Salon for " . $yearmonth . " not found !";
		else {
			$row = mysqli_fetch_array($query);
			foreach ($salon as $field => $value) {
				if (isset($row[$field]))
					$salon[$field] = $row[$field];
			}
		}
		$contest_open = (date_tz(date("Y-m-d"), $salon['submission_timezone']) >= $salon['registration_start_date'] &&
						date_tz(date("Y-m-d"), $salon['submission_timezone']) <= $salon['registration_last_date']);
		$contest_closed = date_tz(date("Y-m-d"), $salon['submission_timezone']) > $salon['registration_last_date'];
		$replacement_closed = date_tz(date("Y-m-d"), $salon['submission_timezone']) > date("Y-m-d", strtotime($salon['registration_last_date'] . " +1 day"));
		$contest_archived = ($salon['archived'] == '1');
		$results_ready = ($salon['results_ready'] == '1');
		$in_update_period = (date("Y-m-d") >= $salon['update_start_date'] && date("Y-m-d") <= $salon['update_end_date']);
		$is_catalog_released = ($salon['catalog_ready'] == '1');
		// Build the main queries for the current report
		$entry_table = ($contest_archived ? "ar_entry" : "entry");
		$pic_table = ($contest_archived ? "ar_pic" : "pic");
		$pic_result_table = ($contest_archived ? "ar_pic_result" : "pic_result");
		$rating_table = ($contest_archived ? "ar_rating" : "rating");
	}

	$sectionList = array();
	$num_digital_sections = 0;
	$num_print_sections = 0;
	$digital_last_date = "";
	$print_last_date = "";
	if ($yearmonth != 0) {
		// $jury_yearmonth = $_SESSION['jury_yearmonth'];
		$sql = "SELECT section_type, section, submission_last_date FROM section WHERE yearmonth = '$yearmonth' ORDER BY section_type, section";
		$sqry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($srow = mysqli_fetch_array($sqry)) {
			$sectionList[$srow['section']] = $srow['section_type'];
			if ($srow['section_type'] == "D") {
				$num_digital_sections ++;
				$digital_last_date = max($srow['submission_last_date'], $digital_last_date);
			}
			if ($srow['section_type'] == "P") {
				$num_print_sections ++;
				$print_last_date = max($srow['submission_last_date'], $print_last_date);
			}
		}
	}

	$youth_category_list = array();
	if ($yearmonth != 0) {
		$sql = "SELECT * FROM entrant_category WHERE yearmonth = '$yearmonth' AND age_within_range = '1' AND age_maximum = '18' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query))
			$youth_category_list[] = $row['entrant_category'];
	}

	// Profile Lists
	// Cash Award Recipients
	$cash_award_recipient_list = array();
	// Picture Awards
	if ($yearmonth != 0) {
		$sql  = "SELECT pic_result.profile_id, IFNULL(SUM(cash_award), 0) AS cash_award ";
		$sql .= "  FROM $pic_result_table AS pic_result, award ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND award.cash_award > 0 ";
		$sql .= " GROUP BY pic_result.profile_id ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if (isset($cash_award_recipient_list[$row['profile_id']]))
				$cash_award_recipient_list[$row['profile_id']] += $row['cash_award'];
			else
				$cash_award_recipient_list[$row['profile_id']] = $row['cash_award'];
		}

		// Entry Result
		$sql  = "SELECT DISTINCT entry_result.profile_id, IFNULL(SUM(cash_award), 0) AS cash_award ";
		$sql .= "  FROM entry_result, award ";
		$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$sql .= "   AND award.cash_award > 0 ";
		$sql .= " GROUP BY entry_result.profile_id ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			if (isset($cash_award_recipient_list[$row['profile_id']]))
				$cash_award_recipient_list[$row['profile_id']] += $row['cash_award'];
			else
				$cash_award_recipient_list[$row['profile_id']] = $row['cash_award'];
		}
	}
	$contest_has_cash_awards = (sizeof($cash_award_recipient_list) > 0);
	$show_cash_award_column = (sizeof($cash_award_recipient_list) > 0);

	// Award Winners with requirement to mail
	$award_winners_list = [];
	if ($yearmonth != 0) {
		$sql  = "SELECT DISTINCT pic_result.profile_id ";
		$sql .= "  FROM $pic_result_table AS pic_result, award ";
		$sql .= " WHERE pic_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
		$sql .= "   AND award.award_id = pic_result.award_id ";
		$sql .= "   AND (award.has_medal = '1' OR award.has_pin = '1' ";
		$sql .= "        OR award.has_ribbon = '1' OR award.has_memento = '1' ";
		$sql .= "        OR award.has_gift = '1') ";
		$sql .= " UNION ";
		$sql .= "SELECT DISTINCT entry_result.profile_id ";
		$sql .= "  FROM entry_result, award ";
		$sql .= " WHERE entry_result.yearmonth = '$yearmonth' ";
		$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
		$sql .= "   AND award.award_id = entry_result.award_id ";
		$sql .= "   AND (award.has_medal = '1' OR award.has_pin = '1' ";
		$sql .= "        OR award.has_ribbon = '1' OR award.has_memento = '1' ";
		$sql .= "        OR award.has_gift = '1') ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query)) {
			$award_winners_list[] = $row['profile_id'];
		}
	}

	// Catalog Orders - Profile List
	$catalog_ordered_participants = [];
	if ($yearmonth != 0) {
		$sql = "SELECT DISTINCT profile_id FROM catalog_order WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		while ($row = mysqli_fetch_array($query))
			$catalog_ordered_participants[] = $row['profile_id'];
	}
	$show_catalog_column = (sizeof($catalog_ordered_participants) > 0);

	// Is there Early Bird
	$has_earlybird = false;
	$earlybird_last_date = NULL;
	$earlybird_open = false;
	if ($yearmonth != 0) {
		$sql  = "SELECT COUNT(*) AS has_early_bird, MAX(fee_end_date) AS last_date FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' AND fee_code = 'EARLY BIRD' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$has_earlybird = ($row['has_early_bird'] != 0);
		$earlybird_last_date = $row['last_date'];
		$earlybird_open = ($has_earlybird && date_tz(date("Y-m-d"), $salon['submission_timezone']) > $earlybird_last_date);
	}

	// Check if exhibition has been published
	$exhibition_announced = false;
	$is_exhibition_virtual = false;
	if ($yearmonth != 0) {
		$sql = "SELECT * FROM exhibition WHERE yearmonth = '$yearmonth' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$exhibition_announced = (mysqli_num_rows($query) > 0);
			$exhibition = mysqli_fetch_array($query);
			$is_exhibition_virtual = $exhibition['is_virtual'];
		}
	}

	// Get Tracking Data
	$tracking_data = [];
	if ($yearmonth != 0 && file_exists("../salons/$yearmonth/blob/posting.json")) {
		$json_data = json_decode(file_get_contents("../salons/$yearmonth/blob/posting.json"), true);
		foreach ($json_data as $posting_category => $posting_list) {
			foreach ($posting_list as $posting) {
				if ($posting_category == "cash_remittances")
					$posting_type = "CASH";
				elseif ($posting_category == "award_mailing")
					$posting_type = "AWARD";
				elseif ($posting_category == "catalog_mailing")
					$posting_type = "CATALOG";
				else
					$posting_type = "UNKNOWN";

				$tracking_data[$posting['profile_id'] . "|" . $posting_type] = $posting;

			}
		}
		// $sql = "SELECT * FROM postings WHERE yearmonth = '$yearmonth' ";
		// $query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		// while ($row = mysqli_fetch_array($query, MYSQLI_ASSOC)) {
		// 	$tracking_data[$row['profile_id'] . "|" . $row['posting_type']] = $row;
		// }
	}

	$salon_reports = [
		"all_profiles" => array(
								"title" => "All Salon Profiles",
								"subject" => "",
								"valid" => true,
								"profiles" => "all_profiles",
								"include_indians" => true,
								"include_foreigners" => true,
						  		"include_yps_members" => false,
								"template" => "",
								"header_image" => "",
								"footer_image" => "",
								"generator" => "",
								"custom_tags" => "",
								"send_mail" => false
							),
		"salon_opening" => array(
								"title" => "Salon Opening Mail",
								"subject" => $salon['contest_name'] . " is open for participation ",
								"valid" => $contest_open,
								"profiles" => "non-entrants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "salon_open_mail.htm",
								"header_image" => "salon_mail_banner.jpg",
								"footer_image" => "",
								"generator" => "salon_open.php",
								"custom_tags" => "participant-name",
								"send_mail" => true
							),
		"salon_reminder" => array(
								"title" => "Salon Reminder",
								"subject" => $salon['contest_name'] . " closes on " . pdate($salon['registration_last_date']) . " - Participate Today",
								"valid" => $contest_open,
								"profiles" => "non-entrants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "salon_reminder_mail.htm",
								"header_image" => "salon_mail_banner.jpg",
								"footer_image" => "",
								"generator" => "salon_reminder.php",
								"custom_tags" => "participant-name",
								"send_mail" => true
							),
		"early_bird_ending" => array(
								"title" => "Early Bird Ending",
								"subject" => "Early Bird rates are ending on " . pdate($earlybird_last_date) . ". Register and pay to avail the rates",
								"valid" => $earlybird_open,
								"profiles" => "all",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => false,
								"template" => "earlybird_reminder.htm",
								"header_image" => "salon_mail_banner.jpg",
								"footer_image" => "",
								"generator" => "salon_earlybird.php",
								"custom_tags" => "participant-name",
								"send_mail" => true
							),
		"upload_reminder" => array(
								"title" => "Uploads Pending",
								"subject" => "You have not completed all the uploads",
								"valid" => $contest_open,
								"profiles" => "registered-not-uploaded",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => true,
								"template" => "upload_reminder.htm",
								"header_image" => "generic_mail_header.jpg",
								"footer_image" => "generic_mail_footer.jpg",
								"generator" => "upload_reminder.php",
								"custom_tags" => "participant-name",
								"send_mail" => true
							),
		"replace_reminder" => array(
								"title" => "Remind Rejected",
								"subject" => "There are rejected pictures requiring replacement",
								"valid" => (! $replacement_closed),
								"profiles" => "replacements-pending",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => true,
								"template" => "replace_reminder.htm",
								"header_image" => "generic_mail_header.jpg",
								"footer_image" => "generic_mail_footer.jpg",
								"generator" => "replace_reminder.php",
								"custom_tags" => "participant-name, pic-notifications",
								"send_mail" => true
							),
		"judging_invite" => array(
								"title" => "Judging Invite",
								"subject" => "You are invited to witness the Judging Event",
								"valid" => ($results_ready == false && $salon['judging_venue'] != ""),
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => true,
								"template" => "judging_invite.htm",
								"header_image" => "generic_mail_header.jpg",
								"footer_image" => "generic_mail_footer.jpg",
								"generator" => "judging_invite.php",
								"custom_tags" => "participant-name",
								"send_mail" => true
							),
		"salon_results" => array(
								"title" => "Mail Results",
								"subject" => "[salon-name] - Results for [participant-name]",
								"valid" => $results_ready,
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => true,
								"cc_to_salon_email" => true,
								"template" => "salon_results.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "salon_results.php",
								"custom_tags" => "participant-name, contest-data, download-actions, update-actions, individual-data, "
												. "special-pic-data, awards-data, acceptances-data, others-data, recognition-data",
								"send_mail" => true
							),
		"update_reminder" => array(
								"title" => "Remind Updates",
								"subject" => "[salon-name] - Action Required by [update-end-date]",
								"valid" => $in_update_period,
								"profiles" => "update-pending",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
						  		"include_yps_members" => true,
								"template" => "update_reminder.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "update_reminder.php",
								"custom_tags" => "participant-name, contest-data, action-required-message, complete-uploads-message",
								"send_mail" => true
							),
		"exhibition_invite_venue" => array(
								"title" => "Physical Exhibition Invite",
								"subject" => "Invitation to [salon-name] Exhibition and Award Function",
								"valid" => ($exhibition_announced && (date("Y-m-d") <= $salon['exhibition_end_date']) && (! $is_exhibition_virtual)),
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "exhibition_invite_venue.htm",
								"header_image" => "exhibition-banner.jpg",
								"footer_image" => "",
								"generator" => "exhibition_invite_venue.php",
								"custom_tags" => "participant-name, exhibition-chair-exists, exhibition-guest-exists, exhibition-other-exists, "
												. "exhibition-invite-exists, exhibition-message-exists, exhibition-invite-img, exhibition-message-blob ",
								"send_mail" => true
							),
		"exhibition_invite_webinar" => array(
								"title" => "Virtual Exhibition Invite",
								"subject" => "Invitation to [salon-name] Virtual Exhibition and Award Function",
								"valid" => ($exhibition_announced && (date("Y-m-d") <= $salon['exhibition_end_date']) && $is_exhibition_virtual),
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "exhibition_invite_webinar.htm",
								"header_image" => "exhibition-banner.jpg",
								"footer_image" => "",
								"generator" => "exhibition_invite_webinar.php",
								"custom_tags" => "participant-name, exhibition-chair-exists, exhibition-guest-exists, exhibition-other-exists",
								"send_mail" => true
							),
		"catalog_mail" => array(
								"title" => "Announce Catalog Release",
								"subject" => "Catalog Released for [salon-name]",
								"valid" => $is_catalog_released && (date("Y-m-d") <= $salon['catalog_order_last_date']),
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "catalog_mail.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "catalog_mail.php",
								"custom_tags" => "participant-name, is-exhibition-virtual, is-exhibition-venue, can-order-catalog, is-participant-indian, "
												. "is-participant-foreigner, catalog-list, catalog-file-view, "
												. "catalog-file-download, account-data, mailing-data, catalog-order-last-date, salon-actions",
								"send_mail" => true
							),
		"catalog_reminder" => array(
								"title" => "Remind to order Catalog",
								"subject" => "Last date for ordering Printed Catalog of [salon-name] is " . pdate($salon['catalog_order_last_date']),
								"valid" => $is_catalog_released && (date("Y-m-d") <= $salon['catalog_order_last_date']),
								"profiles" => "salon-participants",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "catalog_reminder.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "catalog_reminder.php",
								"custom_tags" => "participant-name, can-order-catalog, catalog-order-last-date, catalog-list, catalog-order-link, "
												. "catalog-file-view, catalog-file-download",
								"send_mail" => true
							),
		"money_transfer" => array(
								"title" => "Notify Money Transfer",
								"subject" => "Your award money for [salon-name] has been transferred",
								"valid" => $results_ready && $contest_has_cash_awards,
								"profiles" => "cash-award-winners",
								"show" => "CASH",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "award_money_transfer.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "award_money_transfer.php",
								"custom_tags" => "participant-name, award-currency, cash-award, bank-account, posting-operator, posting-date",
								"send_mail" => true
							),
		"award_mailing" => array(
								"title" => "Notify Award Tracking",
								"subject" => "Your awards for [salon-name] have been shipped",
								"valid" => $results_ready,
								"profiles" => "award-winners",
								"show" => "AWARD",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "award_mailing.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "award_mailing.php",
								"custom_tags" => "participant-name, tracking-number, tracking-website, posting-operator, posting-date",
								"send_mail" => true
							),
		"catalog_mailing" => array(
								"title" => "Notify Catalog Tracking",
								"subject" => "Your catalog for [salon-name] has been shipped",
								"valid" => $results_ready,
								"profiles" => "catalog-orderers",
								"show" => "CATALOG",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "catalog_mailing.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "catalog_mailing.php",
								"custom_tags" => "participant-name, tracking-number, tracking-website, posting-operator, posting-date",
								"send_mail" => true
							),
		"custom_mail" => array(
								"title" => "Custom Email to Awardees",
								"subject" => "Information/Updates on [salon-name] that you had participated",
								"valid" => $results_ready,
								"profiles" => "award-winners",
								"include_indians" => true,
								"include_foreigners" => ($salon['is_international'] == '1'),
								"include_yps_members" => true,
								"template" => "custom_mail.htm",
								"header_image" => "results_banner.jpg",
								"footer_image" => "",
								"generator" => "custom_mail.php",
								"custom_tags" => "participant-name, phone, avatar, digital-sections, print-sections, uploads, medals, honorable-mentions, acceptances",
								"send_mail" => true
							),
	];
	// TODO : Custom Communication to Awardees


	// Establish Current Report
	$cr_name = "";
	$cr_details = [];
	$cr_include_indians = false;
	$cr_include_foreigners = false;
	$cr_include_yps_members = false;
	$cr_cc_to_salon_email = false;
	if (! empty($_REQUEST['report_name'])) {
		foreach ($salon_reports as $report_name => $report_details) {
			if ($report_name == $_REQUEST['report_name']) {		// based on button pressed
				$cr_name = $report_name;
				$cr_details = $report_details;
				$cr_include_indians = $report_details['include_indians'];
				$cr_include_foreigners = $report_details['include_foreigners'];
				$cr_include_yps_members = $report_details['include_yps_members'];
				$cr_cc_to_salon_email = (isset($report_details['cc_to_salon_email']) ? $report_details['cc_to_salon_email'] : false);
			}
		}

		// Build the main queries for the current report
		$entry_table = ($contest_archived ? "ar_entry" : "entry");
		$pic_table = ($contest_archived ? "ar_pic" : "pic");
		$pic_result_table = ($contest_archived ? "ar_pic_result" : "pic_result");
		$rating_table = ($contest_archived ? "ar_rating" : "rating");

		$has_entry_details = false;
		if (isset($cr_details['profiles'])) {
			switch ($cr_details['profiles']) {
				case 'all_profiles' : {
					$main_sql  = "SELECT * FROM profile, country ";
					$main_sql .= " WHERE country.country_id = profile.country_id ";
					$has_entry_details = false;
					break;
				}
				case 'all' : {
					$main_sql  = "SELECT * FROM profile, country ";
					$main_sql .= " WHERE profile_disabled = '0' ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$has_entry_details = false;
					break;
				}
				case 'non-entrants' : {
					$main_sql  = "SELECT * FROM profile, country ";
					$main_sql .= " WHERE profile.profile_id NOT IN ( ";
					$main_sql .= "       SELECT profile_id FROM $entry_table ";
					$main_sql .= "        WHERE yearmonth = '$yearmonth' ) ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$has_entry_details = false;
					break;
				}
				case 'registered-not-uploaded' : {
					// Default to participants in the salon
					$main_sql  = "SELECT * FROM profile, $entry_table AS entry, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND ( entry.uploads < ((digital_sections + print_sections) * 4) ";
					$main_sql .= "    OR entry.payment_received < (entry.fees_payable - entry.discount_applicable) ";
					$main_sql .= "       ) ";

					$has_entry_details = true;
					break;
				}
				case 'replacements-pending' : {
					$main_sql  = "SELECT * FROM profile, $entry_table AS entry, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND profile.profile_id IN ( ";
					$main_sql .= "                      SELECT DISTINCT profile_id FROM $pic_table AS pic ";
					$main_sql .= "                       WHERE yearmonth = '$yearmonth' ";
					$main_sql .= "                         AND pic.notifications != '' ";
					$main_sql .= "                      ) ";

					$has_entry_details = true;
					break;

				}
				case 'salon-participants' : {
					// Default to participants in the salon
					$main_sql  = "SELECT * FROM profile, $entry_table AS entry, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND entry.uploads > '0' ";
					$has_entry_details = true;
					break;
				}
				case 'update-pending' : {
					$main_sql  = "SELECT * FROM profile, $entry_table as entry, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND ( ";
					$main_sql .= "          ( ";
					$main_sql .= "              (   bank_account_number = '' ";		// Bank Account details empty
					$main_sql .= "               OR bank_account_name = '' ";
					$main_sql .= "               OR bank_account_type = '' ";
					$main_sql .= "               OR bank_name = '' ";
					$main_sql .= "               OR bank_branch = '' ";
					$main_sql .= "               OR bank_ifsc_code = '' ";
					$main_sql .= "              ) ";
					$main_sql .= "              AND ( ";
					$main_sql .= "                    profile.profile_id IN ( ";			// There is Cash Award for entry type award
					$main_sql .= "                    SELECT DISTINCT profile_id FROM entry_result, award ";
					$main_sql .= "                     WHERE entry_result.yearmonth = entry.yearmonth ";
					$main_sql .= "                       AND award.yearmonth = entry_result.yearmonth ";
					$main_sql .= "                       AND award.award_id = entry_result.award_id ";
					$main_sql .= "                       AND award.cash_award > 0 ";
					$main_sql .= "                    ) ";
					$main_sql .= "                    OR ";
					$main_sql .= "                    profile.profile_id IN ( ";			// There is Cash Award for picture type award
					$main_sql .= "                    SELECT DISTINCT profile_id FROM $pic_result_table AS pic_result, award ";
					$main_sql .= "                     WHERE pic_result.yearmonth = entry.yearmonth ";
					$main_sql .= "                       AND award.yearmonth = pic_result.yearmonth ";
					$main_sql .= "                       AND award.award_id = pic_result.award_id ";
					$main_sql .= "                       AND award.cash_award > 0 ) ";
					$main_sql .= "              ) ";
					$main_sql .= "          ) ";
					$main_sql .= "          OR ";
					$main_sql .= "             profile.profile_id IN ( ";
					$main_sql .= "             SELECT DISTINCT pic.profile_id FROM $pic_table AS pic, $pic_result_table AS pic_result, award ";
					$main_sql .= "              WHERE pic.yearmonth = entry.yearmonth ";
					$main_sql .= "                AND pic.full_picfile IS NULL ";
					$main_sql .= "                AND pic_result.yearmonth = pic.yearmonth ";
					$main_sql .= "                AND pic_result.profile_id = pic.profile_id ";
					$main_sql .= "                AND pic_result.pic_id = pic.pic_id ";
					$main_sql .= "                AND award.yearmonth = pic_result.yearmonth ";
					$main_sql .= "                AND award.award_id = pic_result.award_id ";
					$main_sql .= "                AND award.level < 99 ) ";
					$main_sql .= "       ) ";

					$has_entry_details = true;
					break;
				}
				case 'cash-award-winners' : {
					$cash_award_profiles = implode(",", array_merge([0], array_keys($cash_award_recipient_list)));
					$main_sql  = "SELECT * FROM profile, $entry_table, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND profile.profile_id IN (" . $cash_award_profiles . ") ";
					$has_entry_details = true;
					break;

				}
				case 'award-winners' : {
					$award_profiles = implode(",", array_merge([0], array_values($award_winners_list)));
					$main_sql  = "SELECT * FROM profile, $entry_table, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND profile.profile_id IN (" . $award_profiles . ") ";
					$has_entry_details = true;
					break;
				}
				case 'catalog-orderers' : {
					$award_profiles = implode(",", array_merge([0], array_values($catalog_ordered_participants)));
					$main_sql  = "SELECT * FROM profile, $entry_table, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$main_sql .= "   AND profile.profile_id IN (" . $award_profiles . ") ";
					$has_entry_details = true;
					break;
				}
				default : {
					// Default to participants in the salon
					$main_sql  = "SELECT * FROM profile, $entry_table, country ";
					$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
					$main_sql .= "   AND entry.profile_id = profile.profile_id ";
					$main_sql .= "   AND country.country_id = profile.country_id ";
					$main_sql .= "   AND profile_disabled = '0' ";
					$has_entry_details = true;
				}
			}
		}
		else {
			// Default to participants in the salon
			$main_sql  = "SELECT * FROM profile, $entry_table ";
			$main_sql .= " WHERE entry.yearmonth = '$yearmonth' ";
			$main_sql .= "   AND entry.profile_id = profile.profile_id ";
			$has_entry_details = true;
		}
	}
?>

<!DOCTYPE html>
<html>

<head>
    <!-- Page title -->
	<title>Youth Photographic Society | Participant Follow-up Panel</title>

	<?php include "inc/header.php"; ?>

    <link rel="stylesheet" href="plugin/datatable/css/dataTables.bootstrap.min.css" />

    <!-- App styles -->
    <link rel="stylesheet" href="plugin/blueimp-gallery/css/blueimp-gallery.min.css" />

	<style>
		div.filter-button {
			display:inline-block;
			margin-right: 15px;
		}
	</style>
</head>
<body class="fixed-navbar fixed-sidebar">

	<!-- Simple splash screen-->
	<div class="splash">
		<div class="color-line"></div>
		<div class="splash-title">
			<h1>    YPS ADMIN PANEL  </h1>
			<p>Please Wait. </p>
			<div class="spinner">
				<div class="rect1"></div>
				<div class="rect2"></div>
				<div class="rect3"></div>
				<div class="rect4"></div>
				<div class="rect5"></div>
			</div>
		</div>
	</div>

	<!-- Header -->
<?php
	include "inc/master_topbar.php";
	include "inc/master_sidebar.php";
?>

	<!-- Main Wrapper -->
	<div id="wrapper">
		<div class="normalheader transition animated fadeIn">
			<div class="hpanel">
				<div class="panel-body">
					<a class="small-header-action" href="#">
						<div class="clip-header">
							<i class="fa fa-arrow-up"></i>
						</div>
					</a>
					<!-- Select a Salon -->
					<h3 class="font-light m-b-xs">
						<?= $yearmonth == 0 ? "Manage Mails - Select a Salon" : "Managing Mails for " . $salon['contest_name'];?>
					</h3>
					<br>
					<form role="form" method="post" id="select-salon-form" name="select-salon-form" action="salon_mail.php" enctype="multipart/form-data" >
						<div class="row form-group">
							<div class="col-sm-8">
								<label for="yearmonth">Select Salon</label>
								<div class="input-group">
									<select class="form-control" name="yearmonth" id="select-yearmonth" value="<?= $yearmonth;?>">
									<?php
										$sql = "SELECT yearmonth, contest_name FROM contest ORDER BY yearmonth DESC ";
										$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
										while ($row = mysqli_fetch_array($query)) {
									?>
										<option value="<?= $row['yearmonth'];?>" <?= ($row['yearmonth'] == $yearmonth) ? "selected" : "";?>><?= $row['contest_name'];?></option>
									<?php
										}
									?>
									</select>
									<span class="input-group-btn">
										<button type="submit" class="btn btn-info pull-right" name="edit-contest-button" id="edit-contest-button" ><i class="fa fa-play"></i> GO </a>
									</span>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>

		<div class="content">
			<div class="row">
				<div class="col-sm-12">
					<div class="hpanel">
						<div class="panel-body">
							<!-- Report Selection -->
							<div class="row">
								<div class="col-sm-12">
									<h3 class="font-light m-b-xs">
										Send Salon Mails
									</h3>
									<form name="filter_form" method="post" action="salon_mail.php">
										<input type="hidden" name="yearmonth" value="<?= $yearmonth;?>">
										<div class="row">
											<div class="col-sm-12 form-group" >
												<label>List of Mails - Select a mail to work on</label>
												<!-- Participant Filter -->
												<div class="row form-group">
													<?php
														$idx = 0;
														foreach ($salon_reports as $report_name => $report_details) {
															if ($report_details['valid']) {
																++ $idx;
																if ($idx > 4) {
																	$idx = 0;
													?>
													<div class="clearfix" style="padding-bottom: 8px;"></div>
													<?php
																}
													?>
													<div class="col-sm-3">
														<div class="input-group">
															<input type="text" class="form-control" value="<?= $report_details['title'];?>" readonly aria-label="...">
															<span class="input-group-btn">
																<button name="report_name" value="<?= $report_name;?>" class="btn btn-info" id="<?= $report_name;?>" ><i class="fa fa-play"></i> GO </button>
															</span>
														</div>
													</div>
													<?php
															}
														}
													?>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
							<!-- Participant Filtering & Selection -->
							<?php
								if (! empty($cr_name)) {
							?>
							<div class="row">
								<div class="col-sm-12">

									<!-- Manage filters -->
									<form method="post" name="mail_queue_form" id="mail-queue-form" >
										<!-- Hidden Inputs -->
										<input type="hidden" id="yearmonth" value="<?= $yearmonth;?>" >
										<input type="hidden" id="report-name" value="<?= $cr_name;?>" >
										<input type="hidden" id="report-details" value='<?= json_encode($cr_details, JSON_FORCE_OBJECT);?>'>

										<h3 class="text-info"><?= isset($cr_details['title']) ? $cr_details['title'] : "None Selected";?></h3>
										<div class="row">
											<?php
												if ($cr_details['send_mail']) {
													echo mail_options();
											?>
											<!-- Controls -->
											<div class="col-sm-4" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
												<div class="row">
													<div class="col-sm-12">
														<label>Show : </label>
														<span style="margin-left: 10px;">
															<label><input type="radio" name="filter_profiles" id="include_all" <?= ($cr_include_indians && $cr_include_foreigners) ? "checked" : "";?> > All</label>
														</span>
														<span style="margin-left: 10px;">
															<label><input type="radio" name="filter_profiles" id="include_indians" <?= ($cr_include_indians && (! $cr_include_foreigners)) ? "checked" : "";?> > Indians</label>
														</span>
														<span style="margin-left: 10px;">
															<label><input type="radio" name="filter_profiles" id="include_foreigners" <?= ($cr_include_foreigners && (! $cr_include_indians)) ? "checked" : "";?> > Foreigners</label>
														</span>
														<span style="margin-left: 10px;">
															<label><input type="checkbox" name="filter_yps_members" id="include_yps_members" <?= $cr_include_yps_members ? "checked" : "";?> > Include YPS Members</label>
														</span>
													</div>
													<div class="col-sm-12">
														<label>Select : </label>
														<span style="margin-left: 10px;">
															<label><input type="radio" name="select_profiles" id="select_none" checked > None</label>
														</span>
														<span style="margin-left: 10px;">
															<label><input type="radio" name="select_profiles" id="select_all"> All</label>
														</span>
														<span style="margin-left: 10px;"><label><input type="radio" name="select_profiles" id="select_all_on_page"> All on Page</label></span>
														<span style="margin-left: 10px; color: red;" id="num-profiles-selected"></span>
													</div>
													<div class="col-sm-12">
														<span><label>Send Options : </label></span>
														<span style="margin-left: 10px;"><label><input type="checkbox" name="send_test_email" id="send-test-email" checked > Send Test Mail</label></span>
														<span style="margin-left: 10px;"><label><input type="checkbox" name="no_cc" id="no-cc" <?= $cr_cc_to_salon_email ? "" : "checked";?> > Do not CC Salon Email</label></span>
														<br>
														<label>Test Emails</label>
														<input type="text" class="form-control" id="test-emails" >
													</div>
												</div>
											</div>
											<?php
												}
											?>
											<!-- Edit Salon Mail -->
											<div class="col-sm-4 form-group" style="padding: 4px; border : 1px solid #aaa; border-radius : 8px;">
											<?php
												if ($cr_details['send_mail']) {
											?>
												<label>Mail Queue <a class='text-info' id='refresh-queue'><i class='fa fa-refresh'></i></a></label>
												<span id="mail-queue-display">
												<?php
													$sql = "SELECT * FROM mail_queue WHERE status != 'COMPLETED' AND status != 'DELETED' ";
													$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													if (mysqli_num_rows($query) == 0) {
												?>
													<p>Mail Queue is empty</p>
												<?php
													}
													else {
														while ($row = mysqli_fetch_array($query)) {
												?>
													<p>
														<?= $row['report_date'];?> <?= $row['report_name'];?>
														( <?= $row['sent'] + $row['skipped'] + $row['failed'];?> / <?= $row['booked'];?> )
														<?= $row['status'];?>
														<a class="delete-queue" data-queue-id="<?= $row['queue_id'];?>"><span style="color: red;"><i class="fa fa-trash"></i></span></a>
													</p>
												<?php
														}
													}
												?>
												</span>
												<p><button name="queue_report" class="btn btn-info btn-sm" id="queue-report"><i class="fa fa-plus"></i> Queue this Report </button></p>
											<?php
												}
											?>
												<label>Stop sending Emails to selected profiles</label>
												<p>
													<button class="btn btn-info btn-sm mail-skip" data-skip-reason="UNSUBSCRIBED"><i class="fa fa-lock"></i> Unsubscribe </button>
													<button class="btn btn-info btn-sm mail-skip" data-skip-reason="INVALID_EMAIL"><i class="fa fa-lock"></i> Invalid Email </button>
													<button class="btn btn-info btn-sm mail-skip" data-skip-reason="RESUME"><i class="fa fa-unlock"></i> Resume Email </button>
												</p>
											</div>
										</div>
										<div class="row">
											<div class="col-sm-12">
												<!-- Show Details -->
												<table id="entry_table" class="table table-striped table-bordered table-hover" style="width: 100%;">
												<thead>
													<tr>
														<th>Select</th>
														<th>Category</th>
														<th>Name, Club, Email, Phone</th>
														<th>Sections</th>
														<th>Uploads</th>
														<th>Wins</th>
														<th>Salon Dues</th>
														<th>Catalog Dues</th>
														<?php
															if ($show_cash_award_column && isset($cr_details["show"]) && $cr_details["show"] == "CASH") {
														?>
														<th>Cash Award Transfer Status</th>
														<?php
															}
															if ($show_catalog_column && isset($cr_details['show']) && $cr_details['show'] == "CATALOG") {
														?>
														<th>Catalog Posting Status</th>
														<?php
															}
															if (isset($cr_details['show']) && $cr_details['show'] == "AWARD") {
														?>
														<th>Award Posting Status</th>
														<?php
															}
														?>
													</tr>
												</thead>
												<tbody>
												<?php
													// Filter Flags
													$showing_pc_list = false;

													// Run the Main Loop
													$entry = mysqli_query($DBCON, $main_sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
													$row_no = 0;
													while($tr_entry = mysqli_fetch_array($entry)) {
														++ $row_no;
														$profile_id = $tr_entry['profile_id'];
														// Get Club Name
														$club_name = get_club_name($tr_entry['club_id']);

														// Non-profile fields
														if ($tr_entry['profile_disabled'] == '1')
															$entrant_category = "Disabled";
														else
															$entrant_category = $has_entry_details ? safe($tr_entry['entrant_category']) : "Non-Participant";
														$fee_code = $has_entry_details ? safe($tr_entry['fee_code']) : "";
														$digital_sections = $has_entry_details ? safe($tr_entry['digital_sections'], 0) : 0;
														$print_sections = $has_entry_details ? safe($tr_entry['print_sections'], 0) : 0;
														$awards = $has_entry_details ? safe($tr_entry['awards'], 0) : 0;
														$hms = $has_entry_details ? safe($tr_entry['hms'], 0) : 0;
														$acceptances = $has_entry_details ? safe($tr_entry['acceptances'], 0) : 0;
														$currency = $has_entry_details ? safe($tr_entry['country_id'] == '101' ? "INR" : "USD") : "";
														$fees_payable = $has_entry_details ? safe($tr_entry['fees_payable'], 0.0) : 0.0;
														$discount_applicable = $has_entry_details ? safe($tr_entry['discount_applicable'], 0.0) : 0.0;
														$payment_received = $has_entry_details ? safe($tr_entry['payment_received'], 0.0) : 0.0;
														$date_of_birth = safe($tr_entry['date_of_birth']);
														$bank_account = $tr_entry['bank_account_number'];
														$num_uploads = $has_entry_details ? get_num_uploads($yearmonth, $profile_id) : 0;
														list($catalog_dues, $catalog_currency) = get_catalog_dues($yearmonth, $profile_id);
														// Determine wheather to display the row or hide the row
														if ($cr_details['send_mail']) {
															$row_visible = false;
															if ($cr_include_indians && $cr_include_foreigners)
																$row_visible = true;
															elseif ($cr_include_indians && $tr_entry['country_id'] == 101)
																$row_visible = true;
															elseif ($cr_include_foreigners && $tr_entry['country_id'] != 101)
																$row_visible = true;
															if ($tr_entry['yps_login_id'] != "" && (! $cr_include_yps_members))
																$row_visible = false;
														}
														else
															$row_visible = true;

														// Skip Mail
														$skip_reason = "";
														if (isset($mail_skip_list[$profile_id])) {
															$skip_reason = $mail_skip_list[$profile_id];
														}
												?>

													<tr <?= $row_visible ? "" : "style='display: none'";?> >
														<td>
															<input name="checkbox[]" type="checkbox" id="row-<?= $row_no;?>"
																	value="<?= $profile_id . "|" . $tr_entry['email'];?>"
																	class="row-selector"
																	data-row-no="<?= $row_no;?>"
																	data-profile-id="<?= $profile_id;?>"
																	data-email="<?= $tr_entry['email'];?>"
																	data-country="<?= $tr_entry['country_id'];?>"
																	data-yps-member-id="<?= $tr_entry['yps_login_id'];?>"
																	data-skip-reason="<?= $skip_reason;?>"
																	data-row-visible="<?= $row_visible ? '1' : '0';?>" >
															<?php printf(" %4d %s ", $row_no, $tr_entry['sortname']);?>
															<br>
															<span id="skip-reason-<?= $profile_id;?>">
																<?= $skip_reason;?>
															</span>
														</td>
														<td><?= $entrant_category;?><br><?= $fee_code;?><br><?= $date_of_birth;?></td>
														<td>
															<?= $tr_entry['profile_name'];?>
															<?= ($tr_entry['yps_login_id'] == "") ? "" : "(" . $tr_entry['yps_login_id'] . ")";?>
															<?= $club_name == "" ? "" : "<br>" . $club_name;?>
															<br>
															<?= $tr_entry['email'] . ", " . $tr_entry['phone'];?>
														</td>
														<td><?= $has_entry_details ? "DG: " . $digital_sections . ", PR: " . $print_sections : "NA";?></td>
														<td class="text-center"><?= $num_uploads;?></td>
														<td><?= "AW:" . $awards . ", HM:" . $hms . ", AC:" . $acceptances;?></td>
														<td><?= sprintf("%.02f %s", $fees_payable - $discount_applicable - $payment_received, $currency);?></td>
														<td><?= sprintf("%.02f %s", $catalog_dues, $catalog_currency);?></td>
														<?php
															if (isset($cr_details['show']) && $cr_details['show'] == "AWARD") {
														?>
														<td>
															<div id="<?= $profile_id;?>-AWARD">
															<?php
																if (($awards + $hms) > 0) {
																	if(isset($tracking_data[$profile_id . "|AWARD"])) {
																		$td = $tracking_data[$profile_id . "|AWARD"];
															?>
																<?= $td['date_of_posting'];?>
																<br><?= get_name($couriers, $td['courier']);?>
																<br><?= $td['tracking_number'];?>
															<?php
																	}
																}
															?>
															</div>
														</td>
														<?php
															}
														?>
														<!-- conditional columns -->
														<?php
															if ($show_cash_award_column && isset($cr_details['show']) && $cr_details['show'] == "CASH") {
														?>
														<td>
															<div id="<?= $profile_id;?>-CASH">
															<?php
																if (isset($cash_award_recipient_list[$profile_id])) {
																	if (isset($tracking_data[$profile_id . "|CASH"])) {
																		$td = $tracking_data[$profile_id . "|CASH"];
															?>
																<?= $td['date_of_posting'];?>
																<br><?= $currency . " " . $td['amount_remitted'];?>
																<br><?= $td['account_number'];?>
															<?php
																	}
																}
															?>
															</div>
														</td>
														<?php
															}
														?>
														<?php
															if ($show_catalog_column && isset($cr_details['show']) && $cr_details['show'] == "CATALOG") {
														?>
														<td>
															<div id="<?= $profile_id;?>-CATALOG">
															<?php
																if (in_array($profile_id, $catalog_ordered_participants)) {
																	if (isset($tracking_data[$profile_id . "|CATALOG"])) {
																		$td = $tracking_data[$profile_id . "|CATALOG"];
															?>
																<?= $td['date_of_posting'];?>
																<br><?= get_name($couriers, $td['courier']);?>
																<br><?= $td['tracking_number'];?>
															<?php
																	}
																}
															?>
															</div>
														</td>
														<?php
															}
														?>
													</tr>
												<?php
													}
												?>
												</tbody>
												</table>
											</div>
										</div>
									</form>
								</div>
							</div>
							<?php
								}
							?>
						</div> <!-- panel-body -->
					</div>	<!-- panel -->
				</div>	<!-- col-sm-12 -->
			</div>	<!-- row -->

			<!-- MODAL Forms -->
			<!-- BLOB -->
			<?php include("inc/blob_modal_html.php");?>
			<!-- END OF BLOB -->

			<!-- POSTING DIALOG -->
			<?php // include("inc/posting_modal_html.php");?>
			<!-- END OF POSTING DIALOG -->

			<!-- END OF MODAL FORMS -->

		</div>	<!-- content -->
	</div>  <!-- wrapper -->

<?php
      include("inc/footer.php");
?>


<!-- DataTables -->
<script src="plugin/datatable/js/jquery.dataTables.min.js"></script>
<script src="plugin/datatable/js/dataTables.bootstrap.min.js"></script>

<!-- DataTables buttons scripts -->
<script src="plugin/datatables.net-buttons/js/buttons.html5.min.js"></script>
<script src="plugin/datatables.net-buttons/js/buttons.print.min.js"></script>
<script src="plugin/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
<script src="plugin/datatables.net-buttons-bs/js/buttons.bootstrap.min.js"></script>

<!-- Form Validation -->
<script src="plugin/validate/js/jquery.validate.min.js"></script>
<!-- Custom Validation Functions -->
<script src="custom/js/validate.js"></script>

<?php include("inc/blob_modal_script.php");?>

<?php // include("inc/posting_modal_script.php");?>


<!-- Ajax Functions -->
<!-- Edit Description Action Handlers -->
<script>
	// Get Description Text from server
	$(document).ready(function(){

		// Load picture into view
		$("#mail-banner").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#mail-banner-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});
		$("#mail-footer").on("change", function(){
			let input = $(this).get(0);
			if (input.files && input.files.length > 0 && input.files[0] != "") {
				// Phase 1 Check Size & Dimensions
				// var file_size = input.files[0].size;
				var reader = new FileReader();
				// Handler for file read completion
				reader.onload = function (e) {
					$("#mail-footer-disp").attr("src", e.target.result);
				}
				// Handler for File Read Error
				reader.onerror = function(e) {
					alert("Unable to open selected picture");
				}

				// Perform File Read
				reader.readAsDataURL(input.files[0]);

			}
		});

		// Ajax Upload
		$("#upload-mail-banner").click(function(e){
			e.preventDefault();
			if ($("#mail-banner").val() == "") {
				swal("Select a Banner Image !", "Please select a banner image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = $(this).attr("data-file");
				let file = $("#mail-banner")[0].files[0];
				let stub = $("#mail-banner").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
			}
		});		// upload image click
		$("#upload-mail-footer").click(function(e){
			e.preventDefault();
			if ($("#mail-footer").val() == "") {
				swal("Select a Footer Image !", "Please select a footer image to upload.", "warning");
			}
			else {
				let yearmonth = "<?= $yearmonth;?>";
				let file_name = $(this).attr("data-file");
				let file = $("#mail-footer")[0].files[0];
				let stub = $("#mail-footer").attr("name");
				let formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("file_name", file_name);
				formData.append(stub, file);

				$('#loader_img').show();
				$.ajax({
						url: "ajax/upload_salon_img.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Image Saved",
										text: "Image has been uploaded and saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
							}
							else{
								swal({
										title: "Upload Failed",
										text: "Uploaded image could not be saved: " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
			}
		});		// upload image click



	});		// document.ready

</script>

<script>

    $(document).ready(function () {

		// Show visible rows and hide non visible rows
		// $.fn.dataTable.ext.search.push(function(settings, data, index) {
		// 	if ( /[0-9]+/.exec(data[0]) == null)
		// 		return true;
		// 	else {
		// 		let row_no = /[0-9]+/.exec(data[0])[0];
		// 		return ($("#row-" + row_no).attr("data-row-visible") == '1');
		// 	}
		// 	// return $(data[0]).filter("input.row-selector").attr("data-row-visible") == '1';
		// });
		//
        // $('#entry_table').DataTable();

		// $("#login_filter").on('keyup change', function() {
		// 	table.column(0).search(this.value)
		// 					.draw();
		// });
		//
		// $("#category_filter").on('keyup change', function() {
		// 	table.column(1).search(this.value)
		// 					.draw();
		// });
		//
		// $("#name_filter").on('keyup change', function() {
		// 	table.column(2).search(this.value)
		// 					.draw();
		// });
		//
		// $("#participation_code_filter").on('keyup change', function() {
		// 	table.column(3).search(this.value)
		// 					.draw();
		// });
	});

</script>

<script>
	$(document).ready(function() {
		// Hide Form till a salon is loaded
		let yearmonth = "<?= $yearmonth;?>";
		if(yearmonth == 0)
			$(".content").hide();

		// Show visible rows and hide non visible rows
		$.fn.dataTable.ext.search.push(function(settings, data, index) {
			let table = $("#entry_table").DataTable();
			if ( /[0-9]+/.exec(data[0]) == null)
				return true;
			else {
				let row_no = /[0-9]+/.exec(data[0])[0];
				return (table.$("#row-" + row_no).attr("data-row-visible") == '1');
			}
			// return $(data[0]).filter("input.row-selector").attr("data-row-visible") == '1';
		});

		var table = $('#entry_table').DataTable();

		// Select rows for queuing
		// Select None - Click handler is used to uncheck manually selected rows too
		$("#select_none").click(function(){
			// let table = $("#entry_table").DataTable();
			table.$(".row-selector").prop("checked", false);
			$("#num-profiles-selected").html("[ " + table.$(".row-selector:checked").length + " selected ]");
		});
		// Select All
		$("#select_all").change(function(){
			// $(".row-selector").prop("checked", false);
			// if ($(this).is(":checked"))
			// $(".row-selector").prop("checked", true);
			// let table = $("#entry_table").DataTable();
			table.$(".row-selector").filter("[data-row-visible='1']").prop("checked", true);
			$("#num-profiles-selected").html("[ " + table.$(".row-selector:checked").length + " selected ]");
		});
		// Select All on Page
		$("#select_all_on_page").change(function(){
			// let table = $("#entry_table").DataTable();
			table.$(".row-selector[data-row-visible='1']", {page: 'current'}).prop("checked", true);
			$("#num-profiles-selected").html("[ " + table.$(".row-selector:checked").length + " selected ]");
		});
		// Update Selected Count
		table.$(".row-selector").on("change", function (){
			// let table = $("#entry_table").DataTable();
			$("#num-profiles-selected").html("[ " + table.$(".row-selector:checked").length + " selected ]");
		});

		// Control row visibility - Works on the entire table
		// Common functions for call from multiple event handlers
		function include_all(include_yps_members) {
			// $("#entry_table").DataTable().destroy();
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").attr("data-row-visible", "1");
			}
			table.draw();
			// Make checked rows visible
			// table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			// table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			// $("#entry_table").DataTable();
		}
		function include_indians(include_yps_members) {
			// $("#entry_table").DataTable().destroy();
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").filter("[data-country='101']").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").filter("[data-country='101']").attr("data-row-visible", "1");
			}
			table.draw();
			// Make checked rows visible
			// table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			// table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			// $("#entry_table").DataTable();
		}
		function include_foreigners(include_yps_members) {
			// $("#entry_table").DataTable().destroy();
			table.$(".row-selector").attr("data-row-visible", "0");		// Turn off visibility for all rows
			if (include_yps_members) {
				table.$(".row-selector").filter("[data-country!='101']").attr("data-row-visible", "1");
			}
			else {
				table.$(".row-selector[data-yps-member-id='']").filter("[data-country!='101']").attr("data-row-visible", "1");
			}
			table.draw();
			// Make checked rows visible
			// table.$(".row-selector[data-row-visible='1']").parent("td").parent("tr").show();
			// table.$(".row-selector[data-row-visible='0']").parent("td").parent("tr").hide();
			// $("#entry_table").DataTable();
		}
		// Include all (yps members are included only when checked)
		$("#include_all").change(function(){
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_all(include_yps_members);
		});

		// Include Indians
		$("#include_indians").change(function(){
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_indians(include_yps_members);
		});

		// Include Foreigners
		$("#include_foreigners").change(function(){
			let include_yps_members = $("#include_yps_members").prop("checked");
			include_foreigners(include_yps_members);
		});

		// Handle toggling of YPS Member Inclusion
		$("#include_yps_members").change(function(){
			let include_yps_members = $("#include_yps_members").prop("checked");
			if ($("#include_indians").prop("checked"))
				include_indians(include_yps_members);
			else if($("#include_foreigners").prop("checked"))
				include_foreigners(include_yps_members);
			else
				include_all(include_yps_members);
		});

	});
</script>

<!-- Create Mail Queue -->
<script>
	function list_mail_queue(qlist) {
		let html = "";
		if (qlist.length == 0) {
			html = "<p>Mail Queue is empty</p>";
		}
		else {
			for (let idx = 0; idx < qlist.length; ++ idx) {
				let item = qlist[idx];
				html += "<p>";
				html += item.report_date;
				html += " " + item.report_name;
				html += " (" + (Number(item.sent) + Number(item.skipped) + Number(item.failed)) + " / " + item.booked + ")";
				html += " " + item.status;
				html += " <a class='delete-queue' data-queue-id='" + item.queue_id + "' >";
				html += "<span style='color: red;'><i class='fa fa-trash'></i></span>";
				html += "</a>";
				html += "</p>";
			}
		}
		$("#mail-queue-display").html(html);
		// Refresh delete queue handler
		$(".delete-queue").click(function(){
			let queue_id = $(this).data("queue-id");
			delete_queue(queue_id);
		});		// delete-queue.click
	}

	function delete_queue(queue_id) {
		// Confirmation to delete queue
		swal({
			title: 'DELETE Confirmation',
			text:  "Do you want to delete the queue ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (delete_confirmed) {
			if (delete_confirmed) {
				let formData = new FormData();
				formData.append("queue_id", queue_id);
				$('#loader_img').show();
				$.ajax({
						url: "ajax/delete_mail_queue.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Deleted",
										text: "Mail Queue has been deleted successfully",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Refresh Mail Queue
								list_mail_queue(response.mail_queue);
								// window.location(window.location.href + "?yearmonth=" + yearmonth + "&report_name=" + report_name));
							}
							else{
								swal({
										title: "Save Failed",
										text: "Mail Queue could not be created " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});		// ajax
			}	// delete_confirmed
		});		// swal.then

	}
	// Delete Queue Handler
	$(document).ready(function(){
		$(".delete-queue").click(function(){
			let queue_id = $(this).data("queue-id");
			delete_queue(queue_id);
		});		// delete-queue.click
	});		// document.ready

	// Create Queue Handler
	$(document).ready(function(){
		let vaidator = $('#mail-queue-form').validate({
			rules:{
			},
			messages:{
			},
			errorElement: "div",
			errorClass: "valid-error",
			showErrors: function(errorMap, errorList) {
							$("div.valid-error").remove();		// Remove all error messages to avoid duplicate messages
							this.defaultShowErrors();			// Show Errors
						},
			submitHandler: function(form) {
				// Validations
				if ($("#send-test-email:checked").length > 0 && $("#test-emails").val() == "") {
					swal("Missing Data", "Test option selected but test Emails not provided for sending", "error");
					return false;
				}
				let table = $("#entry_table").DataTable();
				if (table.$(".row-selector:checked").length == 0) {
					swal("No Selection", "You have not selected any profiles for sending emails", "error");
					return false;
				}

				// Assemble Data
				let yearmonth = $("#yearmonth").val();
				let report_name = $("#report-name").val();
				var formData = new FormData();
				formData.append("yearmonth", yearmonth);
				formData.append("report_name", report_name);
				let cr_details = JSON.parse($("#report-details").val());
				formData.append("subject", cr_details.subject);
				formData.append("header_image", cr_details.header_image);
				formData.append("footer_image", cr_details.footer_image);
				formData.append("template", cr_details.template);
				formData.append("generator", cr_details.generator);
				formData.append("is_test", $("#send-test-email:checked").length > 0 ? "1" : "0");
				formData.append("test_emails", $("#test-emails").val());
				formData.append("no_cc", $("#no-cc:checked").length > 0 ? "1" : "0");
				if ($("#no-cc:checked").length > 0)
					formData.append("cc_to", "");
				else
					formData.append("cc_to", "salon@ypsbengaluru.in");
				let profile_ids = "";
				table.$(".row-selector:checked").each(function(){
					if (profile_ids == "")
						profile_ids = $(this).data("profile-id");
					else
						profile_ids += "," + $(this).data("profile-id");
				});
				formData.append("profile_id_list", profile_ids);


				$('#loader_img').show();
				$.ajax({
						url: "ajax/create_mail_queue.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: "Details Saved",
										text: "Mail Queue has been created successfully",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Turn off all selected profiles
								table.$(".row-selector").prop("checked", false);
								$("#num-profiles-selected").html("");
								// Refresh Mail Queue
								list_mail_queue(response.mail_queue);
								// window.location(window.location.href + "?yearmonth=" + yearmonth + "&report_name=" + report_name));
							}
							else{
								swal({
										title: "Save Failed",
										text: "Mail Queue could not be created " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});
				return false;
			},
		});
	});

	// Refresh Queue Status
	$(document).ready(function(){
		$("#refresh-queue").click(function(){
			$('#loader_img').show();
			$.ajax({
					url: "ajax/get_mail_queue.php",
					type: "POST",
					cache: false,
					processData: false,
					contentType: false,
					success: function(response) {
						$('#loader_img').hide();
						response = JSON.parse(response);
						if(response.success){
							// Refresh Mail Queue
							list_mail_queue(response.mail_queue);
							// window.location(window.location.href + "?yearmonth=" + yearmonth + "&report_name=" + report_name));
						}
						else{
							swal({
									title: "Request Failed",
									text: "Mail Queue could not be refreshed " + response.msg,
									icon: "warning",
									confirmButtonClass: 'btn-warning',
									confirmButtonText: 'OK'
							});
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						$('#loader_img').hide();
						swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
					}
			});		// ajax
		});
	});

</script>



<script language="javascript">
function validate() {
	var chks = document.getElementsByName('checkbox[]');
	var hasChecked = false;
	for (var i = 0; i < chks.length; i++){
		if (chks[i].checked){
			hasChecked = true;
			break;
		}
	}
	if (hasChecked == false){
		swal("Selection Required !", "Please select at least one participant to send mail.", "warning");
		return false;
	}
	return true;
}
</script>


<!-- MAIL SKIP Functions -->
<script>

	$(document).ready(function(){
		$(".mail-skip").click(function(e) {
			e.preventDefault();
			let mail_operation = $(this).attr("data-skip-reason");
			let table = $("#entry_table").DataTable();
			let profile_list = [];
			if (table.$(".row-selector:checked").length == 0) {
				swal("No Selection", "No profile has been selected for the operation", "error");
				return;
			}
			table.$(".row-selector:checked").each(function(profile_id){
				profile_list[profile_list.length] = $(this).data("profile-id");
			});
			register_mail_operation(mail_operation, profile_list);
		});		// delete-queue.click
	});		// document.ready

	function update_profile_status(mail_operation, profile_list) {
		let table = $("#entry_table").DataTable();
		profile_list.forEach(function(profile_id) {
			table.$("[data-profile-id='" + profile_id + "']").attr("data-skip-reason", (mail_operation == "RESUME" ? "" : mail_operation));
			table.$("[data-profile-id='" + profile_id + "']").prop("checked", false);
			table.$("#skip-reason-" + profile_id).html(mail_operation == "RESUME" ? "" : mail_operation);
			$("#num-profiles-selected").html("[ " + table.$(".row-selector:checked").length + " selected ]");
		});
	}

	function register_mail_operation(mail_operation, profile_list) {
		// Confirmation to delete queue
		swal({
			title: mail_operation + ' Confirmation',
			text:  "Do you want to " + mail_operation + " email functions to the selected profiles ?",
			icon: "warning",
			buttons: true,
			dangerMode: true,
		})
		.then(function (operation_confirmed) {
			if (operation_confirmed) {
				let formData = new FormData();
				formData.append("operation", mail_operation);
				formData.append("profiles", profile_list.join(","));
				$('#loader_img').show();
				$.ajax({
						url: "ajax/save_mail_operation.php",
						type: "POST",
						data: formData,
						cache: false,
						processData: false,
						contentType: false,
						success: function(response) {
							$('#loader_img').hide();
							response = JSON.parse(response);
							if(response.success){
								swal({
										title: mail_operation + " request Saved",
										text: mail_operation + " request for the selected profiles has been saved.",
										icon: "success",
										confirmButtonClass: 'btn-success',
										confirmButtonText: 'Great'
								});
								// Refresh Mail Queue
								update_profile_status(mail_operation, profile_list);
							}
							else{
								swal({
										title: "Save Failed",
										text: mail_operation + " request could not be created " + response.msg,
										icon: "warning",
										confirmButtonClass: 'btn-warning',
										confirmButtonText: 'OK'
								});
							}
						},
						error: function(jqXHR, textStatus, errorThrown) {
							$('#loader_img').hide();
							swal("Server Error", "Something went wrong at the server. If this repeats please report the problem to YPS", "error");
						}
				});		// ajax
			}	// delete_confirmed
		});		// swal.then
	}
</script>

</body>

</html>
<?php
}
else
{
	header("Location: " . $_SERVER['HTTP_REFERER']);
	print("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
	//header("Location: /jurypanel/index.php");
	//printf("<script>location.href='/jurypanel/index.php'</script>");
}

?>
