<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");


// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['cln_yearmonth']) && isset($_REQUEST['clone-salon-action']) ) {

	$src_yearmonth = $_REQUEST['yearmonth'];	// Source
	$yearmonth = $_REQUEST['cln_yearmonth'];	// Target
	$contest_name = mysqli_real_escape_string($DBCON, $_REQUEST['cln_contest_name']);

	// Check if contests already exists for target yearmonth
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) != 0)
		return_error("A Salon for the yearmonth already exists", __FILE__, __LINE__, true);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

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
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Clone SECTIONS for the salon
	$sql  = "INSERT INTO section ";
	$sql .= "SELECT '$yearmonth', section, section_type, section_sequence, stub, description, rules, rules_blob, ";
	// $sql .= "       submission_last_date, max_pics_per_entry, cut_off_score, num_entrants, num_pictures, ";
	$sql .= "       submission_last_date, max_pics_per_entry, 0, 0, 0, ";
	// $sql .= "       num_awards, num_hms, num_acceptances, num_winners ";
	$sql .= "       0, 0, 0, 0 ";
	$sql .= "  FROM section WHERE yearmonth = '$src_yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Clone ENTRANT CATEGORIES
	$sql  = "INSERT INTO entrant_category ";
	$sql .= "SELECT '$yearmonth', entrant_category, entrant_category_name, yps_membership_required, yps_member_prefixes, ";
	$sql .= "       gender_must_match, gender_match, age_within_range, age_minimum, age_maximum, country_must_match, ";
	$sql .= "       country_codes, country_excludes, state_must_match, state_names, currency, can_create_club, ";
	$sql .= "       fee_waived, acceptance_reported, award_group, fee_group, default_participation_code, ";
	$sql .= "       default_digital_sections, default_print_sections, discount_group ";
	$sql .= "  FROM entrant_category WHERE yearmonth = '$src_yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Clone AWARD
	$sql  = "INSERT INTO award ";
	$sql .= "SELECT '$yearmonth', award_id, level, sequence, section, award_group, award_type, award_name, recognition_code, ";
	$sql .= "       description, number_of_awards, award_weight, has_medal, has_pin, has_ribbon, has_memento, has_gift, ";
	$sql .= "       has_certificate, cash_award, sponsored_awards, sponsorship_per_award, partial_sponsorship_permitted, ";
	$sql .= "       sponsorship_last_date ";
	$sql .= "  FROM award WHERE yearmonth = '$src_yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Clone FEES & DISCOUNT structures
	// FEE structure
	$sql  = "INSERT INTO fee_structure ";
	$sql .= "SELECT '$yearmonth', fee_code, fee_group, participation_code, currency, description, digital_sections, ";
	$sql .= "       print_sections, fee_start_date, fee_end_date, fees, exclusive ";
	$sql .= "  FROM fee_structure WHERE yearmonth = '$src_yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 // DISCOUNT structure
	 $sql  = "INSERT INTO discount ";
	 $sql .= "SELECT '$yearmonth', discount_code, fee_code, discount_group, participation_code, currency, group_code, ";
	 $sql .= "       minimum_group_size, maximum_group_size, discount_start_date, discount_end_date, discount, ";
	 $sql .= "       discount_percentage, discount_round_digits ";
	 $sql .= "  FROM discount WHERE yearmonth = '$src_yearmonth' AND group_code NOT LIKE 'GDC%' ";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 // Clone Essential Team
	 $sql  = "INSERT INTO team ";
	 $sql .= "SELECT '$yearmonth', member_id, member_login_id, member_password, level, sequence, role, role_name, ";
	 $sql .= "       member_group, member_name, honors, phone, email, profile, avatar, permissions, sections, ";
	 $sql .= "       is_reviewer, is_print_coordinator, allow_downloads, address, reviewed ";
	 $sql .= "  FROM team WHERE yearmonth = '$src_yearmonth' ";
	 $sql .= "   AND role IN ('Chairman', 'Secretary', 'Webmaster') ";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	 $sql = "COMMIT";
	 mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Create Required Folders
	$salon_folder = "../../salons/$yearmonth";
	if (! is_dir($salon_folder)) mkdir($salon_folder);
	if (! is_dir("$salon_folder/blob")) mkdir("$salon_folder/blob");
	if (! is_dir("$salon_folder/img")) mkdir("$salon_folder/img");
	if (! is_dir("$salon_folder/img/com")) mkdir("$salon_folder/img/com");
	if (! is_dir("$salon_folder/img/recognition")) mkdir("$salon_folder/img/recognition");
	if (! is_dir("$salon_folder/img/sponsor")) mkdir("$salon_folder/img/sponsor");

	 $resArray = [];
	 $resArray['success'] = TRUE;
	 $resArray['msg'] = "";
	 echo json_encode($resArray);
	 die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
