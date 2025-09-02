<?php
include("../inc/connect.php");
include("lib.php");

// Default Timezone
date_default_timezone_set("Asia/Kolkata");

// Global Parameters specific to this Salon
define("SALON_HAS_CASH_AWARDS", false);
define("MAX_LEVEL_FOR_FULL_UPLOAD", 9);
define("SALON_ROOT", http_method() . $_SERVER['SERVER_NAME']);
define("MAX_TOKEN_LENGTH", 32);
define("YPS_WEBSITE", "https://ypsbengaluru.com");

$mail_failed = "";
$count = 0;
$counterr = 0;
$skipped = 0;
$mail_suffix = 0;

$has_individual_awards = false;
$has_special_pic_awards = false;
$has_picture_awards = false;
$has_picture_acceptances = false;



// General Reminder mail related functions
function send_upload_mail($profile_id) {
	global $contest;
	global $DBCON;
	global $admin_yearmonth;
	global $partner_data;
	global $test_email;
	global $no_cc;

	// Detemine Upload last dates
	$sql = "SELECT MIN(submission_last_date) AS start_date, MAX(submission_last_date) AS end_date FROM section WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query);
	if ($row['start_date'] == $row['end_date'])
		$last_upload = pdate($row['start_date']);
	else
		$last_upload = pdate($row['start_date']) . " - " . pdate($row['end_date']);


	$sql = "SELECT * FROM entry, profile WHERE entry.yearmonth = '$admin_yearmonth' AND entry.profile_id = '$profile_id' AND profile.profile_id = entry.profile_id ";
	$entry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$row = mysqli_fetch_array($entry);
	$part_name = $row['profile_name'];
	$email = ($test_email == "" ? $row['email'] : $test_email);

	$banner_file = SALON_ROOT . "/salons/$admin_yearmonth/img/salon_open_mail.png";

	$template = file_exists("../../salons/$admin_yearmonth/blob/helpmail_msg.htm") ? "../../salons/$admin_yearmonth/blob/helpmail_msg.htm" : "template/helpmail_msg.htm";

	$message = load_message($template,
								array(	"server-address" => SALON_ROOT,
										"banner-image" => $banner_file,
										"participant-name" => $part_name,
										"last-date" => $last_upload,
										"salon-name" =>$contest['contest_name'],
										"registration-last-date" => pdate($contest['registration_last_date']),
									 	"partner-data" => $partner_data ));

	$subject = $contest['contest_name'] . " - Upload by " . $last_upload;

	send_mail($email, $subject, $message, $no_cc);

}

// Club Member Reminder Mail
function send_club_reminder($profile_id, $email, $club) {
	global $contest;
	global $DBCON;
	global $partner_data;
	global $test_email;
	global $no_cc;

	if ($profile_id != 0) {
		$sql = "SELECT * FROM profile WHERE profile_id = '$profile_id'";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$participant_name = $row['profile_name'];
	}
	else
		$participant_name = $club['club_name'] . " member";

	$values = array(
				"server-name" => SALON_ROOT,
				"participant-name" => $participant_name,
				"club-name" => $club['club_name'],
				"club-contact-name" => $club['profile_name'],
				"last-date" => $contest['registration_last_date'],
				"salon-name" =>$contest['contest_name'],
				"partner-data" => $partner_data
			 );

	$message = load_message("template/club_member_reminder.htm", $values);

	$subject = "Registration pending for " . $contest['contest_name'];

	$to = ($test_email == "" ? $email : $test_email);
	send_mail($to, $subject, $message , $club['email'], $no_cc);

}

// RESULTS MAIL related functions

function generate_profile ($profile_id) {
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;

// 	$template = <<<TEMPLATE
// <table class='table-data'>
// <tbody>
// <tr>	<td><p><b>Name</b></p></td>			<td><p><b>[profile_name]</b></p></td>	</tr>
// <tr>	<td><p><b>Category</b></p></td>		<td><p>[entrant_category]</p></td>		</tr>
// <tr>	<td><p><b>Honors</b></p></td>		<td><p>[honors]</p></td>				</tr>
// <tr>	<td><p><b>Club</b></p></td>			<td><p>[club]</p></td>					</tr>
// <tr>	<td><p><b>Address</b></p></td>		<td><p>[address]</p></td>				</tr>
// <tr>	<td><p><b>Phone</b></p></td>		<td><p>[phone]</p></td>					</tr>
// </tbody>
// </table>
// TEMPLATE;

	$template = <<<TEMPLATE
<p><b>[profile_name]</b></p>
<p>[address]</p>
<p>Ph:[phone]</p>
TEMPLATE;

	$sql = "SELECT * ";
	$sql .= " FROM country, profile ";
	if ($contest_archived)
		$sql .= " LEFT JOIN ar_entry entry ON entry.yearmonth = '$admin_yearmonth' AND entry.profile_id = profile.profile_id ";
	else
		$sql .= " LEFT JOIN entry ON entry.yearmonth = '$admin_yearmonth' AND entry.profile_id = profile.profile_id ";
	$sql .= " LEFT JOIN club ON club.club_id = profile.club_id ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND profile.country_id = country.country_id ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		$address = $res['address_1'];
		if ($res['address_2'] != "")
			$address .= "<br>" . $res['address_2'];
		if ($res['address_3'] != "")
			$address .= "<br>" . $res['address_3'];
		$address .= "<br>" . $res['city'];
		$address .= "<br>" . $res['state'] . " - " . $res['pin'];
		$address .= "<br>" . $res['country_name'];

		$values = array(
					"profile_name" => $res['profile_name'],
					"entrant_category" => $res['entrant_category'],
					"honors" => $res['honors'],
					"club" => ($res['yps_login_id'] == "" ? $res['club_name'] : "Youth Photographic Society"),
					"address" => $address,
					"phone" => $res['phone'] );

		return replace_values($template, $values);
	}
	else
		return "Unable to fetch details for $profile_id";
}

function generate_individual_award($profile_id) {
	global $DBCON;
	global $admin_yearmonth;

	$sql = "SELECT * FROM award, entry_result, profile ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND entry_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry_result.profile_id = profile.profile_id ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.award_type = 'entry' ";
	// $sql .= "   AND award.award_type IN ('entry', 'pic') ";

	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$m = "<h2>Individual Awards</h2>";
	$m .= "<table class='table-data'><tbody>";
	while ($res = mysqli_fetch_array($qry)) {
		$m .= "<tr><td>Award Name</td><td><b>" . $res['award_name'] . "</b></td></tr>";
	}
	$m .= "</tbody></table>";
	return $m;
}

function generate_special_pic_award($profile_id, $include_instructions = true) {
	global $contest;
	global $DBCON;
	global $admin_yearmonth;
	global $contest;
	global $contest_archived;


	$row_template = <<<TEMPLATE
<tr>
	<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
	<td>
		<p><b>[title]</b></p><br>
		<p>has won <b>[award_name]</b></p><br>
		[upload_link]
	</td>
</tr>
TEMPLATE;

	if ($contest_archived)
		$sql = "SELECT * FROM ar_pic_result pic_result, award, profile, ar_pic pic, section ";
	else
		$sql = "SELECT * FROM pic_result, award, profile, pic, section ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section = 'CONTEST' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$m = "<br><h2>Special Picture Awards</h2>";
	if ($contest['has_exhibition']) {
		if ($include_instructions) {
			$m .= <<<HTML
					<p>YPS requires <b>Full Resolution JPEG files of pictures winning Awards/Honorable Mentions</b>
						for publishing in catalog. They are also used for printing and display at the Salon Exhibition.
						YPS prefers <b>3600 x 5400 pixels sRGB JPEGs</b> for the 12x18 prints at 300 DPI.
						Click on the <b>UPLOAD PICTURE</b> link next to each of your award winning pictures
						and upload your high-resolution pictures directly to the Salon website. You can also do this
						from <b>My Results Page</b> on the Salon website.
					</p>
HTML;


			if ($admin_yearmonth == '202008' || $admin_yearmonth == '202012') {
				$m .= "<br><p><b>Note:</b> Due to the pandemic, we are uncertain whether we will be able to exhibit prints in a ";
				$m .= "physical exhibition. But we will still need high resolution files for the Catalog.  We will ";
				$m .= "keep the salon website updated on the status of the exhibition.</p>";
			}
		}
	}
	$m .= "<table class='table-data'><tbody>";

	while ($res = mysqli_fetch_array($qry)) {
		$upload_code = encode_string_array($admin_yearmonth . "|" . $res['award_id'] . "|" . $profile_id . "|" . $res['pic_id']);
		$upload_link = "";
		if ($res['section_type'] == "P")
			$upload_link = "<p style='text-align:center; color: gray;'>NO UPLOAD REQUIRED</p>";
		else {
			if ($res['full_picfile'] == "")
				$upload_link = "<p style='text-align:center;'><a href='" . SALON_ROOT . "/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD PICTURE</a></p>";
			else
				$upload_link = "<p style='text-align:center; color: gray;'>ALREADY UPLOADED</p>";
		}

		$values = array (
					"thumbnail" => SALON_ROOT . "/salons/$admin_yearmonth/upload/" . $res['section'] . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"award_name" => $res['award_name'],
					"upload_link" => $upload_link
				);
		$m .= replace_values($row_template, $values);

	}
	$m .= "</tbody></table>";
	return $m;
}


