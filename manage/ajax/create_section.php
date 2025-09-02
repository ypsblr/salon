<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['update_section'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$section = strtoupper($_REQUEST['section']);
	$stub = strtoupper($_REQUEST['stub']);
	$section_type = $_REQUEST['section_type'];
	$section_sequence = $_REQUEST['section_sequence'];
	$submission_last_date = $_REQUEST['submission_last_date'];
	$max_pics_per_entry = $_REQUEST['max_pics_per_entry'];
	// $rules = mysqli_real_escape_string($DBCON, $_REQUEST['rules']);
	$rules = "";		// In table rules is not supported any more
	$rules_blob = $_REQUEST['rules_blob'];
	if ($rules_blob == "")
		$rules_blob = "section_" . $stub . "_rules.htm";		// force default file name
	$is_edit_section = ($_REQUEST['is_edit_section'] != "0");


	// Validations
	// Check for Duplicate Section Names
	if ($is_edit_section) {
		$sql  = "SELECT * FROM section ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section != '$section' ";
		$sql .= "   AND stub = '$stub' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0)
			return_error("Stub is not unique", __FILE__, __LINE__);
	}
	else {
		$sql  = "SELECT * FROM section ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND (section = '$section' OR stub = '$stub') ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0)
			return_error("Section Name or Stub is not unique", __FILE__, __LINE__);
	}
	// Check for unique stub

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_section) {
		$sql  = "UPDATE section ";
		$sql .= "   SET stub = '$stub' ";
		$sql .= "     , section_type = '$section_type' ";
		$sql .= "     , section_sequence = '$section_sequence' ";
		$sql .= "     , submission_last_date = '$submission_last_date' ";
		$sql .= "     , max_pics_per_entry = '$max_pics_per_entry' ";
		$sql .= "     , rules = '$rules' ";
		$sql .= "     , rules_blob = '$rules_blob' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND section = '$section' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add section.", __FILE__, __LINE__);
	}
	else {
		// Insert section
		$sql  = "INSERT INTO section (yearmonth, section, stub, section_type, section_sequence, submission_last_date,  ";
		$sql .= "                     max_pics_per_entry, rules, rules_blob ) ";
		$sql .= " VALUES('$yearmonth', '$section', '$stub', '$section_type', '$section_sequence', '$submission_last_date', ";
		$sql .= "        '$max_pics_per_entry', '$rules', '$rules_blob' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add section.", __FILE__, __LINE__);
	}

	// Load the inserted section
	$sql  = "SELECT yearmonth, section, section_type, section_sequence, stub, description, rules, rules_blob, ";
	$sql .= "       submission_last_date, max_pics_per_entry ";
	$sql .= "  FROM section ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND section = '$section' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	$html  = "<tr id='" . $row['stub'] . "-row' >";
	$html .= "    <td>" . $row['section_sequence'] . "</td>";
	$html .= "    <td>" . $row['section'] . "</td>";
	$html .= "    <td>" . ($row['section_type'] == "P" ? "Print" : "Digital") . "</td>";
	$html .= "    <td>" . $row['submission_last_date'] . "</td>";
	$html .= "    <td>" . $row['max_pics_per_entry'] . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info section-edit-button' ";
	$html .= "                data-stub='" . $row['stub'] . "' ";
	$html .= "                data-section='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger section-delete-button' ";
	$html .= "                data-stub='" . $row['stub'] . "' ";
	$html .= "                data-section='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
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
	echo json_encode($resArray);
	die();

}
else
	return_error("Invalid Request", __FILE__, __LINE__, true);

?>
