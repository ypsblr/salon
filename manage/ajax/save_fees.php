<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['update_fees'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$row_id = $_REQUEST['row_id'];
	$last_row_id = $_REQUEST['last_row_id'];
	// Keys
	$key_fee_code = $_REQUEST['key_fee_code'];
	$key_fee_group = $_REQUEST['key_fee_group'];
	$key_participation_code = $_REQUEST['key_participation_code'];
	$key_currency = $_REQUEST['key_currency'];
	// Data
	$fee_code = $_REQUEST['fee_code'];
	$fee_group = $_REQUEST['fee_group'];
	$participation_code = $_REQUEST['participation_code'];
	$currency = $_REQUEST['currency'];
	$description = mysqli_real_escape_string($DBCON, $_REQUEST['description']);
	$digital_sections = $_REQUEST['digital_sections'];
	$print_sections = $_REQUEST['print_sections'];
	$fee_start_date = $_REQUEST['fee_start_date'];
	$fee_end_date = $_REQUEST['fee_end_date'];
	$fees = $_REQUEST['fees'];
	$exclusive = $_REQUEST['exclusive'];

	$is_edit_fees = ($_REQUEST['is_edit_fees'] == "1");

	// Duplicate Validation
	if ($key_fee_code != $fee_code || $key_fee_group != $fee_group || $key_participation_code != $participation_code || $key_currency != $currency) {
		$sql  = "SELECT * FROM fee_structure ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND fee_code = '$fee_code' ";
		$sql .= "   AND fee_group = '$fee_group' ";
		$sql .= "   AND participation_code = '$participation_code' ";
		$sql .= "   AND currency = '$currency' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0)
			return_error("There is already a fee record for the details provided.", __FILE__, __LINE__);
	}

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_fees) {
		$sql  = "UPDATE fee_structure ";
		$sql .= "   SET fee_code = '$fee_code' ";
		$sql .= "     , fee_group = '$fee_group' ";
		$sql .= "     , participation_code = '$participation_code' ";
		$sql .= "     , currency = '$currency' ";
		$sql .= "     , description = '$description' ";
		$sql .= "     , digital_sections = '$digital_sections' ";
		$sql .= "     , print_sections = '$print_sections' ";
		$sql .= "     , fee_start_date = '$fee_start_date' ";
		$sql .= "     , fee_end_date = '$fee_end_date' ";
		$sql .= "     , fees = '$fees' ";
		$sql .= "     , exclusive = '$exclusive' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND fee_code = '$key_fee_code' ";
		$sql .= "   AND fee_group = '$key_fee_group' ";
		$sql .= "   AND participation_code = '$key_participation_code' ";
		$sql .= "   AND currency = '$key_currency' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to update fees data ", __FILE__, __LINE__);
	}
	else {
		// Insert section
		$sql  = "INSERT INTO fee_structure (yearmonth, fee_code, fee_group, participation_code, currency, description,  ";
		$sql .= "            digital_sections, print_sections, fee_start_date, fee_end_date, fees, exclusive ) ";
		$sql .= " VALUES('$yearmonth', '$fee_code', '$fee_group', '$participation_code', '$currency', '$description', ";
		$sql .= "        '$digital_sections', '$print_sections', '$fee_start_date', '$fee_end_date', '$fees', '1' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create fees data.", __FILE__, __LINE__);
	}

	// Load the inserted section
	$sql  = "SELECT * FROM fee_structure ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND fee_code = '$fee_code' ";
	$sql .= "   AND fee_group = '$fee_group' ";
	$sql .= "   AND participation_code = '$participation_code' ";
	$sql .= "   AND currency = '$currency' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	if (! $is_edit_fees) {
		$row_id = $last_row_id;
		++ $last_row_id;
	}
	$html  = "<tr id='" . $row_id . "-row' class='fees-row' >";
	$html .= "    <td>" . $row['fee_code'] . "</td>";
	$html .= "    <td>" . $row['fee_group'] . "</td>";
	$html .= "    <td>" . $row['participation_code'] . "</td>";
	$html .= "    <td>" . $row['digital_sections'] . "</td>";
	$html .= "    <td>" . $row['print_sections'] . "</td>";
	$html .= "    <td>" . $row['currency'] . "</td>";
	$html .= "    <td>" . $row['fees'] . "</td>";
	$html .= "    <td>" . $row['fee_start_date'] . "</td>";
	$html .= "    <td>" . $row['fee_end_date'] . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info fees-edit-button' ";
	$html .= "                data-row-id='" . $row_id . "' ";
	$html .= "                data-fees='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger fees-delete-button' ";
	$html .= "                data-row-id='" . $row_id . "' ";
	$html .= "                data-fees='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
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