function generate_awards($profile_id, $include_instructions = true) {
	global $contest;
	global $DBCON;
	global $admin_yearmonth;
	global $contest;
	global $contest_archived;

	$row_template = <<<TEMPLATE
<tr>
	<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
	<td>
		<p><b>[title]</b></p><br><p>Total Score: [total_rating] / 15<p>
	</td>
	<td>
		<p><u>[pic_section]</u></p><p><b>[award_name]</b></p><br>
		[upload_link]
	</td>
</tr>
TEMPLATE;

	if ($contest_archived)
		$sql = "SELECT * FROM ar_pic_result pic_result, award, profile, ar_pic pic, section ";
	else
		$sql = "SELECT * FROM pic_result, award, profile, pic, section ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level != 99 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND section.yearmonth = pic.yearmonth ";
	$sql .= "   AND section.section = pic.section ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= " ORDER BY award.section, award.level ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$m = "<br><h3>Pictures winning Awards</h3>";
// 	if ($contest['has_exhibition']) {
// 			$m .= <<<HTML
// 					<p>YPS requires <b>Full Resolution JPEG files of pictures winning Awards/Honorable Mentions</b>
// 						for publishing in catalog. They are also used for printing and display at the Salon Exhibition.
// 						YPS prefers <b>3600 x 5400 pixels sRGB JPEGs</b> for the 12x18 prints at 300 DPI.
// 						Click on the <b>UPLOAD PICTURE</b> link next to each of your award winning pictures
// 						and upload your high-resolution pictures directly to the Salon website. You can also do this
// 						from <b>My Results Page</b> on the Salon website.
// 					</p>
// HTML;
//
//
// 			if ($admin_yearmonth == '202008' || $admin_yearmonth == '202012') {
// 				$m .= "<br><p><b>Note:</b> Due to the pandemic, we are uncertain whether we will be able to exhibit prints in a ";
// 				$m .= "physical exhibition. But we will still need high resolution files for the Catalog.  We will ";
// 				$m .= "keep the salon website updated on the status of the exhibition.</p>";
// 			}
// 	}
	$m .= "<table class='table-data'><tbody>";

	while ($res = mysqli_fetch_array($qry)) {
		$upload_code = encode_string_array($admin_yearmonth . "|" . $res['award_id'] . "|" . $profile_id . "|" . $res['pic_id']);
		$upload_link = "";
		if ($res['section_type'] == "P")
			$upload_link = "<p style='text-align:center; color: gray;'>NO UPLOAD REQUIRED</p>";
		else {
			if ($res['full_picfile'] == "")
				$upload_link = "<p style='text-align:center;'><a href='" . SALON_ROOT . "/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD HI-RES PICTURE</a></p>";
			else
				$upload_link = "<p style='text-align:center; color: gray;'>ALREADY UPLOADED</p>";
		}
		$values = array (
					"thumbnail" => SALON_ROOT . "/salons/$admin_yearmonth/upload/" . $res['section'] . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"total_rating" => $res['total_rating'],
					"pic_section" => $res['section'],
					"award_name" => $res['award_name'],
					"upload_link" => $upload_link );
		$m .= replace_values($row_template, $values);

		// $tn_dir = SALON_ROOT . "/upload/" . $res['pic_section'] . "/tn/";
		// $pic_dir = SALON_ROOT . "/upload/" . $res['pic_section'] . "/";
		// $full_dir = SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/" . $res['pic_section'] . "/full/";
		// $upload_code = strrev(sprintf("%03d%04d", $entry_id, $res['pic_pic_id']));
		// $upload_code = encode_string_array($contest['yearmonth'] . "-" . $entry_id . "-" . $res['pic_pic_id']);
		// $m .= "<tr><td><img src='" . $tn_dir . $res['picfile'] . "' style='width:120px;height:auto' ></td>";
		// $m .= "<td><p><b>" . $res['title'] . "</b></p><br><p>Total Score: " . $res['total_rating'] . " / 15<p></td>";
		// $m .= "<td><p><u>" . $res['pic_section'] . "</u></p><p><b>" . $res['award_name'] . "</b></p><br>";
		// $m .= "<p style='text-align:center;'>Upload Code: <b>" . $res['eseq'] . "</b></p>";
		// $m .= "<p style='text-align:center;'><a href='" . SALON_ROOT . "/upload_awarded.php?code=" . $upload_code . "' style='color:red;' >UPLOAD PICTURE</a></p></td></tr>";
	}
	$m .= "</tbody></table>";
	return $m;
}

function generate_acceptances($profile_id) {
	global $contest;
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;

	$row_template = <<<TEMPLATE
<tr>
	<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
	<td><p><b>[title]</b></p><br><p>Total Score: [total_rating] / 15<p></td>
	<td><p><u>[pic_section]</u></p><p><b>[award_name]</b></p></td>
</tr>
TEMPLATE;

	if ($contest_archived)
		$sql = "SELECT * FROM ar_pic_result pic_result, award, profile, ar_pic pic ";
	else
		$sql = "SELECT * FROM pic_result, award, profile, pic ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level = 99 ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$sql .= "   AND profile.profile_id = pic_result.profile_id ";
	$sql .= " ORDER BY award.section, award.level ";

	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$m = "<br><h3>Pictures winning Acceptances</h3>";
	$m .= "<table class='table-data'><tbody>";

	while ($res = mysqli_fetch_array($qry)) {
		$values = array (
					"thumbnail" => SALON_ROOT . "/salons/$admin_yearmonth/upload/" . $res['section'] . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"total_rating" => $res['total_rating'],
					"pic_section" => $res['section'],
					"award_name" => $res['award_name']);
		$m .= replace_values($row_template, $values);
	}
	$m .= "</tbody></table>";

	return $m;
}

function generate_others($profile_id) {
	global $contest;
	global $has_picture_acceptances, $has_picture_awards;
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;

	$row_template = <<<TEMPLATE
<tr>
	<td><img src='[thumbnail]' style='width:120px;height:auto' ></td>
	<td>
		<p><u>[pic_section]</u></p>
		<p><b>[title]</b></p><br>
		<p>[rejection_text]</p>
		<p>Total Score: [total_rating] / 15<p></td>
</tr>
TEMPLATE;

	if ($contest_archived)
		$sql  = "SELECT * FROM profile, ar_pic pic ";
	else
		$sql  = "SELECT * FROM profile, pic ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND pic.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic.profile_id = profile.profile_id ";
	$sql .= "   AND pic.pic_id NOT IN ( ";
	if ($contest_archived)
		$sql .= "       SELECT pic_result.pic_id FROM ar_pic_result pic_result ";
	else
		$sql .= "       SELECT pic_result.pic_id FROM pic_result ";
	$sql .= "       WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "         AND pic_result.profile_id = '$profile_id' ";
	$sql .= "    ) ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	if ($has_picture_acceptances || $has_picture_awards)
		$m = "<br><h3>Total Scores for your other Pictures</h3>";
	else
		$m = "<br><h3>Total Scores for your Pictures</h3>";
	$m .= "<p>These pictures are <b>not accepted</b> for exhibition in this salon.</p>";
	$m .= "<table class='table-data'><tbody>";

	while ($res = mysqli_fetch_array($qry)) {
		if ($res['notifications'] != "") {
			$rejection_text = rejection_text($res['notifications']);
			if ($rejection_text != "")
				$rejection_text = "<b>Rejected :</b> " . $rejection_text;
		}
		else
			$rejection_text = "";
		$values = array (
					"thumbnail" => SALON_ROOT . "/salons/$admin_yearmonth/upload/" . $res['section'] . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"rejection_text" => $rejection_text,
					"total_rating" => $res['total_rating'],
					"pic_section" => $res['section'] );
		$m .= replace_values($row_template, $values);
		// $tn_dir = SALON_ROOT . "/upload/" . $res['pic_section'] . "/tn/";
		// $pic_dir = SALON_ROOT . "/upload/" . $res['pic_section'] . "/";
		// $full_dir = SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/" . $res['pic_section'] . "/full/";
		// $m .= "<tr><td><img src='" . $tn_dir . $res['picfile'] . "' style='width:120px;height:auto' ></td>";
		// $m .= "<td><p><u>" . $res['pic_section'] . "</u></p><p><b>" . $res['title'] . "</b></p><br><p>Total Score: " . $res['total_rating'] . " / 15<p></td></tr>";
	}
	$m .= "</tbody></table>";

	return $m;
}

