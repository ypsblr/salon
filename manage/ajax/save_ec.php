<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['update_ec'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$entrant_category = $_REQUEST['entrant_category'];
	$entrant_category_name = mysqli_real_escape_string($DBCON, $_REQUEST['entrant_category_name']);
	$yps_membership_required = isset($_REQUEST['yps_membership_required']) ? "1" : "0";
	if ($yps_membership_required == "1") {
		if (sizeof($_REQUEST['yps_member_prefixes']) == 0)
			return_error("You have not selected types of YPS members who can participate", __FILE__, __LINE__);
		$yps_member_prefixes = implode(",", $_REQUEST['yps_member_prefixes']);
	}
	else {
		$yps_member_prefixes = "";
	}
	$gender_must_match = isset($_REQUEST['gender_must_match']) ? "1" : "0";
	$gender_match = ($gender_must_match == "1") ? $_REQUEST['gender_match'] : "";
	$age_within_range = isset($_REQUEST['age_within_range']) ? "1" : "0";
	if ($age_within_range == "1") {
		$age_minimum = $_REQUEST['age_minimum'];
		$age_maximum = $_REQUEST['age_maximum'];
	}
	else {
		$age_minimum = "0";
		$age_maximum = "0";
	}
	$country_must_match = isset($_REQUEST['country_must_match']) ? "1" : "0";
	if ($country_must_match == "1") {
		if (empty($_REQUEST['country_codes']) && empty($_REQUEST['country_excludes']))
			return_error("Countries to be included/excluded not selected", __FILE__, __LINE__);
		$country_codes = isset($_REQUEST['country_codes']) ? implode(",", $_REQUEST['country_codes']) : "";
		$country_excludes = isset($_REQUEST['country_excludes']) ? implode(",", $_REQUEST['country_excludes']) : "";
	}
	else {
		$country_codes = "";
		$country_excludes = "";
	}
	$state_must_match = $_REQUEST['state_must_match'];
	$state_names = $_REQUEST['state_names'];
	$currency = $_REQUEST['currency'];
	$can_create_club = isset($_REQUEST['can_create_club']) ? "1" : "0";
	$fee_waived = isset($_REQUEST['fee_waived']) ? "1" : "0";
	$acceptance_reported = isset($_REQUEST['acceptance_reported']) ? "1" : "0";
	$award_group = $_REQUEST['award_group'];
	$fee_group = $_REQUEST['fee_group'];
	$default_participation_code = isset($_REQUEST['default_participation_code']) ? $_REQUEST['default_participation_code'] : "";
	$default_digital_sections = $_REQUEST['default_digital_sections'];
	$default_print_sections = $_REQUEST['default_print_sections'];
	$discount_group = isset($_REQUEST['discount_group']) ? $_REQUEST['discount_group'] : "";

	$is_edit_ec = ($_REQUEST['is_edit_ec'] != "0");

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_ec) {
		$sql  = "UPDATE entrant_category ";
		$sql .= "   SET entrant_category_name = '$entrant_category_name' ";
		$sql .= "     , yps_membership_required = '$yps_membership_required' ";
		$sql .= "     , yps_member_prefixes = '$yps_member_prefixes' ";
		$sql .= "     , gender_must_match = '$gender_must_match' ";
		$sql .= "     , gender_match = '$gender_match' ";
		$sql .= "     , age_within_range = '$age_within_range' ";
		$sql .= "     , age_minimum = '$age_minimum' ";
		$sql .= "     , age_maximum = '$age_maximum' ";
		$sql .= "     , country_must_match = '$country_must_match' ";
		$sql .= "     , country_codes = '$country_codes' ";
		$sql .= "     , country_excludes = '$country_excludes' ";
		$sql .= "     , state_must_match = '$state_must_match' ";
		$sql .= "     , state_names = '$state_names' ";
		$sql .= "     , currency = '$currency' ";
		$sql .= "     , can_create_club = '$can_create_club' ";
		$sql .= "     , fee_waived = '$fee_waived' ";
		$sql .= "     , acceptance_reported = '$acceptance_reported' ";
		$sql .= "     , award_group = '$award_group' ";
		$sql .= "     , fee_group = '$fee_group' ";
		$sql .= "     , default_participation_code = '$default_participation_code' ";
		$sql .= "     , default_digital_sections = '$default_digital_sections' ";
		$sql .= "     , default_print_sections = '$default_print_sections' ";
		$sql .= "     , discount_group = '$discount_group' ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND entrant_category = '$entrant_category' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add section.", __FILE__, __LINE__);
	}
	else {
		// Insert section
		$sql  = "INSERT INTO entrant_category (yearmonth, entrant_category, entrant_category_name, yps_membership_required, ";
		$sql .= "       yps_member_prefixes, gender_must_match, gender_match, age_within_range, age_minimum, age_maximum, ";
		$sql .= "       country_must_match, country_codes, country_excludes, state_must_match, state_names, ";
		$sql .= "       currency, can_create_club, fee_waived, acceptance_reported, award_group, fee_group, ";
		$sql .= "       default_participation_code, default_digital_sections, default_print_sections, discount_group ) ";
		$sql .= " VALUES('$yearmonth', '$entrant_category', '$entrant_category_name', '$yps_membership_required', ";
		$sql .= "        '$yps_member_prefixes', '$gender_must_match', '$gender_match', '$age_within_range', '$age_minimum', '$age_maximum', ";
		$sql .= "        '$country_must_match', '$country_codes', '$country_excludes', '$state_must_match', '$state_names', ";
		$sql .= "        '$currency', '$can_create_club', '$fee_waived', '$acceptance_reported', '$award_group', '$fee_group', ";
		$sql .= "        '$default_participation_code', '$default_digital_sections', '$default_print_sections', '$discount_group' ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to add section.", __FILE__, __LINE__);
	}

	$include_country_list = "INCLUDE(NONE)";
	$exclude_country_list = "EXCLUDE(NONE)";
	if ($country_must_match == '1') {
		// Include list
		if (! empty($country_codes)) {
			$sql  = "SELECT GROUP_CONCAT(country_name SEPARATOR ', ') AS country_list FROM country ";
			$sql .= " WHERE country_id IN (" . $country_codes . ") ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$include_country_list = "INCLUDE (" . $row['country_list'] . ")";
		}
		// Exclude List
		if (! empty($country_excludes)) {
			$sql  = "SELECT GROUP_CONCAT(country_name SEPARATOR ', ') AS country_list FROM country ";
			$sql .= " WHERE country_id IN (" . $country_excludes . ") ";
			$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
			$row = mysqli_fetch_array($query);
			$exclude_country_list = "EXCLUDE (" . $row['country_list'] . ")";
		}
	}

	// Load the inserted section
	$sql  = "SELECT * FROM entrant_category ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND entrant_category = '$entrant_category' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	$html  = "<tr id='" . $entrant_category . "-row' >";
	$html .= "    <td>" . $row['entrant_category_name'] . "</td>";
	$html .= "    <td>" . ($row['yps_membership_required'] == "1" ? "Must be " . $row['yps_member_prefixes'] : "Not Reqd") . "</td>";
	$html .= "    <td>" . ($row['gender_must_match'] == "1" ? "Must be " . $row['gender_match'] : "Any") . "</td>";
	$html .= "    <td>" . ($row['age_within_range'] == '1' ? "Between " . $row['age_minimum'] . " and " . $row['age_maximum'] : "Any age") . "</td>";
	$html .= "    <td>$include_country_list<br>$exclude_country_list</td>";
	$html .= "    <td>" . $row['award_group'] . "</td>";
	$html .= "    <td>" . ($row['fee_waived'] == "0" ? $row['fee_group'] : "FREE") . "</td>";
	$html .= "    <td>" . $row['discount_group'] . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info ec-edit-button' ";
	$html .= "                data-category='" . $row['entrant_category'] . "' ";
	$html .= "                data-ec='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger ec-delete-button' ";
	$html .= "                data-category='" . $row['entrant_category'] . "' ";
	$html .= "                data-ec='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
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
