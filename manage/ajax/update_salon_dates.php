<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";


// MAIN
if (isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['edit-dates']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

    $sql = "SELECT * FROM contest WHERE yearmonth = '$yearmonth' ";
    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	if (mysqli_num_rows($query) != 1)
		return_error("Salon $yearmonth not found", __FILE__, __LINE__);

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update contest table
	$sql  = "UPDATE contest ";
	$sql .= "   SET registration_start_date = '" . $_REQUEST['registration_start_date'] . "' ";
	$sql .= "     , registration_last_date = '" . $_REQUEST['registration_last_date'] . "' ";
	$sql .= "     , submission_timezone = '" . $_REQUEST['submission_timezone'] . "' ";
	$sql .= "     , submission_timezone_name = '" . $_REQUEST['submission_timezone_name'] . "' ";
	$sql .= "     , judging_start_date = '" . $_REQUEST['judging_start_date'] . "' ";
	$sql .= "     , judging_end_date = '" . $_REQUEST['judging_end_date'] . "' ";
	$sql .= "     , results_date = '" . $_REQUEST['results_date'] . "' ";
	$sql .= "     , update_start_date = '" . $_REQUEST['update_start_date'] . "' ";
	$sql .= "     , update_end_date = '" . $_REQUEST['update_end_date'] . "' ";
	$sql .= "     , exhibition_start_date = '" . $_REQUEST['exhibition_start_date'] . "' ";
	$sql .= "     , exhibition_end_date = '" . $_REQUEST['exhibition_end_date'] . "' ";
	$sql .= "     , catalog_release_date = '" . $_REQUEST['catalog_release_date'] . "' ";
	$sql .= "     , catalog_order_last_date = '" . $_REQUEST['catalog_order_last_date'] . "' ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// UPDATE Fee Dates
	// if ($_REQUEST['has_earlybird_dates'] == '1') {
	// 	// Fee Structure
	// 	$sql  = "UPDATE fee_structure ";
	// 	$sql .= "   SET fee_start_date = '" . $_REQUEST['earlybird_fee_start_date'] . "' ";
	// 	$sql .= "     , fee_end_date = '" . $_REQUEST['earlybird_fee_end_date'] . "' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	$sql .= "   AND fee_code = 'EARLY BIRD' ";
	// 	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// 	// Discount
	// 	$sql  = "UPDATE discount ";
	// 	$sql .= "   SET discount_start_date = '" . $_REQUEST['earlybird_discount_start_date'] . "' ";
	// 	$sql .= "     , discount_end_date = '" . $_REQUEST['earlybird_discount_end_date'] . "' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	$sql .= "   AND fee_code = 'EARLY BIRD' ";
	// 	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// }

	// if ($_REQUEST['has_regular_dates'] == '1') {
	// 	// Fee Structure
	// 	$sql  = "UPDATE fee_structure ";
	// 	$sql .= "   SET fee_start_date = '" . $_REQUEST['regular_fee_start_date'] . "' ";
	// 	$sql .= "     , fee_end_date = '" . $_REQUEST['regular_fee_end_date'] . "' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	$sql .= "   AND fee_code = 'REGULAR' ";
	// 	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// 	// Discount
	// 	$sql  = "UPDATE discount ";
	// 	$sql .= "   SET discount_start_date = '" . $_REQUEST['regular_discount_start_date'] . "' ";
	// 	$sql .= "     , discount_end_date = '" . $_REQUEST['regular_discount_end_date'] . "' ";
	// 	$sql .= " WHERE yearmonth = '$yearmonth' ";
	// 	$sql .= "   AND fee_code = 'REGULAR' ";
	// 	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	// }

	// Submission Last Dates
	// Digital
	if ($_REQUEST['has_digital_sections'] == '1') {
		$sql  = "UPDATE section ";
		$sql .= "   SET submission_last_date = '" . $_REQUEST['submission_last_date_digital'] . "' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section_type = 'D' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}
	// Print
	if ($_REQUEST['has_print_sections'] == '1') {
		$sql  = "UPDATE section ";
		$sql .= "   SET submission_last_date = '" . $_REQUEST['submission_last_date_print'] . "' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section_type = 'P' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	// Sponsorship Last Date
	if ($_REQUEST['has_sponsorship_dates'] == '1') {
		$sql  = "UPDATE award ";
		$sql .= "   SET sponsorship_last_date = '" . $_REQUEST['sponsorship_last_date'] . "' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND sponsored_awards > 0 ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	}

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	echo json_encode(array("success" => true, "err_msg" => ""));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
