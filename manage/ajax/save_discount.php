<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['update_discount'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$row_id = $_REQUEST['row_id'];
	$last_row_id = $_REQUEST['last_row_id'];
	// Keys
	$key_discount_code = $_REQUEST['key_discount_code'];
	$key_fee_code = $_REQUEST['key_fee_code'];
	$key_discount_group = $_REQUEST['key_discount_group'];
	$key_participation_code = $_REQUEST['key_participation_code'];
	$key_currency = $_REQUEST['key_currency'];
	$key_group_code = $_REQUEST['key_group_code'];
	// Data
	$discount_code = $_REQUEST['discount_code'];
	$fee_code = $_REQUEST['fee_code'];
	$discount_group = $_REQUEST['discount_group'];
	$participation_code = $_REQUEST['participation_code'];
	$currency = $_REQUEST['currency'];
	$group_code = $_REQUEST['group_code'];
	$minimum_group_size = $_REQUEST['minimum_group_size'];
	$maximum_group_size = $_REQUEST['maximum_group_size'];
	$discount_start_date = $_REQUEST['discount_start_date'];
	$discount_end_date = $_REQUEST['discount_end_date'];
	$discount = $_REQUEST['discount'];
	$discount_percentage = $_REQUEST['discount_percentage'] / 100;
	$discount_round_digits = $_REQUEST['discount_round_digits'];

	$is_edit_discount = ($_REQUEST['is_edit_discount'] == "1");

	// Duplicate Validation
	if ($key_discount_code != $discount_code || $key_fee_code != $fee_code || $key_discount_group != $discount_group ||
			$key_participation_code != $participation_code || $key_currency != $currency || $key_group_code != $group_code) {
		$sql  = "SELECT * FROM discount ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND discount_code = '$discount_code' ";
		$sql .= "   AND fee_code = '$fee_code' ";
		$sql .= "   AND discount_group = '$discount_group' ";
		$sql .= "   AND participation_code = '$participation_code' ";
		$sql .= "   AND currency = '$currency' ";
		$sql .= "   AND group_code = '$group_code' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0)
			return_error("There is already a discount record for the details provided.", __FILE__, __LINE__);
	}

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_discount) {
		$sql  = "UPDATE discount ";
		$sql .= "   SET discount_code = '$discount_code' ";
		$sql .= "     , fee_code = '$fee_code' ";
		$sql .= "     , discount_group = '$discount_group' ";
		$sql .= "     , participation_code = '$participation_code' ";
		$sql .= "     , currency = '$currency' ";
		$sql .= "     , group_code = '$group_code' ";
		$sql .= "     , minimum_group_size = '$minimum_group_size' ";
		$sql .= "     , maximum_group_size = '$maximum_group_size' ";
		$sql .= "     , discount_start_date = '$discount_start_date' ";
		$sql .= "     , discount_end_date = '$discount_end_date' ";
		$sql .= "     , discount = '$discount' ";
		$sql .= "     , discount_percentage = '$discount_percentage' ";
		$sql .= "     , discount_round_digits = '$discount_round_digits' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND discount_code = '$key_discount_code' ";
		$sql .= "   AND fee_code = '$key_fee_code' ";
		$sql .= "   AND discount_group = '$key_discount_group' ";
		$sql .= "   AND participation_code = '$key_participation_code' ";
		$sql .= "   AND currency = '$key_currency' ";
		$sql .= "   AND group_code = '$group_code' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to update disount data ", __FILE__, __LINE__);
	}
	else {
		// Insert section
		$sql  = "INSERT INTO discount (yearmonth, discount_code, fee_code, discount_group, participation_code, currency, ";
		$sql .= "            group_code, minimum_group_size, maximum_group_size, discount_start_date, discount_end_date, discount, ";
		$sql .= "            discount_percentage, discount_round_digits ) ";
		$sql .= " VALUES('$yearmonth', '$discount_code', '$fee_code', '$discount_group', '$participation_code', '$currency', ";
		$sql .= "        '$group_code', '$minimum_group_size', '$maximum_group_size', '$discount_start_date', '$discount_end_date', '$discount', ";
		$sql .= "        '$discount_percentage', '$discount_round_digits' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create discount data.", __FILE__, __LINE__);
	}

	// Load the inserted section
	$sql  = "SELECT * FROM discount ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND discount_code = '$discount_code' ";
	$sql .= "   AND fee_code = '$fee_code' ";
	$sql .= "   AND discount_group = '$discount_group' ";
	$sql .= "   AND participation_code = '$participation_code' ";
	$sql .= "   AND currency = '$currency' ";
	$sql .= "   AND group_code = '$group_code' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	if (! $is_edit_discount) {
		$row_id = $last_row_id;
		++ $last_row_id;
	}
	$html  = "<tr id='" . $row_id . "-row' class='discount-row' >";
	$html .= "    <td>" . $row['discount_code'] . "</td>";
	$html .= "    <td>" . $row['fee_code'] . "</td>";
	$html .= "    <td>" . $row['discount_group'] . "</td>";
	$html .= "    <td>" . $row['participation_code'] . "</td>";
	$html .= "    <td>" . $row['currency'] . "</td>";
	$html .= "    <td>" . $row['group_code'] . "</td>";
	$html .= "    <td>" . $row['minimum_group_size'] . "</td>";
	$html .= "    <td>" . $row['maximum_group_size'] . "</td>";
	$html .= "    <td>" . ( $row['discount'] > 0 ? $row['discount'] : ($row['discount_percentage'] * 100) . " %" ) . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info discount-edit-button' ";
	$html .= "                data-row-id='" . $row_id . "' ";
	$html .= "                data-discount='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger discount-delete-button' ";
	$html .= "                data-row-id='" . $row_id . "' ";
	$html .= "                data-discount='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-trash'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "</tr> ";

	$sql = "COMMIT";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['row_html'] = $html;
	$resArray['last_row_id'] = $last_row_id;
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>