function send_results_mail($profile_id) {

	global $contest;
	global $skipped;
	global $has_individual_awards, $has_special_pic_awards, $has_picture_acceptances, $has_picture_awards;
	global $recognition_data;
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;
	global $partner_data;
	global $test_email;
	global $no_cc;

	// [contest-data]
	$about = <<<CONTEST
		Thank you for participating in the [salon-name] hosted by <a href='[yps-website]'>Youth Photographic Society</a>, Bangalore, India.
		An Open Judging event was held between [judging-start-date] and [judging-end-date] to rate the pictures and select award winners. The results
		have been posted on the <a href='[results-link]'>[salon-name] Website</a>.
CONTEST;


	// if ($admin_yearmonth == '202008' || $admin_yearmonth == '202012' || $admin_yearmonth = '202108') {
	if ($contest['judging_mode'] == 'REMOTE') {
		$about .= "<br><br>A remote judging model was used for this salon and scores were displayed live through YPS Facebook Page and YPS Youtube Channel. ";
		$about .= "We thank all the jury and the hundreds of patrons who joined the Live webcasts and made the event a grand success. ";
	}
	else {
	}
	$about .= "<br><br>The [exhibition-name] is planned to be held from [exhibition-start-date] to [exhibition-end-date]. ";

	$values = array(
				"salon-name" => $contest['contest_name'],
				"registration-last-date" => pdate($contest['registration_last_date']),
				"judging-start-date" => pdate($contest['judging_start_date']),
				"judging-end-date" => pdate($contest['judging_end_date']),
				"results-link" => SALON_ROOT . "/results.php",
				"exhibition-name" => $contest['exhibition_name'],
				"exhibition-venue" => $contest['exhibition_venue'],
				"exhibition-start-date" => pdate($contest['exhibition_start_date']),
				"exhibition-end-date" => pdate($contest['exhibition_end_date']),
				"yps-website", YPS_WEBSITE);
	$contest_info = replace_values($about, $values);

	$sql  = "SELECT profile_name, entrant_category, email, uploads, bank_account_number ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_entry entry ";
	else
		$sql .= "  FROM profile, entry ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND entry.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry.profile_id = profile.profile_id ";
	$entry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($entry);

	// Do not send a report for people who have not uploaded
	if ($row['uploads'] == 0) {
		$skipped ++;
		return;
	}

	// Check submission status
	$sql = "SELECT COUNT(*) AS num_pics ";
	if ($contest_archived)
		$sql .= " FROM ar_pic pic ";
	else
		$sql .= " FROM pic ";
	$sql .= " WHERE yearmonth = '$admin_yearmonth' AND profile_id = '$profile_id' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry))
		$num_pics = $res['num_pics'];
	else
		$num_pics = 0;

	$money_transfer_required = false;
	$mailing_required = false;
	$has_individual_awards = false;
	$has_special_pic_awards = false;
	$has_picture_acceptances = false;
	$has_picture_awards = false;

	// check for individual entry awards
	$sql = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, ";
	$sql .= "      SUM(has_medal + has_pin + has_ribbon + has_memento + has_gift + has_certificate) AS num_to_mail ";
	$sql .= " FROM award, entry_result ";
	$sql .= " WHERE entry_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.award_type = 'entry' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if (! $has_individual_awards)
			$has_individual_awards = ($res['individual_awards'] > 0);
		if (! $money_transfer_required)
			$money_transfer_required = ($res['award_money'] > 0);
		if (! $mailing_required)
			$mailing_required = ($res['num_to_mail'] > 0);
	}

	// check for special picture awards
	$sql = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, ";
	$sql .= "      SUM(has_medal + has_pin + has_ribbon + has_memento + has_gift + has_certificate) AS num_to_mail ";
	if ($contest_archived)
		$sql .= " FROM award, ar_pic_result pic_result ";
	else
		$sql .= " FROM award, pic_result ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section = 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if (! $has_special_pic_awards)
			$has_special_pic_awards = ($res['individual_awards'] > 0);
		if (! $money_transfer_required)
			$money_transfer_required = ($res['award_money'] > 0);
		if (! $mailing_required)
			$mailing_required = ($res['num_to_mail'] > 0);
	}

	// check or picture awards
	$sql = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, ";
	$sql .= "      SUM(has_medal + has_pin + has_ribbon + has_memento + has_gift + has_certificate) AS num_to_mail ";
	if ($contest_archived)
		$sql .= " FROM ar_pic_result pic_result, award ";
	else
		$sql .= " FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level != 99 ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if (! $has_picture_awards)
			$has_picture_awards = ($res['individual_awards'] > 0);
		if (! $money_transfer_required)
			$money_transfer_required = ($res['award_money'] > 0);
		if (! $mailing_required)
			$mailing_required = ($res['num_to_mail'] > 0);
	}

	// check or picture acceptances
	$sql = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, ";
	$sql .= "      SUM(has_medal + has_pin + has_ribbon + has_memento + has_gift + has_certificate) AS num_to_mail ";
	if ($contest_archived)
		$sql .= " FROM ar_pic_result pic_result, award ";
	else
		$sql .= " FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "    AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level = 99 ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if (! $has_picture_acceptances)
			$has_picture_acceptances = ($res['individual_awards'] > 0);
		if (! $money_transfer_required)
			$money_transfer_required = ($res['award_money'] > 0);
		if (! $mailing_required)
			$mailing_required = ($res['num_to_mail'] > 0);
	}

	// Generate content for [download-actions]
	$code = encode_string_array("$admin_yearmonth|$profile_id");
	$msg  = "<h3>QUICK LINKS</h3>";
	$msg .= "<br><table class='table-grid'><tr>";
	$msg .= "<td width='50%'><a href='" . SALON_ROOT . "/user_results_from_email.php?code=$code'>";
	$msg .= "<img src='". SALON_ROOT . "/salons/$admin_yearmonth/img/share-results.jpg'></a></td>";
	$msg .= "<td><a href='" . SALON_ROOT . "/user_results_from_email.php?code=$code'>";
	$msg .= "<img src='". SALON_ROOT . "/salons/$admin_yearmonth/img/download-scorecard.jpg'></a></td>";
	$msg .= "</tr></table><br>";
	$download_actions = $msg;

	// Generate Congratulatory Message
	if ($has_individual_awards || $has_special_pic_awards || $has_picture_acceptances || $has_picture_awards) {
		$update_end_date = strtoupper(date("F d, Y", strtotime($contest['update_end_date'])));
		$m  = "<h3>COMPLETE FOLLOWING ACTIONS BEFORE " . $update_end_date . "</h3>";
		$m .= "<table class='table-data'>";
		if ($mailing_required) {
			$m .= "<tr><td width='30%'><h4>Verify and update your contact details</h4></td>";
			$m .= "<td>" . generate_profile($profile_id) . "</td></tr>";
		}
		if ($money_transfer_required ) {
			$m .= "<tr><td width='30%'><h4>Update your Bank Account details</h4></td>";
			$m .= "<td><p>Your award money will be transferred to your account after the exhibition. ";
			$m .= "Login and use Edit Profile option to update your Bank Account Number.</p></td></tr>";
		}
		if ( ($has_picture_awards && $contest['has_exhibition'] != 0) ) {
			$m .= "<tr><td width='30%'><h4>Upload Full Resolution Picture</h4></td>";
			$m .= "<td><p>We need full resolution JPEG files for pictures that have won awards. ";
			$m .= "Use the <b>UPLOAD PICTURE</b> link in the My Pictures section below to upload these files.</p></td></tr>";

			$m .= "<tr><td width='30%'><h4>Send Short Video</h4></td>";
			$m .= "<td><p>We will be happy to host a short video not <b>exceeding 20 seconds</b> explaining the story and the ";
			$m .= "thought process behind each of your awarded pictures. Send the videos to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a></p></td></tr>";
		}
		$m .= "</table>";

		$update_actions = $m;
	}
	else
		$update_actions = "";

	// Insert Banner image if exists
	$banner_file = SALON_ROOT . "/salons/" . $admin_yearmonth . "/img/results-mail-banner.jpg";
	if (file_exists("../../salons/$admin_yearmonth/img/results-mail-banner.jpg")) {
		$banner_html = "<tr><td colspan='2'><img src='" . $banner_file . "' style='max-width: 100%;'></td></tr>";
	}
	else
		$banner_html = "";

	list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

	$message = load_message("template/result_mail_msg.htm", array(
															"server-address" => SALON_ROOT,
															"banner-image" => $banner_html,
															"participant-name" => $row['profile_name'],
															"contest-data" => $contest_info,
															"download-actions" => $download_actions,
															"update-actions" => $update_actions,
															// "profile-data" => generate_profile($profile_id),
															"individual-data" => ($has_individual_awards ? generate_individual_award($profile_id) : ""),
														 	"special-pic-data" => ($has_special_pic_awards ? generate_special_pic_award($profile_id) : ""),
															"awards-data" => ($has_picture_awards ? generate_awards($profile_id) : ""),
															"acceptances-data" => ($has_picture_acceptances ? generate_acceptances($profile_id) : ""),
															"others-data" => generate_others($profile_id),
															"recognition-data" => $recognition_data,
															"contest-name" => $contest['contest_name'],
															"secretary-role" => $secretary_role,
														 	"salon-secretary" => $secretary_name,
															"partner-data" => $partner_data,
															"yps-website" => YPS_WEBSITE
														));

	$subject = $contest['contest_name'] . " - Results for " . $row['profile_name'];

	$to = ($test_email == "" ? $row['email'] : $test_email);
	send_mail($to, $subject, $message, $no_cc);

}

