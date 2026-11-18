<?php
register_shutdown_function(function () {
	$err = error_get_last();
	if (!$err)
		return;
	$fatal = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
	if (!in_array($err['type'], $fatal, true))
		return;
	$dir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
	if (!is_dir($dir))
		@mkdir($dir, 0755, true);
	@file_put_contents(
		$dir . "/debug.txt",
		date("Y-m-d H:i:s") . " clone_salon FATAL: " . $err['message'] . " in " . $err['file'] . ":" . $err['line'] . "\r\n",
		FILE_APPEND
	);
});

include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function clone_salon_log($message, $extra = null) {
	$dir = $_SERVER["DOCUMENT_ROOT"] . "/logs";
	if (!is_dir($dir))
		@mkdir($dir, 0755, true);
	$line = date("Y-m-d H:i:s") . " clone_salon: " . $message . "\r\n";
	if ($extra !== null)
		$line .= print_r($extra, true) . "\r\n";
	@file_put_contents($dir . "/debug.txt", $line, FILE_APPEND);
}

function clone_salon_fail($step, $sql, $errmsg) {
	clone_salon_log("FAILED at $step: $errmsg", $sql);
	sql_json_error($sql, $errmsg, __FILE__, __LINE__);
}

function clone_salon_query($DBCON, $step, $sql) {
	clone_salon_log("start $step");
	try {
		$query = mysqli_query($DBCON, $sql);
	} catch (Throwable $e) {
		clone_salon_fail($step, $sql, get_class($e) . ": " . $e->getMessage());
	}
	if ($query === false)
		clone_salon_fail($step, $sql, mysqli_error($DBCON));
	clone_salon_log("ok $step");
	return $query;
}

