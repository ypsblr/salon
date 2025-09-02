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
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['edit-salon']) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("There is no Salon for $yearmonth ", __FILE__, __LINE__, true);

	// Assemble values
	$contest_name = mysqli_real_escape_string($DBCON, $_REQUEST['contest_name']);
	$is_salon = isset($_REQUEST['is_salon']) ? "1" : "0";
	$is_international = isset($_REQUEST['is_international']) ? "1" : "0";
	$is_no_to_past_acceptance = isset($_REQUEST['is_no_to_past_acceptance']) ? "1" : "0";
	$registration_start_date = null_safe_date($_REQUEST['registration_start_date']);
	$registration_last_date = null_safe_date($_REQUEST['registration_last_date']);
	$submission_timezone = $_REQUEST['submission_timezone'];
	$submission_timezone_name = $_REQUEST['submission_timezone_name'];
	$contest_description_blob = $_REQUEST['contest_description_blob'];
	$terms_conditions_blob = $_REQUEST['terms_conditions_blob'];
	$contest_announcement_blob = $_REQUEST['contest_announcement_blob'];
	$judging_start_date = null_safe_date($_REQUEST['judging_start_date']);
	$judging_end_date = null_safe_date($_REQUEST['judging_end_date']);
	$results_date = null_safe_date($_REQUEST['results_date']);
	$exhibition_start_date = null_safe_date($_REQUEST['exhibition_end_date']);
	$exhibition_end_date = null_safe_date($_REQUEST['exhibition_end_date']);
	$max_pics_per_entry = $_REQUEST['max_pics_per_entry'];
	$max_width = $_REQUEST['max_width'];
	$max_height = $_REQUEST['max_height'];
	$max_file_size_in_mb = $_REQUEST['max_file_size_in_mb'];
	$review_in_progress = isset($_REQUEST['review_in_progress']) ? "1" : "0";

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert if New
	// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
	$sql  = "UPDATE contest ";
	$sql .= "   SET contest_name = '$contest_name' ";
	$sql .= "     , is_salon = '$is_salon' ";
	$sql .= "     , is_international = '$is_international' ";
	$sql .= "     , is_no_to_past_acceptance = '$is_no_to_past_acceptance' ";
	$sql .= "     , registration_start_date = $registration_start_date ";		// null_safe_date adds quotes for non NULL date values
	$sql .= "     , registration_last_date = $registration_last_date ";
	$sql .= "     , submission_timezone = '$submission_timezone' ";
	$sql .= "     , submission_timezone_name = '$submission_timezone_name' ";
	$sql .= "     , contest_description_blob = '$contest_description_blob' ";
	$sql .= "     , terms_conditions_blob = '$terms_conditions_blob' ";
	$sql .= "     , contest_announcement_blob = '$contest_announcement_blob' ";
	$sql .= "     , judging_start_date = $judging_start_date ";
	$sql .= "     , judging_end_date = $judging_end_date ";
	$sql .= "     , results_date = $results_date ";
	$sql .= "     , exhibition_start_date = $exhibition_start_date ";
	$sql .= "     , exhibition_end_date = $exhibition_end_date ";
	$sql .= "     , max_pics_per_entry = '$max_pics_per_entry' ";
	$sql .= "     , max_width = '$max_width' ";
	$sql .= "     , max_height = '$max_height' ";
	$sql .= "     , max_file_size_in_mb = '$max_file_size_in_mb' ";
	$sql .= "     , review_in_progress = '$review_in_progress' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

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