function generate_cert_links($profile_id) {
	global $contest;
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;

	$row_template = <<<TEMPLATE
<td><img src='[img-file]' style='width:80px;height:auto' ></td>
<td>
	<p><b>[title]</b></p><br><p><u>[section]</u></p><p><b>[award]</b></p>
	<p><a href='[cert-link]' style='color:red;' >Download Certificate</a></p>
</td>
TEMPLATE;

	if ($contest_archived)
		$sql = "SELECT * FROM ar_pic_result pic_result, award, ar_pic pic ";
	else
		$sql = "SELECT * FROM pic_result, award, pic ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND pic.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND pic.profile_id = pic_result.profile_id ";
	$sql .= "   AND pic.pic_id = pic_result.pic_id ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$m = "<br><h3>Download Certificates</h3>";
	$m .= "<table class='table-data'><tbody>";
	// $tn_dir = SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/" . $res['pic_section'] . "/tn/";
	// $pic_dir = SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/" . $res['pic_section'] . "/";
	// $full_dir = SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/" . $res['pic_section'] . "/full/";
	$idx = 0;
	while ($res = mysqli_fetch_array($qry)) {
		if (($idx % 2) == 0)
			$m .= "<tr>";
		++ $idx;
		// $upload_code = strrev(sprintf("%03d%04d", $entry_id, $res['pic_pic_id']));
		$upload_code = encode_string_array($admin_yearmonth . "|" . $res['award_id'] . "|" . $res['profile_id'] . "|" . $res['pic_id']);
		$values = array(
					"img-file" => SALON_ROOT . "/salons/" . $contest['yearmonth'] . "/upload/" . $res['section'] . "/tn/" . $res['picfile'],
					"title" => $res['title'],
					"section" => $res['section'],
					"award" => $res['award_name'],
					"eseq" => $res['eseq'],
					"cert-link" => SALON_ROOT . "/op/certificate.php?cert=" . $upload_code
					);
		$m .= replace_values($row_template, $values);
		if (($idx % 2) == 1)
			$m .= "<td width='4'></td>";
		else
			$m .= "</tr>";
	}
	if (($idx % 2) == 1)
		$m .= "</tr>";
	$m .= "</tbody></table>";

	return $m;
}


// Email Catalog & Certificates download links
function send_catalog_mail($profile_id) {
	global $DBCON;
	global $contest;
	global $skipped;
	global $has_individual_awards, $has_picture_acceptances, $has_picture_awards;
	global $recognition_data;
	global $admin_yearmonth;
	global $num_print_sections;
	global $contest_archived;
	global $partner_data;
	global $test_email;
	global $no_cc;

	$sql  = "SELECT profile_name, entrant_category, email, uploads, awards, hms, acceptances, bank_account_name, bank_account_number, bank_account_type, ";
	$sql .= "       bank_name, bank_branch, bank_ifsc_code ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_entry entry ";
	else
		$sql .= "  FROM profile, entry ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND entry.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry.profile_id = profile.profile_id ";
	$entry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($entry);


	// Do not send a report for people who have not uploaded
	if ($row['uploads'] == 0) {
		$skipped ++;
		return;
	}

	$contest_info = "";

	//echo "Stage 1<br>";
	// Assemble Contest Opening Paragraph
//	$contest_info  = "<p>We would like to thank you once again for participating in the " . $contest['contest_name'] . " hosted by <a href='yps-website'> Youth Photographic Society</a>, Bangalore, India. ";
//	$contest_info .= "The Salon Exhibition and Award function were held as planned at " . $contest['exhibition_venue'] . ". ";
//	$contest_info .= "The Salon exhibited in print and frame all the pictures whose full resolution files were uploaded by the participants";
//	if ($num_print_sections > 0)
//		$contest_info .= " and all the prints submitted under PRINT sections";
//	$contest_info .= ". The Salon Catalog was released by the Chief Guest during the Award function. ";
//	$contest_info .= "The Chief Guest also presented the medals and ribbons to the award winners who could attend the Function in person.</p>";
//
//	$contest_info .= "<h2>Salon Catalog Released</h2>";
//	$contest_info .= "<p>The Salon Catalog is released as a Digital Catalog. You can ";
//	$contest_info .= "<a href='" . SALON_ROOT . "/catalog/catalog.php?id=" . $admin_yearmonth . "&catalog=" . $contest['catalog'] . "' ><b>View Catalog Online</b></a> or ";
//	$contest_info .= "<a href='" . SALON_ROOT . "/salons/" . $admin_yearmonth . "/files/" . $contest['catalog_download'] . "' download><b>Download the Catalog</b></a>.</p>";
//
//	$contest_info .= "<h2>Ordering Printed Catalog</h2>";
//	$contest_info .= "<p>If you like to order a printed catalog, you can send an email to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a>. ";
//	$contest_info .= "The catalog will be printed in 8x10 inch size. Here is the cost of the catalog:</p>";
//	$contest_info .= "<br>";
//	$contest_info .= "<table class='table-data'><tbody>";
//	$contest_info .= "<tr><td><p>Participants from India</p></td><td><p>Rs. 1750 + Mailing Charges</p></td></tr>";
//	$contest_info .= "<tr><td><p>Participants from Other Countries</p></td><td><p>USD 27 + Mailing Charges</p></td></tr>";
//	$contest_info .= "</tbody></table>";
//	$contest_info .= "<br>";
//	$contest_info .= "<p>After placing the order, YPS will send instructions to make the payment. Catalog printing and mailing will be initiated after receipt of payment. ";
//	$contest_info .= "<b>The last date for receipt of orders for Printed Salon Catalog is September 10, 2019.</b></p>";
//
//	$contest_info .= "<h2>Distribution of Awards/Prizes and Certificates</h2>";
//	$contest_info .= "<p>We thank everyone who could attend the Inaugural Function. Here is the arrangement for distribution of ";
//	$contest_info .= "Prizes/Awards and Certificates for those who could not attend: </p>";
//	$contest_info .= "<ul>";
//	$contest_info .= "<li>Mementos/Medals/Prizes/Certificates related to Awards and Honorable Mentions will be sent through post.</li>";
//	$contest_info .= "<li>A Digital Certificate can be downloaded for each picture Accepted either by logging into the Salon web-site or by using the links provided in the email.</li>";
//	$contest_info .= "</ul>";

	//echo "Stage 2<br>";

	// Message related to Cash Award
	// Determine the award money
	$award_money = 0;
	$num_mementos = 0;
	$num_certificates = 0;

	// check for individual awards
	$sql = "SELECT COUNT(*) AS individual_awards, SUM(cash_award) AS award_money, SUM(has_certificate) AS num_certificates, SUM(has_memento) AS num_mementos ";
	$sql .= " FROM award, entry_result ";
	$sql .= " WHERE entry_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.award_type = 'entry' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if ($res['individual_awards'] > 0)
			$has_individual_awards = true;
		$award_money += $res['award_money'];
		$num_mementos += $res['num_mementos'];
		$num_certificates += $res['num_certificates'];
	}

	// check for picture awards
	$sql = "SELECT COUNT(*) AS picture_awards, SUM(cash_award) AS award_money, SUM(has_certificate) AS num_certificates, SUM(has_memento) AS num_mementos ";
	if ($contest_archived)
		$sql .= " FROM ar_pic_result pic_result, award ";
	else
		$sql .= " FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level != 99 ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ($res = mysqli_fetch_array($qry)) {
		if ($res['picture_awards'] > 0)
			$has_picture_awards = true;
		$award_money += $res['award_money'];
		$num_mementos += $res['num_mementos'];
		$num_certificates += $res['num_certificates'];
	}

	if ($award_money > 0) {
		$contest_info .= "<h3>Remittance of Award Money</h3>";
		$contest_info .= "<p style='text-align: justify;'>One or more of your pictures has Cash Award. ";
		$contest_info .= "Cash Award will be distributed by money transfer to the Bank Account you have specified in your profile. ";
		if ($row['bank_account_number'] == "") {
			$contest_info .= "<b>YOU ARE YET TO UPDATE BANK ACCOUNT DETAILS</b> on the Salon web-site. We request you to login and update ";
			$contest_info .= "the details immediately and inform us. ";
			$contest_info .= "</p>";
		}
		else {
			$contest_info .= "Reproducing the Bank Account Details updated by you for verification:</p>";
			$contest_info .= "<table class='table-data'>";
			$contest_info .= "<tr><td><p>Name of Account</p></td><td><p>" . $row['bank_account_name'] . "</p></td></tr>";
			$contest_info .= "<tr><td><p>Account Number ending</p></td><td><p>..." . substr($row['bank_account_number'], -4) . "</p></td></tr>";
			$contest_info .= "<tr><td><p>Account Type</p></td><td><p>" . $row['bank_account_type'] . "</p></td></tr>";
			$contest_info .= "<tr><td><p>Bank</p></td><td><p>" . $row['bank_name'] . "</p></td></tr>";
			$contest_info .= "<tr><td><p>Branch</p></td><td><p>" . $row['bank_branch'] . "</p></td></tr>";
			$contest_info .= "<tr><td><p>IFSC Code</p></td><td><p>" . $row['bank_ifsc_code'] . "</p></td></tr>";
			$contest_info .= "</table>";
			$contest_info .= "<p>If any of these need to be corrected, please send an email to <a href='mailto:salon@ypsbengaluru.in'>salon@ypsbengaluru.in</a> immediately.</p>";
		}
	}

	//echo "Stage 3<br>";

	$has_picture_awards = (($row['awards'] + $row['hms']) > 0);
	$has_picture_acceptances = ($row['acceptances'] > 0);
	$mailing_info = "";
	if ($has_picture_awards) {
		$mailing_info .= "<h3>Mailing of Awards</h3>";
		$mailing_info .= "<p style='text-align: justify;'>One or more of your pictures has won an Award. ";
		$mailing_info .= "We have commenced mailing of medals/ribbons/certificates to the postal address ";
		$mailing_info .= "specified by you in your profile. ";
	}
	// list($price_inr, $postage_inr) = ($contest['catalog_price_in_inr'] == "") ? array(0, 0) : explode("|", $contest['catalog_price_in_inr']);
	// list($price_usd, $postage_usd) = ($contest['catalog_price_in_usd'] == "") ? array(0, 0) : explode("|", $contest['catalog_price_in_usd']);

	$cert_link = SALON_ROOT . "/op/certificate.php?cert=" . encode_string_array($admin_yearmonth . "|PROFILE|" . $profile_id . "|ALL");
	$order_link = SALON_ROOT . "/catalog_order.php?code=" . encode_string_array($admin_yearmonth . "|" . $profile_id);

	$message_file = "../../salons/$admin_yearmonth/blob/catalog_mail_msg.htm";

	list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

	$message = load_message($message_file, array("participant-name" => $row['profile_name'],
														  	"server-name" => SALON_ROOT,
															"contest-name" => $contest['contest_name'],
														  	"exhibition-start-date" => pdate($contest['exhibition_start_date']),
														  	"exhibition-end-date" => pdate($contest['exhibition_end_date']),
														  	"catalog-order-last-date" => pdate($contest['catalog_order_last_date']),
														  	// "catalog-price-inr" => $price_inr,
														  	// "catalog-postage-inr" => $postage_inr,
												 			// "catalog-total-inr" => $price_inr + $postage_inr,
														  	// "catalog-price-usd" => $price_usd,
														  	// "catalog-postage-usd" => $postage_usd,
												 			// "catalog-total-usd" => $price_usd + $postage_usd,
															"contest-data" => $contest_info,
															"mailing-data" => $mailing_info,
															"cert-link" => (($has_picture_awards || $has_picture_acceptances) ? $cert_link : "#"),
															"hide-certificate-image" => (($has_picture_awards || $has_picture_acceptances) ? "" : "display: none;"),
															"catalog-order-link" => $order_link,
															"recognition-data" => $recognition_data,
															"secretary-role" => $secretary_role,
															"salon-secretary" => $secretary_name,
															"partner-data" => $partner_data,
												 			"yps-website" => YPS_WEBSITE
															));

	$subject = $contest['contest_name'] . " - Distribution of Awards, Catalogs & Prizes to " . $row['profile_name'];

	$to = ($test_email == "" ? $row['email'] : $test_email);
	send_mail($to, $subject, $message, $no_cc);

}


