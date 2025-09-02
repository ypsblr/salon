<?php
// session_start();
include("../inc/session.php");
include "../inc/connect.php";
include "ajax_lib.php";


// MAIN
if (isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

    $sql  = "SELECT registration_start_date, registration_last_date, submission_timezone, submission_timezone_name, ";
	$sql .= "       judging_start_date, judging_end_date, results_date, update_start_date, update_end_date, ";
	$sql .= "       exhibition_start_date, exhibition_end_date, catalog_release_date, catalog_order_last_date ";
	$sql .= "  FROM contest WHERE yearmonth = '$yearmonth' ";
    $query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
    $salon = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Get Fee Dates
	$sql  = "SELECT fee_code, MIN(fee_start_date) AS fee_start_date, MAX(fee_end_date) AS fee_end_date ";
	$sql .= "  FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= " GROUP BY fee_code ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon['has_earlybird_fee'] = false;
	$salon['has_regular_fee'] = false;
	$salon['earlybird_fee_start_date'] = null;
	$salon['earlybird_fee_end_date'] = null;
	$salon['regular_fee_start_date'] = null;
	$salon['regular_fee_end_date'] = null;
	while ($row = mysqli_fetch_array($query)) {
		if ($row['fee_code'] == "REGULAR") {
			$salon['regular_fee_start_date'] = $row['fee_start_date'];
			$salon['regular_fee_end_date'] = $row['fee_end_date'];
			$salon['has_regular_fee'] = true;
		}
		else {
			$salon['earlybird_fee_start_date'] = $row['fee_start_date'];
			$salon['earlybird_fee_end_date'] = $row['fee_end_date'];
			$salon['has_earlybird_fee'] = true;
		}
	}

	// Get Discount Dates
	$sql  = "SELECT fee_code, MIN(discount_start_date) AS discount_start_date, MAX(discount_end_date) AS discount_end_date ";
	$sql .= "  FROM discount ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND discount_code = 'CLUB' ";
	$sql .= " GROUP BY fee_code ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon['has_earlybird_discount'] = false;
	$salon['has_regular_discount'] = false;
	$salon['earlybird_discount_start_date'] = null;
	$salon['earlybird_discount_end_date'] = null;
	$salon['regular_discount_start_date'] = null;
	$salon['regular_discount_end_date'] = null;
	while ($row = mysqli_fetch_array($query)) {
		if ($row['fee_code'] == "REGULAR") {
			$salon['regular_discount_start_date'] = $row['discount_start_date'];
			$salon['regular_discount_end_date'] = $row['discount_end_date'];
			$salon['has_regular_discount'] = true;
		}
		else {
			$salon['earlybird_discount_start_date'] = $row['discount_start_date'];
			$salon['earlybird_discount_end_date'] = $row['discount_end_date'];
			$salon['has_earlybird_discount'] = true;
		}
	}

	// Submission Last Dates
	$sql  = "SELECT section_type, MAX(submission_last_date) AS submission_last_date ";
	$sql .= "  FROM section ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= " GROUP BY section_type ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon['has_print_sections'] = false;
	$salon['submission_last_date_print'] = null;
	$salon['submission_last_date_digital'] = null;
	while ($row = mysqli_fetch_array($query)) {
		if ($row['section_type'] == "P") {
			$salon['has_print_sections'] = true;
			$salon['submission_last_date_print'] = $row['submission_last_date'];
		}
		else
			$salon['submission_last_date_digital'] = $row['submission_last_date'];
	}

	// Sponsorship Last Date
	$sql  = "SELECT MAX(sponsorship_last_date) AS sponsorship_last_date ";
	$sql .= "  FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND sponsored_awards > 0 ";
	$query = mysqli_query($DBCON, $sql) or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$salon['has_sponsorship'] = false;
	$salon['sponsorship_last_date'] = NULL;
	if (mysqli_num_rows($query) > 0) {
		$row = mysqli_fetch_array($query);
		if ($row['sponsorship_last_date'] != NULL) {
			$salon['sponsorship_last_date'] = $row['sponsorship_last_date'];
			$salon['has_sponsorship'] = true;
		}
	}

	echo json_encode(array("success" => true, "salon" => $salon));
}
else {
	//$_SESSION['err_msg'] = "Invalid parameters";
	return_error("Invalid parameters", __FILE__, __LINE__);
}
?>