clone_salon_log("request", array(
	"method" => isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "",
	"admin_id_set" => isset($_SESSION['admin_id']) ? "yes" : "no",
	"yearmonth" => isset($_REQUEST['yearmonth']) ? $_REQUEST['yearmonth'] : "",
	"cln_yearmonth" => isset($_REQUEST['cln_yearmonth']) ? $_REQUEST['cln_yearmonth'] : "",
	"cln_contest_name" => isset($_REQUEST['cln_contest_name']) ? $_REQUEST['cln_contest_name'] : "",
	"clone_button" => isset($_REQUEST['clone-salon-action']) ? "yes" : "no",
));

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['cln_yearmonth']) && isset($_REQUEST['clone-salon-action']) ) {

	$src_yearmonth = $_REQUEST['yearmonth'];	// Source
	$yearmonth = $_REQUEST['cln_yearmonth'];	// Target
	$contest_name = mysqli_real_escape_string($DBCON, isset($_REQUEST['cln_contest_name']) ? $_REQUEST['cln_contest_name'] : "");
	clone_salon_log("cloning $src_yearmonth -> $yearmonth ($contest_name)");

	// Check if contests already exists for target yearmonth
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = clone_salon_query($DBCON, "check target contest", $sql);
	if (mysqli_num_rows($query) != 0)
		return_error("A Salon for the yearmonth already exists", __FILE__, __LINE__, true);

	// Start Transaction
	$sql = "START TRANSACTION";
	clone_salon_query($DBCON, "start transaction", $sql);

	// Clone CONTEST row for the salon
	$sql  = "INSERT INTO contest ";
	$sql .= "SELECT '$yearmonth', '$contest_name', is_salon, is_international, is_no_to_past_acceptance, ";
	$sql .= "       contest_description_blob, terms_conditions_blob, contest_announcement_blob, fee_structure_blob, ";
	$sql .= "       discount_structure_blob, registration_start_date, registration_last_date, submission_timezone, ";
	$sql .= "       submission_timezone_name, judging_start_date, judging_end_date, results_date, update_start_date, ";
	$sql .= "       update_end_date, exhibition_start_date, exhibition_end_date, has_judging_event, has_exhibition, ";
	$sql .= "       has_catalog, review_in_progress, '0', judging_mode, judging_description_blob, ";
	// $sql .= "       has_catalog, review_in_progress, judging_in_progress, judging_mode, judging_description_blob, ";
	$sql .= "       judging_venue, judging_venue_address, judging_venue_location_map, judging_report_blob, ";
	$sql .= "       judging_photos_php, '0', '0', results_description_blob, exhibition_name, ";
	// $sql .= "       judging_photos_php, results_ready, certificates_ready, results_description_blob, exhibition_name, ";
	$sql .= "       exhibition_description_blob, exhibition_venue, exhibition_venue_address, exhibition_venue_location_map, ";
	$sql .= "       exhibition_report_blob, exhibition_photos_php, inauguration_photos_php, NULL, ";
	// $sql .= "       exhibition_report_blob, exhibition_photos_php, inauguration_photos_php, catalog_release_date, ";
	$sql .= "       '0', '', '', NULL, catalog_price_in_inr, ";
	// $sql .= "       catalog_ready, catalog, catalog_download, catalog_order_last_date, catalog_price_in_inr, ";
	$sql .= "       catalog_price_in_usd, chairman_message_blob, max_pics_per_entry, max_width, max_height, ";
	// $sql .= "       max_file_size_in_mb, fee_model, num_entries, num_women, num_pictures, num_awards, num_hms, ";
	$sql .= "       max_file_size_in_mb, fee_model, 0, 0, 0, 0, 0, ";
	// $sql .= "       num_acceptances, num_winners, web_pics, archived ";
	$sql .= "       0, 0, web_pics, 0 ";
	$sql .= "  FROM contest WHERE yearmonth = '$src_yearmonth' ";
	clone_salon_query($DBCON, "insert contest", $sql);

	// Clone SECTIONS for the salon
	$sql  = "INSERT INTO section ";
	$sql .= "SELECT '$yearmonth', section, section_type, section_sequence, stub, description, rules, rules_blob, ";
	// $sql .= "       submission_last_date, max_pics_per_entry, cut_off_score, num_entrants, num_pictures, ";
	$sql .= "       submission_last_date, max_pics_per_entry, 0, 0, 0, ";
	// $sql .= "       num_awards, num_hms, num_acceptances, num_winners ";
	$sql .= "       0, 0, 0, 0 ";
	$sql .= "  FROM section WHERE yearmonth = '$src_yearmonth' ";
	clone_salon_query($DBCON, "insert section", $sql);

	// Clone ENTRANT CATEGORIES
	$sql  = "INSERT INTO entrant_category ";
	$sql .= "SELECT '$yearmonth', entrant_category, entrant_category_name, yps_membership_required, yps_member_prefixes, ";
	$sql .= "       gender_must_match, gender_match, age_within_range, age_minimum, age_maximum, country_must_match, ";
	$sql .= "       country_codes, country_excludes, state_must_match, state_names, currency, can_create_club, ";
	$sql .= "       fee_waived, acceptance_reported, award_group, fee_group, default_participation_code, ";
	$sql .= "       default_digital_sections, default_print_sections, discount_group ";
	$sql .= "  FROM entrant_category WHERE yearmonth = '$src_yearmonth' ";
	clone_salon_query($DBCON, "insert entrant_category", $sql);

	// Clone AWARD
	$sql  = "INSERT INTO award ";
	$sql .= "SELECT '$yearmonth', award_id, level, sequence, section, award_group, award_type, award_name, recognition_code, ";
	$sql .= "       description, number_of_awards, award_weight, has_medal, has_pin, has_ribbon, has_memento, has_gift, ";
	$sql .= "       has_certificate, cash_award, sponsored_awards, sponsorship_per_award, partial_sponsorship_permitted, ";
	$sql .= "       sponsorship_last_date ";
	$sql .= "  FROM award WHERE yearmonth = '$src_yearmonth' ";
	clone_salon_query($DBCON, "insert award", $sql);

	// Clone FEES & DISCOUNT structures
	// FEE structure
	$sql  = "INSERT INTO fee_structure ";
	$sql .= "SELECT '$yearmonth', fee_code, fee_group, participation_code, currency, description, digital_sections, ";
	$sql .= "       print_sections, fee_start_date, fee_end_date, fees, exclusive ";
	$sql .= "  FROM fee_structure WHERE yearmonth = '$src_yearmonth' ";
	clone_salon_query($DBCON, "insert fee_structure", $sql);

	 // DISCOUNT structure
	 $sql  = "INSERT INTO discount ";
	 $sql .= "SELECT '$yearmonth', discount_code, fee_code, discount_group, participation_code, currency, group_code, ";
	 $sql .= "       minimum_group_size, maximum_group_size, discount_start_date, discount_end_date, discount, ";
	 $sql .= "       discount_percentage, discount_round_digits ";
	 $sql .= "  FROM discount WHERE yearmonth = '$src_yearmonth' AND group_code NOT LIKE 'GDC%' ";
	 clone_salon_query($DBCON, "insert discount", $sql);

	 // Clone Essential Team
	 $sql  = "INSERT INTO team ";
	 $sql .= "SELECT '$yearmonth', member_id, member_login_id, member_password, level, sequence, role, role_name, ";
	 $sql .= "       member_group, member_name, honors, phone, email, profile, avatar, permissions, sections, ";
	 $sql .= "       is_reviewer, is_print_coordinator, allow_downloads, address, reviewed ";
	 $sql .= "  FROM team WHERE yearmonth = '$src_yearmonth' ";
	 $sql .= "   AND role IN ('Chairman', 'Secretary', 'Webmaster') ";
	 clone_salon_query($DBCON, "insert team", $sql);

	 $sql = "COMMIT";
	 clone_salon_query($DBCON, "commit", $sql);

	// Create Required Folders
	$salon_folder = "../../salons/$yearmonth";
	if (! is_dir($salon_folder)) mkdir($salon_folder);
	if (! is_dir("$salon_folder/blob")) mkdir("$salon_folder/blob");
	if (! is_dir("$salon_folder/img")) mkdir("$salon_folder/img");
	if (! is_dir("$salon_folder/img/com")) mkdir("$salon_folder/img/com");
	if (! is_dir("$salon_folder/img/recognition")) mkdir("$salon_folder/img/recognition");
	if (! is_dir("$salon_folder/img/sponsor")) mkdir("$salon_folder/img/sponsor");

	 clone_salon_log("clone finished for $yearmonth");
	 $resArray = [];
	 $resArray['success'] = TRUE;
	 $resArray['msg'] = "";
	 echo json_encode($resArray);
	 die();
}
else {
	clone_salon_log("rejected request: missing admin session or yearmonth / cln_yearmonth / clone-salon-action");
	return_error("Invalid Update Request", __FILE__, __LINE__, true);
}

?>