// Email Reminders for ordering Catalog & complete feedback
function send_catalog_reminder_mail($profile_id) {
	global $DBCON;
	global $contest;
	global $skipped;
	global $has_individual_awards, $has_picture_acceptances, $has_picture_awards;
	global $recognition_data;
	global $admin_yearmonth;
	global $num_print_sections;
	global $contest_archived;
	global $partner_data;
	global $test_email;

	$sql  = "SELECT profile_name, entrant_category, email, uploads, awards, hms, acceptances, bank_account_name, bank_account_number, bank_account_type, ";
	$sql .= "       bank_name, bank_branch, bank_ifsc_code, country_id ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_entry entry ";
	else
		$sql .= "  FROM profile, entry ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND entry.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry.profile_id = profile.profile_id ";
	$entry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($entry);
	$email = $row['email'];

	// Do not send a report for people who have not uploaded
	if ($row['uploads'] == 0) {
		$skipped ++;
		return;
	}

	// Check if the Catalog has already been ordered
	if ($contest['catalog_order_last_date'] == NULL || date("Y-m-d") > $contest['catalog_order_last_date'])
		$order_catalog = false;
	else {
		$sql = "SELECT COUNT(*) AS ordered FROM catalog_order WHERE yearmonth = '$admin_yearmonth' AND profile_id = '$profile_id' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$catalog_order = mysqli_fetch_array($query);
		$order_catalog = ($catalog_order['ordered'] == 0);
	}
	// $has_inr_catalog = ($contest['catalog_price_in_inr'] != "");
	// list($price_inr, $postage_inr) = ($contest['catalog_price_in_inr'] == "") ? array(0, 0) : explode("|", $contest['catalog_price_in_inr']);
	// $has_usd_catalog = ($contest['catalog_price_in_usd'] != "");
	// list($price_usd, $postage_usd) = ($contest['catalog_price_in_usd'] == "") ? array(0, 0) : explode("|", $contest['catalog_price_in_usd']);

	// New JSON price format
	$catalog_inr = [];
	$catalog_usd = [];
	$cat = "";
	if ($contest['catalog_price_in_inr'] != "")
		$catalog_inr = json_decode($contest['catalog_price_in_inr']);
	if ($contest['catalog_price_in_usd'] != "")
		$catalog_usd = json_decode($contest['catalog_price_in_usd']);

	if ( ($row['country_id'] == '101' && sizeof($catalog_inr) > 0) || ($row['country_id'] != '101' && sizeof($catalog_inr) > 0) ) {
		$cat  = "<style>";
		$cat .= "table.catalog-list { width: 100%;}";
		$cat .= "table.catalog-list th.right {text-align: right;}";
		$cat .= "table.catalog-list td.right {text-align: right;}";
		$cat .= "</stytle>";
		$cat .= "<div style='margin-left: 15px; margin-right: 15px;'>";
		$cat .= "<table class='catalog-list'><tbody>";
		$cat .= "<tr><th>Catalog Type</th><th class='right'>Price</th><th class='right'>Postage</th><th class='right'>Total Cost</th></tr>";
		$catalog_list = ($row['country_id'] == '101' ? $catalog_inr : $catalog_usd);
		$currency = ($row['country_id'] == '101' ? "Rs." : "US$");
		foreach ($catalog_list as $catalog) {
			$cat .= "<tr>";
			$cat .= "<td>" . $catalog['model'] . "</td>";
			$cat .= "<td class='right'>" . sprintf("%s %.2f", $currency, $catalog['price']) . "</td>";
			$cat .= "<td class='right'>" . sprintf("%s %.2f", $currency, $catalog['postage']) . "</td>";
			$cat .= "<td class='right'>" . sprintf("%s %.2f", $currency, $catalog['price'] + $catalog['postage']) . "</td>";
			$cat .= "</tr>";
		}
		$cat .= "</tbody></table>";
		$cat .= "</div>";
	}

	// Check if the user has visited the exhibition
	$sql = "SELECT COUNT(*) AS visited FROM visitor_book WHERE yearmonth = '$admin_yearmonth' AND visitor_email = '$email' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$visitor = mysqli_fetch_array($query);
	$visit_exhibition = ($visitor['visited'] == 0);

	$cert_link = SALON_ROOT . "/op/certificate.php?cert=" . encode_string_array($admin_yearmonth . "|PROFILE|" . $profile_id . "|ALL");
	$order_link = SALON_ROOT . "/catalog_order.php?code=" . encode_string_array($admin_yearmonth . "|" . $profile_id);

	list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

	$message_file = "../../salons/$admin_yearmonth/blob/catalog_reminder_msg.htm";

	$message = load_message($message_file, array("participant-name" => $row['profile_name'],
														  	"server-name" => SALON_ROOT,
															"contest-name" => $contest['contest_name'],
														  	"exhibition-start-date" => pdate($contest['exhibition_start_date']),
														  	"exhibition-end-date" => pdate($contest['exhibition_end_date']),
														  	"catalog-order-last-date" => pdate($contest['catalog_order_last_date']),
															// "catalog-inr" => $has_inr_catalog,
												 			// "catalog-usd" => $has_usd_catalog,
															"catalog-list" => $cat,
														  	// "catalog-price-inr" => $price_inr,
														  	// "catalog-postage-inr" => $postage_inr,
												 			// "catalog-total-inr" => $price_inr + $postage_inr,
														  	// "catalog-price-usd" => $price_usd,
														  	// "catalog-postage-usd" => $postage_usd,
												 			// "catalog-total-usd" => $price_usd + $postage_usd,
												 			"catalog-order" => $order_catalog,
												 			"visit-exhibition" => $visit_exhibition,
															"cert-link" => (($has_picture_awards || $has_picture_acceptances) ? $cert_link : "#"),
															"hide-certificate-image" => (($has_picture_awards || $has_picture_acceptances) ? "" : "display: none;"),
															"catalog-order-link" => $order_link,
															"secretary-role" => $secretary_role,
															"salon-secretary" => $secretary_name,
															"recognition-data" => $recognition_data,
															"partner-data" => $partner_data
															));

	$subject = $contest['contest_name'] . " - Distribution of Awards, Catalogs & Prizes to " . $row['profile_name'];

	$to = ($test_email == "" ? $row['email'] : $test_email);
	send_mail($to, $subject, $message, "nocc");

}

