<?php
session_save_path(__DIR__ . "/../inc/session");
session_start();
include("../inc/connect.php");
include("ajax_lib.php");

// A function to return an error message and stop
function return_error($errmsg) {
	$resArray = array();
	$resArray['success'] = FALSE;
	$resArray['msg'] = $errmsg;
	echo json_encode($resArray);
	die;
}

// Usage: $query = mysql_query($sql) or sql_error($sql, mysql_error(), __FILE__, __LINE__);
function sql_json_error($sql, $errmsg, $phpfile, $phpline) {
	log_sql_error($sql, $errmsg, $phpfile, $phpline);
	return_error("Server Error");
	die;
}


if (isset($_REQUEST['entrant_category']) && isset($_REQUEST['currency']) && isset($_REQUEST['digital_sections']) && isset($_REQUEST['print_sections']) ) {

	$entrant_category = $_REQUEST['entrant_category'];
	$currency = $_REQUEST['currency'];
	$digital_sections = $_REQUEST['digital_sections'];
	$print_sections = $_REQUEST['print_sections'];
	$fee_array = array();
	$max_fees = array();

	// Get the Maximum Fees for this fee_code
	// If a fee option matches both digital_sections and print_sections take it - It will be only for ALL_PRINT_DIGITAL
	$sql  = "SELECT fee_code, participation_code, fees, digital_sections, print_sections FROM fee_structure ";
	$sql .= "WHERE entrant_category = '$entrant_category' ";
	$sql .= "  AND currency = '$currency' ";
	$sql .= "  AND digital_sections != '0' ";
	$sql .= "  AND print_sections != '0' ";
	$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	while ($row = mysqli_fetch_array($query)) {
		// Get Discount
		$sql  = "SELECT discount FROM discount WHERE discount_code = 'CLUB' AND fee_code = '" . $row['fee_code'] . "' ";
		$sql .= " AND entrant_category = '$entrant_category' AND currency = '$currency' AND participation_code = '" . $row['participation_code'] . "' ";
		$tmpq = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($tmpq) > 0) {
			$tmpr = mysqli_fetch_array($tmpq);
			$discount = $tmpr['discount'];
		}
		else
			$discount = 0.0;
		$max_fees[$row['fee_code']] = array("participation_code" => $row['participation_code'], "fees" => $row['fees'], "discount" => $discount,
												"digital_sections" => $row['digital_sections'], "print_sections" => $row['print_sections']);
		if ($digital_sections == $row['digital_sections'] && $print_sections == $row['print_sections'])
			$fee_array[] = array ("fee_code" => $row['fee_code'], "participation_code" => $row['participation_code'], "fees" => $row['fees']);
	}
	if (sizeof($fee_array) == 0) {
		// Check Individually for digital_sections and print_sections
		if ($digital_sections > 0) {
			$sql  = "SELECT fee_code, participation_code, fees FROM fee_structure ";
			$sql .= "WHERE entrant_category = '$entrant_category' ";
			$sql .= "  AND currency = '$currency' ";
			$sql .= "  AND digital_sections = '$digital_sections' ";
			$sql .= "  AND print_sections = '0' ";
			$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) > 0) {
				while ($row = mysqli_fetch_array($query)) {
					$fee_array[] = array ("fee_code" => $row['fee_code'], "participation_code" => $row['participation_code'], "fees" => $row['fees']);
				}
			}
		}
		// Check Individually for digital_sections and print_sections
		if ($print_sections > 0) {
			$sql  = "SELECT fee_code, participation_code, fees FROM fee_structure ";
			$sql .= "WHERE entrant_category = '$entrant_category' ";
			$sql .= "  AND currency = '$currency' ";
			$sql .= "  AND print_sections = '$print_sections' ";
			$sql .= "  AND digital_sections = '0' ";
			$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			if (mysqli_num_rows($query) > 0) {
				while ($row = mysqli_fetch_array($query)) {
					$fee_array[] = array ("fee_code" => $row['fee_code'], "participation_code" => $row['participation_code'], "fees" => $row['fees']);
				}
			}
		}
	}

	$early_bird_fee = 0.0;
	$early_bird_discount = 0.0;
	$regular_fee = 0.0;
	$regular_discount = 0.0;

	//debug_dump("fee_arry", $fee_array, __FILE__, __LINE__);
	foreach ($fee_array as $fee_row) {
		// Get Discount
		$sql  = "SELECT discount FROM discount WHERE discount_code = 'CLUB' AND fee_code = '" . $fee_row['fee_code'] . "' ";
		$sql .= " AND entrant_category = '$entrant_category' AND currency = '$currency' AND participation_code = '" . $fee_row['participation_code'] . "' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0) {
			$row = mysqli_fetch_array($query);
			$discount = $row['discount'];
		}
		else
			$discount = 0.0;
		if ($fee_row['fee_code'] == "EARLY BIRD") {
			$early_bird_fee += $fee_row['fees'];
			$early_bird_discount += $discount;
		}
		else {
			$regular_fee += $fee_row['fees'];
			$regular_discount += $discount;
		}
	}

	$resArray = array();
	$resArray['success'] = true;
	$resArray['msg'] = '';

	if ($early_bird_fee >= $max_fees['EARLY BIRD']['fees'] ||
			($early_bird_fee - $early_bird_discount) >= ($max_fees['EARLY BIRD']['fees'] - $max_fees['EARLY BIRD']['discount']) ||
			$regular_fee >= $max_fees['REGULAR']['fees'] ||
			($regular_fee - $regular_discount) >= ($max_fees['REGULAR']['fees'] - $max_fees['REGULAR']['discount']) ) {
		// Raise to maximum
		if ($digital_sections != $max_fees['REGULAR']['digital_sections'] || $print_sections != $max_fees['REGULAR']['print_sections'])
			$resArray['msg'] = 'Changed the choices as it is cheaper to participate in all digital and print sections';
		$early_bird_fee = $max_fees['EARLY BIRD']['fees'];
		$early_bird_discount = $max_fees['EARLY BIRD']['discount'];
		$regular_fee = $max_fees['REGULAR']['fees'];
		$regular_discount = $max_fees['REGULAR']['discount'];
		$digital_sections = $max_fees['REGULAR']['digital_sections'];
		$print_sections = $max_fees['REGULAR']['print_sections'];
	}

	$resArray['early_bird_no_discount'] = $early_bird_fee;
	$resArray['early_bird_with_discount'] = $early_bird_fee - $early_bird_discount;
	$resArray['regular_no_discount'] = $regular_fee;
	$resArray['regular_with_discount'] = $regular_fee - $regular_discount;
	$resArray['digital_sections'] = $digital_sections;
	$resArray['print_sections'] = $print_sections;
	echo json_encode($resArray);
}
else {
	return_error("Invalid Request");
}
?>
