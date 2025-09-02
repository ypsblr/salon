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
if ( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['edit-judging']) ) {

	// Assemble Data
	$yearmonth = $_REQUEST['yearmonth'];
	$sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) == 0)
		return_error("There is no Salon for $yearmonth ", __FILE__, __LINE__, true);

	// Assemble values
	$judging_start_date = null_safe_date($_REQUEST['judging_start_date']);
	$judging_end_date = null_safe_date($_REQUEST['judging_end_date']);
	$results_date = null_safe_date($_REQUEST['results_date']);
	$judging_description_blob = $_REQUEST['judging_description_blob'];
	$has_judging_event = isset($_REQUEST['has_judging_event']) ? "1" : "0";
	$judging_mode = $_REQUEST['judging_mode'];
	$judging_venue = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue']);
	$judging_venue_address = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue_address']);
	$judging_venue_location_map = mysqli_real_escape_string($DBCON, $_REQUEST['judging_venue_location_map']);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Insert if New
	// *** NOTE - Do not place date variables under quotes - they are already quoted by null_safe_date function
	$sql  = "UPDATE contest ";
	$sql .= "   SET judging_start_date = $judging_start_date ";
	$sql .= "     , judging_end_date = $judging_end_date ";
	$sql .= "     , results_date = $results_date ";
	$sql .= "     , judging_description_blob = '$judging_description_blob' ";
	$sql .= "     , has_judging_event = '$has_judging_event' ";
	$sql .= "     , judging_mode = '$judging_mode' ";
	$sql .= "     , judging_venue = '$judging_venue' ";
	$sql .= "     , judging_venue_address = '$judging_venue_address' ";
	$sql .= "     , judging_venue_location_map = '$judging_venue_location_map' ";
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