function send_bank_details_mail($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	global $recognition_data;
	global $contest_archived;
	global $partner_data;
	global $test_email;
	global $no_cc;

	$sql  = "SELECT profile_name, entrant_category, email, uploads, awards, hms, bank_account_number ";
	if ($contest_archived)
		$sql .= "  FROM profile, ar_entry entry ";
	else
		$sql .= "  FROM profile, entry ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND entry.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry.profile_id = profile.profile_id ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$entry = mysqli_fetch_array($query);
	if ($entry['uploads'] == 0 || ($entry['awards'] + $entry['hms']) == 0) {
		++ $skipped;
		return;
	}

	$award_money = 0;

	// check for individual awards
	$sql = "SELECT SUM(cash_award) AS award_money ";
	$sql .= " FROM award, entry_result ";
	$sql .= " WHERE entry_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND entry_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = entry_result.yearmonth ";
	$sql .= "   AND award.award_id = entry_result.award_id ";
	$sql .= "   AND award.award_type = 'entry' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$res = mysqli_fetch_array($qry);
	$award_money += $res['award_money'];

	$upload_pics_list = array();

	// Check for Special Picture Awards
	$sql  = "SELECT pic_result.pic_id, cash_award ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award ";
	else
		$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section = 'CONTEST' ";			// special picture awards at contest level
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_special_pics = mysqli_num_rows($query);
	while ($res = mysqli_fetch_array($query)) {
		$award_money += $res['cash_award'];
		$upload_pics_list[] = $res['pic_id'];
	}

	// Pictures requiring File Uploads and award money amount
	$sql  = "SELECT pic_result.pic_id, cash_award ";
	if ($contest_archived)
		$sql .= "  FROM ar_pic_result pic_result, award ";
	else
		$sql .= "  FROM pic_result, award ";
	$sql .= " WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic_result.profile_id = '$profile_id' ";
	$sql .= "   AND award.yearmonth = pic_result.yearmonth ";
	$sql .= "   AND award.section != 'CONTEST' ";			// exclude special picture awards
	$sql .= "   AND award.award_id = pic_result.award_id ";
	$sql .= "   AND award.award_type = 'pic' ";
	$sql .= "   AND award.level < 99 ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$num_award_pics = mysqli_num_rows($query);
	while ($res = mysqli_fetch_array($query)) {
		$award_money += $res['cash_award'];
		$upload_pics_list[] = $res['pic_id'];
	}

	// Check how many pictures have been uploaded
	if ($contest_archived)
		$sql  = "SELECT COUNT(*) AS frf_uploaded FROM ar_pic pic ";
	else
		$sql  = "SELECT COUNT(*) AS frf_uploaded FROM pic ";
	$sql .= " WHERE pic.yearmonth = '$admin_yearmonth' ";
	$sql .= "   AND pic.profile_id = '$profile_id' ";
	$sql .= "   AND pic.full_picfile IS NOT NULL ";
	$sql .= "   AND pic.full_picfile != '' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$res = mysqli_fetch_array($query);
	$frf_uploaded = $res['frf_uploaded'];

	$uploads_completed = ($frf_uploaded == sizeof(array_unique($upload_pics_list)));

	// Generate Contest Info
	$about = <<<CONTEST
		Thank you for participating in the [salon-name] hosted by <a href='[yps-website]'> Youth Photographic Society</a>, Bangalore, India.
		You have won awards in the Salon that requires action to be completed on or before <b>[update-end-date]</b>. Please see the details below and complete
		the actions today.
CONTEST;
	$values = array(
				"salon-name" => $contest['contest_name'],
				"update-end-date" => pdate($contest['update_end_date'])
				);
	$contest_info = replace_values($about, $values);

	// Generate Action Required Message
	$processed = false;
	$complete_uploads_message ="";
	$m  = "<ul>";
	if ($entry['bank_account_number'] == "" && $award_money > 0) {
		$processed = true;
		$m .= "<li>Please login to <a href='" . SALON_ROOT . "'>Salon Website</a> and update your Bank Account Details. ";
		$m .= "Bank Account Details are required to remit Award money related to your awards. Please update the details immediately.</li>";
	}
	if (! $uploads_completed) {
		$processed = true;
		$m .= "<li>You are required to upload " . ($entry['awards'] + $entry['hms'] - $frf_uploaded) . " full resolution picture(s) related to your Awards and Honorable Mentions. ";
		$m .= "Follow the instructions below and complete the uploads today.</li>";
		$msg = <<<MESSAGE
			<h2>Upload Full-Resolution Pictures</h2>
			<p>Full Resolution pictures are required for printing catalog and for printin for the exhibition.</p>
			<p>[special-pic-data]</p>
			<p>[awards-data]</p>
MESSAGE;
		$values = array(
					"special-pic-data" => ($num_special_pics > 0 ? generate_special_pic_award($profile_id, false) : ""),
					"awards-data" => ($num_award_pics > 0 ? generate_awards($profile_id, false) : "")
					);
		$complete_uploads_message = replace_values($msg, $values);
	}
	$m .= "</ul>";

	list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

	if ($processed) {
		$subject = $contest['contest_name'] . " - ACTION REQUIRED";
		$message = load_message("template/bank_details_mail_msg.htm",
														array(
																"server-address" => SALON_ROOT,
																"participant-name" => $entry['profile_name'],
																"contest-data" => $contest_info,
																"update-end-date" => pdate($contest['update_end_date']),
																"action-required-message" => $m,
																"complete-uploads-message" => $complete_uploads_message,
																"recognition-data" => $recognition_data,
																"contest-name" => $contest['contest_name'],
																"secretary-role" => $secretary_role,
																"salon-secretary" => $secretary_name,
																"partner-data" => $partner_data,
																"yps-website" => YPS_WEBSITE
																));
		$to = ($test_email == "" ? $entry['email'] : $test_email);
		send_mail($to, $subject, $message, $no_cc);
	}
	else
		++ $skipped;
}

function send_judging_mail($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	global $contest_archived;
	global $partner_data;
	global $test_email;
	global $no_cc;

	if ($contest_archived)
		$sql  = "SELECT profile_name, email, uploads FROM ar_entry entry, profile ";
	else
		$sql  = "SELECT profile_name, email, uploads FROM entry, profile ";
	$sql .= " WHERE yearmonth = '$admin_yearmonth' AND entry.profile_id = '$profile_id' AND profile.profile_id = entry.profile_id ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$entry = mysqli_fetch_array($query);
	$banner_file = SALON_ROOT . "/salons/" . $admin_yearmonth . "/img/judging-invite-banner.jpg";
	if (file_exists("../../salons/$admin_yearmonth/img/judging-invite-banner.jpg")) {
		$banner_html = "<tr><td colspan='2'><img src='" . $banner_file . "' style='max-width: 100%;' alt='YPS Remote judging schedule'></td></tr>";
	}
	else
		$banner_html = "";

	list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);
	$secretary_name = preg_replace('/^(Mr|Mrs|Ms|Dr)\. *(.*)$/', "$2", $secretary_name);

	if ($entry['uploads'] > 0) {
		$values = array (
					"server-address" => SALON_ROOT,
					"banner-image" => $banner_html,
					"salon-name" => $contest['contest_name'],
					"secretary-role" => $secretary_role,
					"salon-secretary" => $secretary_name,
					"partner-data" => $partner_data,
					"participant-name" => $entry['profile_name'],
					"judging-start-date" => pdate($contest['judging_start_date']),
					"judging-end-date" => pdate($contest['judging_end_date']),
					"judging-venue" => $contest['judging_venue'],
					"judging-venue-address" => $contest['judging_venue_address'],
					"judging-venue-location-map" => $contest['judging_venue_location_map'] );

		if ($contest['judging_mode'] == 'VENUE') {
			if (file_exists("../../salons/$admin_yearmonth/blob/judging_mail_msg_venue.htm"))
				$message = load_message("../../salons/$admin_yearmonth/blob/judging_mail_msg_venue.htm", $values);
			else
				$message = load_message("template/judging_mail_msg_venue.htm", $values);
		}
		else {
			if (file_exists("../../salons/$admin_yearmonth/blob/judging_mail_msg_remote.htm"))
				$message = load_message("../../salons/$admin_yearmonth/blob/judging_mail_msg_remote.htm", $values);
			else
				$message = load_message("template/judging_mail_msg_remote.htm", $values);
		}

		$subject = "Invitation to attend Open Judging of " . $contest['contest_name'];

		$to = ($test_email == "" ? $entry['email'] : $test_email);
		send_mail($to, $subject, $message, $no_cc);	// Don't copy salon email
		// send_mail($entry['email'], $subject, $message);	// copy salon email
	}
	else
		$skipped ++;
}

