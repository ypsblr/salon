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

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['award_id']) && isset($_REQUEST['update_award'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$award_id = $_REQUEST['award_id'];
	$section = $_REQUEST['section'];
	$level = $_REQUEST['level'];
	$sequence = $_REQUEST['sequence'];
	$award_group = $_REQUEST['award_group'];
	$award_type = $_REQUEST['award_type'];
	$award_name = $_REQUEST['award_name'];
	$recognition_code = $_REQUEST['recognition_code'];
	$description = mysqli_real_escape_string($DBCON, $_REQUEST['description']);
	$number_of_awards = $_REQUEST['number_of_awards'];
	$award_weight = $_REQUEST['award_weight'];
	$has_medal = isset($_REQUEST['has_medal']) ? "1" : "0";
	$has_pin = isset($_REQUEST['has_pin']) ? "1" : "0";
	$has_ribbon = isset($_REQUEST['has_ribbon']) ? "1" : "0";
	$has_memento = isset($_REQUEST['has_memento']) ? "1" : "0";
	$has_gift = isset($_REQUEST['has_gift']) ? "1" : "0";
	$has_certificate = isset($_REQUEST['has_certificate']) ? "1" : "0";
	$cash_award = $_REQUEST['cash_award'];
	$sponsored_awards = $_REQUEST['sponsored_awards'];
	$sponsorship_per_award = $_REQUEST['sponsorship_per_award'];
	$partial_sponsorship_permitted = isset($_REQUEST['partial_sponsorship_permitted']) ? "1" : "0";
	$sponsorship_last_date = null_safe_date($_REQUEST['sponsorship_last_date']);

	$is_edit_award = ($_REQUEST['is_edit_award'] == "1");

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_award) {
		$sql  = "UPDATE award ";
		$sql .= "   SET level = '$level' ";
		$sql .= "     , sequence = '$sequence' ";
		$sql .= "     , section = '$section' ";
		$sql .= "     , award_group = '$award_group' ";
		$sql .= "     , award_type = '$award_type' ";
		$sql .= "     , award_name = '$award_name' ";
		$sql .= "     , recognition_code = '$recognition_code' ";
		$sql .= "     , description = '$description' ";
		$sql .= "     , number_of_awards = '$number_of_awards' ";
		$sql .= "     , award_weight = '$award_weight' ";
		$sql .= "     , has_medal = '$has_medal' ";
		$sql .= "     , has_pin = '$has_pin' ";
		$sql .= "     , has_ribbon = '$has_ribbon' ";
		$sql .= "     , has_memento = '$has_memento' ";
		$sql .= "     , has_gift = '$has_gift' ";
		$sql .= "     , has_certificate = '$has_certificate' ";
		$sql .= "     , cash_award = '$cash_award' ";
		$sql .= "     , sponsored_awards = '$sponsored_awards' ";
		$sql .= "     , sponsorship_per_award = '$sponsorship_per_award' ";
		$sql .= "     , partial_sponsorship_permitted = '$partial_sponsorship_permitted' ";
		$sql .= "     , sponsorship_last_date = $sponsorship_last_date ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND award_id = '$award_id' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to update award $award_name.", __FILE__, __LINE__);
	}
	else {
		// Find the last award id
		$sql = "SELECT MAX(award_id) AS last_award_id FROM award WHERE yearmonth = '$yearmonth' AND section = '$section' ";
		$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		$row = mysqli_fetch_array($query);
		$award_id = $row['last_award_id'] + 1;

		// Insert section
		$sql  = "INSERT INTO award (yearmonth, award_id, level, sequence, section, award_group, award_type, award_name, ";
		$sql .= "            recognition_code, description, number_of_awards, award_weight, has_medal, has_pin, has_ribbon, ";
		$sql .= "            has_memento, has_gift, has_certificate, cash_award, sponsored_awards, sponsorship_per_award, ";
		$sql .= "            partial_sponsorship_permitted, sponsorship_last_date) ";
		$sql .= "VALUES ('$yearmonth', '$award_id', '$level', '$sequence', '$section', '$award_group', '$award_type', '$award_name', ";
		$sql .= "       '$recognition_code', '$description', '$number_of_awards', '$award_weight', '$has_medal', '$has_pin', '$has_ribbon', ";
		$sql .= "       '$has_memento', '$has_gift', '$has_certificate', '$cash_award', '$sponsored_awards', '$sponsorship_per_award', ";
		$sql .= "       '$partial_sponsorship_permitted', $sponsorship_last_date) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create the award $award_name.", __FILE__, __LINE__);
	}

	// Load the inserted section
	$sql  = "SELECT * FROM award ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND award_id = '$award_id' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	$html  = "<tr id='" . $row['award_id'] . "-row' >";
	$html .= "    <td>" . $row['award_name'] . "</td>";
	$html .= "    <td>" . $row['recognition_code'] . "</td>";
	$html .= "    <td>" . $row['number_of_awards'] . "</td>";
	$html .= "    <td>" . $row['sponsored_awards'] . "</td>";
	$html .= "    <td>" . $row['cash_award'] . "</td>";
	$html .= "    <td>" . $row['sponsorship_per_award'] . "</td>";
	$html .= "    <td>" . $row['award_group'] . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info award-edit-button' ";
	$html .= "                data-award-id='" . $row['award_id'] . "' ";
	$html .= "                data-award='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger award-delete-button' ";
	$html .= "                data-award-id='" . $row['award_id'] . "' ";
	$html .= "                data-award='" . json_encode($row, JSON_FORCE_OBJECT) . "' >";
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
