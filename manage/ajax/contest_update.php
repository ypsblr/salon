<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function null_safe_date($date) {
	if ($date == NULL || $date == "")
		return "NULL";
	else
		return "'" . $date . "'";
}

function sort_on_price($catalog_list) {
	if (sizeof($catalog_list) <= 1)
		return $catalog_list;		// Nothing to sort

	// Do Bubble Sort on Price till no records are swapped
	do {
		$swaps = 0;
		for ($i = 1; $i < sizeof($catalog_list); ++ $i) {
			if ($catalog_list[$i]["price"] < $catalog_list[$i-1]["price"]) {
				$temp = $catalog_list[$i];
				$catalog_list[$i] = $catalog_list[$i-1];
				$catalog_list[$i-1] = $temp;
				++ $swaps;
			}
		}
	} while ($swaps > 0);

	return $catalog_list;
}


// Main Code
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['edit-update']) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$is_new_salon = $_REQUEST['new_salon'] == '1';
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if ((! $is_new_salon) && mysqli_num_rows($query) == 0)
		return_error("Salon for $yearmonth not found", __FILE__, __LINE__, true);
	if ($is_new_salon && mysqli_num_rows($query) != 0)
		return_error("There is already a Salon for $yearmonth ", __FILE__, __LINE__, true);

	// Assemble Catalog Price Json strings
	$catalog_price_in_inr = "";
	$catalog_price_in_usd = "";
	if (isset($_REQUEST['catalog_model']) && isset($_REQUEST['catalog_currency']) &&
			isset($_REQUEST['catalog_price']) && isset($_REQUEST['catalog_postage'])) {
		$catalog_inr = [];
		$catalog_usd = [];
		for ($i = 0; $i < sizeof($_REQUEST['catalog_model']); ++ $i) {
			if ($_REQUEST['catalog_currency'][$i] == "INR") {
				$catalog_inr[] = array(
									"currency" => $_REQUEST['catalog_currency'][$i],
									"model" => $_REQUEST['catalog_model'][$i],
									"price" => $_REQUEST['catalog_price'][$i],
									"postage" => $_REQUEST['catalog_postage']
								);
			}
			else {
				$catalog_usd[] = array(
									"currency" => $_REQUEST['catalog_currency'][$i],
									"model" => $_REQUEST['catalog_model'][$i],
									"price" => $_REQUEST['catalog_price'][$i],
									"postage" => $_REQUEST['catalog_postage']
								);
			}
		}
		$catalog_price_in_inr = json_encode(sort_on_price($catalog_inr));
		$catalog_price_in_usd = json_encode(sort_on_price($catalog_usd));
	}

	// Assemble values
	$contest_name = mysqli_real_escape_string($DBCON, $_REQUEST['contest_name']);
	$is_salon = isset($_REQUEST['is_salon']) ? "1" : "0";
	$is_international = isset($_REQUEST['is_international']) ? "1" : "0";
	$is_no_to_past_acceptance = isset($_REQUEST['is_no_to_past_acceptance']) ? "1" : "0";
	$contest_description_blob = $_REQUEST['contest_description_blob'];
	$terms_conditions_blob = $_REQUEST['terms_conditions_blob'];
	$contest_announcement_blob = $_REQUEST['contest_announcement_blob'];
	$fee_structure_blob = $_REQUEST['fee_structure_blob'];
	$discount_structure_blob = $_REQUEST['discount_structure_blob'];
	$registration_start_date = null_safe_date($_REQUEST['registration_start_date']);
	$registration_last_date = null_safe_date($_REQUEST['registration_last_date']);
	$submission_timezone = $_REQUEST['submission_timezone'];
	$submission_timezone_name = $_REQUEST['submission_timezone_name'];
	$judging_start_date = null_safe_date($_REQUEST['judging_start_date']);
	$judging_end_date = null_safe_date($_REQUEST['judging_end_date']);
	$results_date = null_safe_date($_REQUEST['results_date']);
	$update_start_date = null_safe_date($_REQUEST['update_start_date']);
	$update_end_date = null_safe_date($_REQUEST['update_end_date']);
	$exhibition_start_date = null_safe_date($_REQUEST['exhibition_end_date']);
	$exhibition_end_date = null_safe_date($_REQUEST['exhibition_end_date']);
	$has_judging_event = isset($_REQUEST['has_judging_event']) ? "1" : "0";
	$has_exhibition = isset($_REQUEST['has_exhibition']) ? "1" : "0";
	$has_catalog = isset($_REQUEST['has_catalog']) ? "1" : "0";
	$judging_mode = $_REQUEST['judging_mode'];
	$judging_description_blob = $_REQUEST['judging_description_blob'];
	$judging_venue = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue']);
	$judging_venue_address = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue_address']);
	$judging_venue_location_map = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue_location_map']);
	$judging_report_blob = $_REQUEST['judging_report_blob'];
	$judging_photos_php = $_REQUEST['judging_photos_php'];
	$results_ready = isset($_REQUEST['results_ready']) ? "1" : "0";
	$certificates_ready = isset($_REQUEST['certificates_ready']) ? "1" : "0";
	$results_description_blob = $_REQUEST['results_description_blob'];
	$exhibition_name = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_name']);
	$exhibition_description_blob = $_REQUEST['exhibition_description_blob'];
	$exhibition_venue = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue']);
	$exhibition_venue_address = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue_address']);
	$exhibition_venue_location_map = mysqli_real_escape_string($DBCON, $_REQUEST['exhibition_venue_location_map']);
	$exhibition_report_blob = $_REQUEST['exhibition_report_blob'];
	$exhibition_photos_php = $_REQUEST['exhibition_photos_php'];
	$inauguration_photos_php = $_REQUEST['inauguration_photos_php'];
	$catalog_release_date = null_safe_date($_REQUEST['catalog_release_date']);
	$catalog_ready = isset($_REQUEST['catalog_ready']) ? "1" : "0";
	$catalog = $_REQUEST['catalog'];
	$catalog_download = $_REQUEST['catalog_download'];
	$catalog_order_last_date = null_safe_date($_REQUEST['catalog_order_last_date']);
	$chairman_message_blob = $_REQUEST['chairman_message_blob'];
	$max_pics_per_entry = $_REQUEST['max_pics_per_entry'];
	$max_width = $_REQUEST['max_width'];
	$max_height = $_REQUEST['max_height'];
	$max_file_size_in_mb = $_REQUEST['max_file_size_in_mb'];
	$fee_model = isset($_REQUEST['fee_model']) ? $_REQUEST['fee_model'] : "POLICY";
	$review_in_progress = isset($_REQUEST['review_in_progress']) ? "1" : "0";

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert if New
	if ($is_new_salon) {
		// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
		$sql  = "INSERT INTO contest ";
		$sql .= " ( yearmonth, contest_name, is_salon, is_international, is_no_to_past_acceptance ";
		$sql .= "   , contest_description_blob, terms_conditions_blob, contest_announcement_blob, fee_structure_blob ";
		$sql .= "   , discount_structure_blob, registration_start_date, registration_last_date, submission_timezone ";
		$sql .= "   , submission_timezone_name, judging_start_date, judging_end_date, results_date, update_start_date ";
		$sql .= "   , update_end_date, exhibition_start_date, exhibition_end_date, has_judging_event, has_exhibition ";
		$sql .= "   , has_catalog, judging_mode, judging_description_blob, judging_venue, judging_venue_address ";
		$sql .= "   , judging_venue_location_map, judging_report_blob, judging_photos_php, results_ready ";
		$sql .= "   , certificates_ready, results_description_blob, exhibition_name, exhibition_description_blob ";
		$sql .= "   , exhibition_venue, exhibition_venue_address, exhibition_venue_location_map, exhibition_report_blob ";
		$sql .= "   , exhibition_photos_php, inauguration_photos_php, catalog_release_date, catalog_ready, catalog ";
		$sql .= "   , catalog_download, catalog_order_last_date, catalog_price_in_inr, catalog_price_in_usd ";
		$sql .= "   , chairman_message_blob, max_pics_per_entry, max_width, max_height, max_file_size_in_mb, fee_model ";
		$sql .= "   , review_in_progress ";
		$sql .= " ) ";
		$sql .= " VALUES ( ";
		$sql .= " '$yearmonth', '$contest_name', '$is_salon', '$is_international', '$is_no_to_past_acceptance' ";
		$sql .= " , '$contest_description_blob', '$terms_conditions_blob', '$contest_announcement_blob', '$fee_structure_blob' ";
		$sql .= " , '$discount_structure_blob', $registration_start_date, $registration_last_date, '$submission_timezone' ";
		$sql .= " , '$submission_timezone_name', $judging_start_date, $judging_end_date, $results_date, $update_start_date ";
		$sql .= " , $update_end_date, $exhibition_start_date, $exhibition_end_date, '$has_judging_event', '$has_exhibition' ";
		$sql .= " , '$has_catalog', '$judging_mode', '$judging_description_blob', '$judging_venue', '$judging_venue_address' ";
		$sql .= " , '$judging_venue_location_map', '$judging_report_blob', '$judging_photos_php', '$results_ready' ";
		$sql .= " , '$certificates_ready', '$results_description_blob', '$exhibition_name', '$exhibition_description_blob' ";
		$sql .= " , '$exhibition_venue', '$exhibition_venue_address', '$exhibition_venue_location_map', '$exhibition_report_blob' ";
		$sql .= " , '$exhibition_photos_php', '$inauguration_photos_php', $catalog_release_date, '$catalog_ready', '$catalog' ";
		$sql .= " , '$catalog_download', $catalog_order_last_date, '$catalog_price_in_inr', '$catalog_price_in_usd' ";
		$sql .= " , '$chairman_message_blob', '$max_pics_per_entry', '$max_width', '$max_height', '$max_file_size_in_mb', '$fee_model' ";
		$sql .= " , '$review_in_progress' ";
		$sql .= " ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

		// Create Required Folders
		$salon_folder = "../../salons/$yearmonth";
		if (! is_dir($salon_folder)) mkdir($salon_folder);
		if (! is_dir("$salon_folder/blob")) mkdir("$salon_folder/blob");
		if (! is_dir("$salon_folder/img")) mkdir("$salon_folder/img");
		if (! is_dir("$salon_folder/img/com")) mkdir("$salon_folder/img/com");
		if (! is_dir("$salon_folder/img/recognition")) mkdir("$salon_folder/img/recognition");
		if (! is_dir("$salon_folder/img/sponsor")) mkdir("$salon_folder/img/sponsor");
	}
	else {
		// Update existing salon
		// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
		$sql  = "UPDATE contest ";
		$sql .= "   SET contest_name = '$contest_name' ";
		$sql .= "     , is_salon = '$is_salon' ";
		$sql .= "     , is_international = '$is_international' ";
		$sql .= "     , is_no_to_past_acceptance = '$is_no_to_past_acceptance' ";
		$sql .= "     , contest_description_blob = '$contest_description_blob' ";
		$sql .= "     , terms_conditions_blob = '$terms_conditions_blob' ";
		$sql .= "     , contest_announcement_blob = '$contest_announcement_blob' ";
		$sql .= "     , fee_structure_blob = '$fee_structure_blob' ";
		$sql .= "     , discount_structure_blob = '$discount_structure_blob' ";
		$sql .= "     , registration_start_date = $registration_start_date ";
		$sql .= "     , registration_last_date = $registration_last_date ";
		$sql .= "     , submission_timezone = '$submission_timezone' ";
		$sql .= "     , submission_timezone_name = '$submission_timezone_name' ";
		$sql .= "     , judging_start_date = $judging_start_date ";
		$sql .= "     , judging_end_date = $judging_end_date ";
		$sql .= "     , results_date = $results_date ";
		$sql .= "     , update_start_date = $update_start_date ";
		$sql .= "     , update_end_date = $update_end_date ";
		$sql .= "     , exhibition_start_date = $exhibition_start_date ";
		$sql .= "     , exhibition_end_date = $exhibition_end_date ";
		$sql .= "     , has_judging_event = '$has_judging_event' ";
		$sql .= "     , has_exhibition = '$has_exhibition' ";
		$sql .= "     , has_catalog = '$has_catalog' ";
		$sql .= "     , judging_mode = '$judging_mode' ";
		$sql .= "     , judging_description_blob = '$judging_description_blob' ";
		$sql .= "     , judging_venue = '$judging_venue' ";
		$sql .= "     , judging_venue_address = '$judging_venue_address' ";
		$sql .= "     , judging_venue_location_map = '$judging_venue_location_map' ";
		$sql .= "     , judging_report_blob = '$judging_report_blob' ";
		$sql .= "     , judging_photos_php = '$judging_photos_php' ";
		$sql .= "     , results_ready = '$results_ready' ";
		$sql .= "     , certificates_ready = '$certificates_ready' ";
		$sql .= "     , results_description_blob = '$judging_description_blob' ";
		$sql .= "     , exhibition_name = '$exhibition_name' ";
		$sql .= "     , exhibition_description_blob = '$exhibition_description_blob' ";
		$sql .= "     , exhibition_venue = '$exhibition_venue' ";
		$sql .= "     , exhibition_venue_address = '$exhibition_venue_address' ";
		$sql .= "     , exhibition_venue_location_map = '$exhibition_venue_location_map' ";
		$sql .= "     , exhibition_photos_php = '$exhibition_photos_php' ";
		$sql .= "     , inauguration_photos_php = '$inauguration_photos_php' ";
		$sql .= "     , catalog_release_date = $catalog_release_date ";
		$sql .= "     , catalog_ready = '$catalog_ready' ";
		$sql .= "     , catalog = '$catalog' ";
		$sql .= "     , catalog_download = '$catalog_download' ";
		$sql .= "     , catalog_order_last_date = $catalog_order_last_date ";
		$sql .= "     , catalog_price_in_inr = '$catalog_price_in_inr' ";
		$sql .= "     , catalog_price_in_usd = '$catalog_price_in_usd' ";
		$sql .= "     , chairman_message_blob = '$chairman_message_blob' ";
		$sql .= "     , max_pics_per_entry = '$max_pics_per_entry' ";
		$sql .= "     , max_width = '$max_width' ";
		$sql .= "     , max_height = '$max_height' ";
		$sql .= "     , max_file_size_in_mb = '$max_file_size_in_mb' ";
		$sql .= "     , fee_model = '$fee_model' ";
		$sql .= "     , review_in_progress = '$review_in_progress' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