function send_exhibition_invite($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	global $partner_data;
	global $test_email;
	global $no_cc;

	// Send only if exhibition details have been published on the website
	if ($contest['has_exhibition'] && date("Y-m-d") < $contest['exhibition_start_date'] && $contest['exhibition']['email_message_blob'] != "" &&
			file_exists($_SERVER['DOCUMENT_ROOT'] . "/salons/$admin_yearmonth/blob/" . $contest['exhibition']['email_message_blob'])) {
		$sql  = "SELECT profile_name, email FROM profile WHERE profile_id = '$profile_id' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$entry = mysqli_fetch_array($query);
		$exhibition_invite_blob = "../../salons/$admin_yearmonth/blob/" . $contest['exhibition']['email_message_blob'];
		$exhibition_header_img = SALON_ROOT . "/salons/$admin_yearmonth/img/" . $contest['exhibition']['email_header_img'];

		list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

		$values = array (
					"server-name" => SALON_ROOT,
					"contest-name" => $contest['contest_name'],
					"participant-name" => $entry['profile_name'],
					"exhibition-start-date" => pdate($contest['exhibition_start_date']),
					"exhibition-end-date" => pdate($contest['exhibition_end_date']),
					"exhibition-header-img" => $exhibition_header_img,
					"exhibition-venue" => $contest['exhibition_venue'],
					"exhibition-venue-address" => $contest['exhibition_venue_address'],
					"secretary-role" => $secretary_role,
					"salon-secretary" => $secretary_name,
				 	"partner-data" => $partner_data );

		// $message = load_message("exhibition_invite.htm", $values);
		$message = load_message($exhibition_invite_blob, $values);

		$subject = "Invitation to " . $contest['contest_name'] . " Exhibition";

		$to = ($test_email == "" ? $entry['email'] : $test_email);
		send_mail($to, $subject, $message, $no_cc);

	}
	else
		$skipped ++;
}

//
// Send custom notification to award Winners
// template : award_winners_communication.htm under blob folder for the salon
// dynamic communication data from award_winners_actions.php under blob folder of the salon
//
function send_custom_awardee_email($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	global $partner_data;
	global $test_email;
	global $no_cc;

	// Check if there is any action pending
	if (is_action_pending($profile_id)) {

		$sql  = "SELECT profile_name, email FROM profile ";
		$sql .= " WHERE profile.profile_id = '$profile_id' ";
		$sql .= "   AND profile.profile_id IN ( ";
		$sql .= "       SELECT DISTINCT pic_result.profile_id FROM pic_result ";
		$sql .= "        WHERE pic_result.yearmonth = '$admin_yearmonth' ";
		$sql .=	"       ) ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$entry = mysqli_fetch_array($query);

			list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

			$values = array (
						"server-name" => SALON_ROOT,
						"salon-name" => $contest['contest_name'],
						"participant-name" => $entry['profile_name'],
						"actions-description" => get_action_description($profile_id),
						"actions-list" => get_action_list($profile_id),
						"secretary-role" => $secretary_role,
						"salon-secretary" => $secretary_name,
						"partner-data" => $partner_data
					);

			// $message = load_message("exhibition_invite.htm", $values);
			$message_file = "../../salons/$admin_yearmonth/blob/award_winners_communication.htm";
			$message = load_message($message_file, $values);

			$subject = "Request for action related to " . $contest['contest_name'] ;

			$to = ($test_email == "" ? $entry['email'] : $test_email);
			send_mail($to, $subject, $message, $no_cc);
		}
		else {
			log_error("Participant with $profile_id does not have any wins", __FILE__, __LINE__);
			$skipped ++;
		}
	}
	else {
		log_error("No actions pending for $profile_id", __FILE__, __LINE__);
		$skipped ++;
	}
}

//
// Send Mailing information and Tracking Information to Salon Award and Catalog Recipients
// template : award_winners_communication.htm under blob folder for the salon
//
function find_mail_data($email) {
	global $mailing_data;

	foreach ($mailing_data as $data) {
		if ($data[0] == $email)
			return $data;
	}

	return false;
}

function send_mailing_info($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	// inherited from the include file "../../salons/$admin_yearmonth/blob/mailing_data.php"
	global $tracking_site;
	global $posts_name;
	global $partner_data;
	global $mail_format;
	global $test_email;
	global $no_cc;

	$sql  = "SELECT profile_name, email FROM profile ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$sql .= "   AND profile.profile_id IN ( ";
	$sql .= "       SELECT DISTINCT pic_result.profile_id FROM pic_result ";
	$sql .= "        WHERE pic_result.yearmonth = '$admin_yearmonth' ";
	$sql .= "       UNION ";
	$sql .= "       SELECT DISTINCT catalog_order.profile_id FROM catalog_order ";
	$sql .= "        WHERE catalog_order.yearmonth = '$admin_yearmonth' ";
	$sql .= "          AND catalog_order.order_value = catalog_order.payment_received ";
	$sql .=	"       ) ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$entry = mysqli_fetch_array($query);
		if ( ($data = find_mail_data($entry['email'])) != false) {
			list ($email, $items, $mailing_date, $tracking_no, $notes) = $data;
			$items_list = "";
			foreach( explode("|", $items) as $item )
				$items_list .= "<li>$item</li>";

			list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

			$values = array (
						"server-name" => SALON_ROOT,
						"salon-name" => $contest['contest_name'],
						"participant-name" => $entry['profile_name'],
						"courier" => $posts_name,
						"website" => $tracking_site,
						"list_of_items" => $items_list,
						"posted_on" => pdate($mailing_date),
						"tracking_number" => $tracking_no,
						"notes" => $notes,
						"secretary-role" => $secretary_role,
						"salon-secretary" => $secretary_name,
						"partner-data" => $partner_data
					);

			// $message = load_message("exhibition_invite.htm", $values);
			// $message_file = "../../salons/$admin_yearmonth/blob/mailing_info_email.htm";
			$message_file = "../../salons/$admin_yearmonth/blob/" . $mail_format;
			$message = load_message($message_file, $values);

			$subject = $contest['contest_name'] . " - Information on awards" ;

			$to = ($test_email == "" ? $entry['email'] : $test_email);
			send_mail($to, $subject, $message, $no_cc);
		}
		else
			$skipped ++;
	}
	else
		$skipped ++;
}


function send_earlybird_mail($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $early_bird_end_date;
	global $admin_yearmonth;
	global $contest_archived;
	global $test_email;
	global $no_cc;

	if (date("Y-m-d") <= $early_bird_end_date) {
		$sql  = "SELECT name, entrant_category, fee_code, login_id, email, fees_payable, discount_applicable, payment_received ";
		if ($contest_archived)
			$sql .= "  FROM profile, ar_entry entry ";
		else
			$sql .= "  FROM profile, entry ";
		$sql .= " WHERE profile.profile_id = '$profile_id' ";
		$sql .= "   AND entry.yearmonth = '$admin_yearmonth' ";
		$sql .= "   AND entry.profile_id = profile.profile_id ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$entry = mysqli_fetch_array($query);
		if ($entry['payment_received'] >= ($entry['fees_payable'] - $entry['discount_applicable']) ) {
			++ $skipped;
			return;
		}

		$m  = "<html><body>";
		$m .= "<p>Dear " . $entry['profile_name'] . ",</p><br>";
		$m .= "<p>Thank you for registering for " . $contest['profile_name'] . ". We noticed that you are yet to make a selection of sections to participate and make the payment. ";
		$m .= "If you make the payment on or before " . $early_bird_end_date . " you can avail concessional Early Bird rates. You can upload images any time till the last date ";
		$m .= "for upload and even increase number of sections any time later.</p><br>";
		$m .= "<p><b>Login and make the payment today.</b></p><br>";
		$m .= "<p><b>YPS Salon Committee</b></p>";
		$m .= "<p>" . $contest['contest_name'] . "</p><br>";
		$m .= "</body></html>";

		$subject = $contest['contest_name'] . " - PAY AT EARLY BIRD RATES";

		$to = ($test_email == "" ? $entry['email'] : $test_email);
		send_mail($to, $subject, $m, $no_cc);

	}
	else
		$skipped ++;
}

//
// Send custom notification to award Winners
// template : award_winners_communication.htm under blob folder for the salon
// dynamic communication data from award_winners_actions.php under blob folder of the salon
//
function send_salon_open_mail($profile_id) {
	global $contest;
	global $skipped;
	global $DBCON;
	global $admin_yearmonth;
	global $partner_data;
	global $test_email;
	global $no_cc;

	// Check if there is any action pending
	$sql  = "SELECT profile_name, email, IFNULL(noprom.is_active, 0) AS is_active ";
	$sql .= "  FROM profile LEFT JOIN noprom ON noprom.profile_id = profile.profile_id ";
	$sql .= " WHERE profile.profile_id = '$profile_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) > 0) {
		$entry = mysqli_fetch_array($query);
		if ($entry['is_active'] == '1') {
			++ $skipped;
			return ;
		}

		list($secretary_role, $secretary_name) = explode("|", $contest['Secretary']);

		$values = array (
					"server-address" => SALON_ROOT,
					"banner-image" => SALON_ROOT . "/salons/$admin_yearmonth/img/salon_open_mail.png",
					"salon-name" => $contest['contest_name'],
					"participant-name" => $entry['profile_name'],
					"registration-last-date" => pdate($contest['registration_last_date']),
					"partner-data" => $partner_data
				);

		// $message = load_message("exhibition_invite.htm", $values);
		$message_file = "../../salons/$admin_yearmonth/blob/salon_open_mail.htm";
		$message = load_message($message_file, $values);

		$subject = $contest['contest_name'] . " is open for participation ";

		$to = ($test_email == "" ? $entry['email'] : $test_email);
		send_mail($to, $subject, $message, $no_cc);
	}
	else {
		++ $skipped;
	}
}


if (isset($_SESSION['admin_id']) && isset($_SESSION['admin_yearmonth']) && isset($_REQUEST['checkbox']) &&
	(isset($_REQUEST['upload_mail']) || isset($_REQUEST['judging_mail']) || isset($_REQUEST['results_mail']) || isset($_REQUEST['catalog_mail']) ||
		isset($_REQUEST['bank_details_mail']) || isset($_REQUEST['earlybird_mail']) || isset($_REQUEST['club_reminder_mail']) ||
		isset($_REQUEST['exhibition_invite']) || isset($_REQUEST['catalog_remind']) || isset($_REQUEST['custom_awardee_email']) ||
		isset($_REQUEST['mailing_info_email']) || isset($_REQUEST['salon_open_mail']) )
	) {

	$admin_yearmonth = $_SESSION['admin_yearmonth'];
	if (isset($_REQUEST['send_test_email']))
		$test_email = $_REQUEST['admin_email'];
	else
		$test_email = "";

	// debug_dump("test_email", $test_email, __FILE__, __LINE__);
	// debug_dump("REQUEST", $_REQUEST, __FILE__, __LINE__);

	if (isset($_REQUEST['no_cc']))
		$no_cc = "nocc";
	else
		$no_cc = "";

	// Global Data
	$sql = "SELECT * FROM contest WHERE yearmonth = '$admin_yearmonth'";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest = mysqli_fetch_array($query);
	$contest_archived = ($contest['archived'] == '1');

	// Exhibition Data
	$sql = "SELECT * FROM exhibition WHERE yearmonth = '$admin_yearmonth' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$contest["exhibition"] = mysqli_fetch_array($query);

	// Chairman & Secretary
	$sql = "SELECT * FROM team WHERE yearmonth = '$admin_yearmonth' AND role IN ('Chairman', 'Secretary') ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($res = mysqli_fetch_array($query))
		$contest[$res['role']] = $res['role_name'] . "|" . $res['member_name'];

	// num_print_sections
	$sql = "SELECT COUNT(*) AS num_print_sections FROM section WHERE yearmonth = '$admin_yearmonth' AND section_type = 'P' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$res = mysqli_fetch_array($query);
	$num_print_sections = $res['num_print_sections'];

	// Assemble Recognition Data
	$sql = "SELECT * FROM recognition WHERE yearmonth = '$admin_yearmonth' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$recognition_data  = "<h2>This Salon is recognized by:</h2>";
	$recognition_data .= "<table class='table-data' width='100%'><tbody>";

	// $target_dir = http_method() . $_SERVER['SERVER_NAME'] . "/res/logo/";
	$target_dir = http_method() . $_SERVER['SERVER_NAME'] . "/salons/$admin_yearmonth/img/recognition/";
	while ($res = mysqli_fetch_array($qry)) {
		$recognition_data .= "<tr>";
		$recognition_data .= "<td><img src='" . $target_dir . $res['logo'] . "' style='width:80px;' ></td>";
		$recognition_data .= "<td><h4>" . $res['organization_name'] . "</h4><p><a href='" . $res['website'] . "'>" . $res['website'] . "</a></p></td>";
		$recognition_data .= "<td><h3>" . $res['recognition_id'] . "</h3></td>";
		$recognition_data .= "</tr>";
	}
	$recognition_data .= "</tbody></table>";

	// Assemble Partner Data
	$salon_folder = "../../salons/$admin_yearmonth";
	$partner_data = "";
	if (file_exists($salon_folder . "/blob/partner_data.php")) {
		include($salon_folder . "/blob/partner_data.php");
		if ( ! empty($partners) && sizeof($partners) > 0) {
			$partner_data  = "<p><span style='font-size: 20px; font-weight: bold'>Salon supported by</span></p>";
			$partner_data .= partner_email_footer($admin_yearmonth);
			// $partner_data .= "<table cellpadding='8' width='100%' class='partner-data' >";
			// $partner_data .= "<tbody>";
			// foreach ($partners as $partner) {
			// 	$partner_logo = http_method() . $_SERVER['HTTP_HOST'] . "/salons/" . $admin_yearmonth . "/img/sponsor/" . $partner['logo'];
			// 	$partner_data .= "<tr style='border-bottom: 1px solid #aaa; border-top: 1px solid #aaa;'>";
			// 	$partner_data .= "<td width='96'>";
			// 	if (! empty($partner['logo']) && file_exists($salon_folder . "/img/sponsor/" . $partner['logo'])) {
			// 		if ( ! empty($partner['website']))
			// 			$partner_data .= "<a href='" . $partner['website'] . "'><img style='max-width:80px; max-height:120px;' src='" . $partner_logo . "' ></a>";
			// 		else
			// 			$partner_data .= "<img style='max-width:80px; max-height:80px;' src='" . $partner_logo . "' >";
			// 	}
			// 	$partner_data .= "</td>";
			// 	$partner_data .= "<td>";
			// 	$partner_data .= "<b>" . $partner['name'] . "</b>";
			// 	if (! empty($partner['website']))
			// 		$partner_data .= "<br><a href='" . $partner['website'] . "'>" . $partner['website'] . "</a>";
			// 	if (! empty($partner['email']))
			// 		$partner_data .= "<br><a href='mailto:" . $partner['email'] . "'>" . $partner['email'] . "</a>";
			// 	if (! empty($partner['phone']))
			// 		$partner_data .= "<br>Phone: " . $partner['phone'];
			// 	if (! empty($partner['text']))
			// 		$partner_data .= "<br>" . $partner['text'];
			// 	$partner_data .= "</td>";
			// 	$partner_data .= "</tr>";
			// }
			// $partner_data .= "</tbody>";
			// $partner_data .= "</table>";
		}
	}

	// Determine Ealy Bird Date
	$sql = "SELECT MAX(fee_end_date) AS fee_end_date FROM fee_structure WHERE yearmonth = '$admin_yearmonth' AND fee_code = 'EARLY BIRD' ";
	$qry = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$ebd = mysqli_fetch_array($qry);
	$early_bird_end_date = $ebd['fee_end_date'];

	if (isset($_REQUEST['club_id'])) {
		$club_id = $_REQUEST['club_id'];
		$sql  = "SELECT * FROM club, club_entry, profile ";
		$sql .= " WHERE club.club_id = '$club_id' ";
		$sql .= "   AND club_entry.yearmonth = '" . $_SESSION['admin_yearmonth'] . "' ";
		$sql .= "   AND club_entry.club_id = club.club_id ";
		$sql .= "   AND profile.profile_id = club_entry.club_entered_by ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0) {
			$_SESSION['err_msg'] = "Incorrect Club ID value ($club_id)";
			header("Location: ".$_SERVER['HTTP_REFERER']);
			printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");
			die();
		}
		$club = mysqli_fetch_array($query);
	}
	else
		$club_id = 0;

	// Custom Include Files
	if (isset($_REQUEST['custom_awardee_email']))
		require ("../../salons/$admin_yearmonth/blob/award_winners_actions.php");

	if (isset($_REQUEST['mailing_info_email']))
		require ("../../salons/$admin_yearmonth/blob/mailing_data.php");

	$selection = $_REQUEST['checkbox'];
	foreach($selection as $row_val) {
		list($profile_id, $email) = explode("|", $row_val);		// Break the row spec

		$has_individual_awards = false;
		$has_picture_awards = false;
		$has_picture_acceptances = false;

		if (isset($_REQUEST['earlybird_mail']))
			send_earlybird_mail($profile_id);
		else if(isset($_REQUEST['upload_mail']))
			send_upload_mail($profile_id);
		else if (isset($_REQUEST['judging_mail']))
			send_judging_mail($profile_id);
		else if (isset($_REQUEST['results_mail']))
			send_results_mail($profile_id);
		else if (isset($_REQUEST['catalog_mail']))
			send_catalog_mail($profile_id);
		else if (isset($_REQUEST['catalog_remind']))
			send_catalog_reminder_mail($profile_id);
		else if (isset($_REQUEST['bank_details_mail']))
			send_bank_details_mail($profile_id);
		else if (isset($_REQUEST['club_reminder_mail']))
			send_club_reminder($profile_id, $email, $club);
		else if (isset($_REQUEST['exhibition_invite']))
			send_exhibition_invite($profile_id);
		else if (isset($_REQUEST['custom_awardee_email']))
			send_custom_awardee_email($profile_id);
		else if (isset($_REQUEST['mailing_info_email']))
			send_mailing_info($profile_id);
		else if (isset($_REQUEST['salon_open_mail']))
			send_salon_open_mail($profile_id);
		else if (isset($_REQUEST['salon_reminder_mail']))
			send_salon_reminder_mail($profile_id);

		$count ++;
	}

	$_SESSION['count'] = $count - $counterr - $skipped;
	if($mail_failed == "")
		$_SESSION['success_msg'] = "Mails successfully sent to " . ($count - $counterr - $skipped) . " participants. Errors encountered in sending emails to $counterr participants. Skipped sending emails to $skipped participants";
	else
		$_SESSION['err_msg'] = "Sending email failed for:  " . $mail_failed;

}

// debug_dump("Session", $_SESSION, __FILE__, __LINE__);
// debug_dump("Parameters", $_REQUEST, __FILE__, __LINE__);
$_SESSION['err_msg'] = "Invalid request ";

header("Location: ".$_SERVER['HTTP_REFERER']);
printf("<script>location.href='".$_SERVER['HTTP_REFERER']."'</script>");

?>
