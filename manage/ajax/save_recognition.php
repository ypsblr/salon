<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

// Main Code
if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['update_recognition'])) {

	$yearmonth = $_REQUEST['yearmonth'];
	$organization_name = mysqli_real_escape_string($DBCON, $_REQUEST['organization_name']);
	$short_code = strtoupper($_REQUEST['short_code']);
	$recognition_id = $_REQUEST['recognition_id'];
	$website = mysqli_real_escape_string($DBCON, $_REQUEST['website']);
	$small_logo = "";

	$prev_logo = $_REQUEST['logo'];
	$prev_notification = $_REQUEST['notification'];

	$logo = strtolower($short_code) . "_logo.jpg";
	$notification = strtolower($short_code) . "_notification.jpg";

	$notice = mysqli_real_escape_string($DBCON, $_REQUEST['notice']);

	$is_edit_recognition = ($_REQUEST['is_edit_recognition'] == "1");

	// Upload logo if specified
	if (isset($_FILES) && isset($_FILES["logo_file"]) && $_FILES['logo_file']['error'] != UPLOAD_ERR_NO_FILE) {
		// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
		$target_file = "../../salons/$yearmonth/img/recognition/$logo";
		if (! ($_FILES['logo_file']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading the logo. Try again", __FILE__, __LINE__, true);
		if (! move_uploaded_file($_FILES['logo_file']['tmp_name'], $target_file))
			return_error("Error in copying the logo", __FILE__, __LINE__, true);
	}
	else
		$logo = $prev_logo;	// Previous File

	// Upload notification if specified
	if (isset($_FILES) && isset($_FILES["notification_file"]) && $_FILES['notification_file']['error'] != UPLOAD_ERR_NO_FILE) {
		// debug_dump("FILES", $_FILES, __FILE__, __LINE__);
		$target_file = "../../salons/$yearmonth/img/recognition/$notification";
		if (! ($_FILES['notification_file']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading the notification. Try again", __FILE__, __LINE__, true);
		if (! move_uploaded_file($_FILES['notification_file']['tmp_name'], $target_file))
			return_error("Error in copying the notification", __FILE__, __LINE__, true);
	}
	else
		$notification = $prev_notification;	// Previous File

	// Validations
	// Check for Duplicate Section Names
	if ($is_edit_recognition) {
		$sql  = "SELECT * FROM recognition ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND short_code = '$short_code' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) == 0)
			return_error("Recognition for $short_code not found", __FILE__, __LINE__);
	}
	else {
		$sql  = "SELECT * FROM recognition ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND short_code = '$short_code' ";
		$query = mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_num_rows($query) > 0)
			return_error("There is already an entry for $short_code", __FILE__, __LINE__);
	}
	// Check for unique stub

	// Start Transaction
	$sql = "START TRANSACTION";
	mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);

	// Update section
	if ($is_edit_recognition) {
		$sql  = "UPDATE recognition ";
		$sql .= "   SET organization_name = '$organization_name' ";
		$sql .= "     , recognition_id = '$recognition_id' ";
		$sql .= "     , website = '$website' ";
		$sql .= "     , small_logo = '' ";
		$sql .= "     , logo = '$logo' ";
		$sql .= "     , notification = '$notification' ";
		$sql .= "     , description = '' ";
		$sql .= "     , notice = '$notice' ";
		$sql .= "     , rules = NULL ";
		$sql .= " WHERE yearmonth = '$yearmonth' ";
		$sql .= "   AND short_code = '$short_code' ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		// if (mysqli_affected_rows($DBCON) != 1)
		// 	return_error("Unable to update recognition data for $short_code.", __FILE__, __LINE__);
	}
	else {
		// Insert section
		$sql  = "INSERT INTO recognition (yearmonth, short_code, organization_name, recognition_id, website, small_logo,  ";
		$sql .= "            logo, notification, description, notice, rules ) ";
		$sql .= " VALUES('$yearmonth', '$short_code', '$organization_name', '$recognition_id', '$website', '', ";
		$sql .= "        '$logo', '$notification', '', '$notice', NULL ) ";
		mysqli_query($DBCON, $sql) or sql_json_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
		if (mysqli_affected_rows($DBCON) != 1)
			return_error("Unable to create recognition for $short_code.", __FILE__, __LINE__);
	}

	// Load the inserted section
	$sql  = "SELECT * FROM recognition ";
	$sql .= " WHERE yearmonth = '$yearmonth' ";
	$sql .= "   AND short_code = '$short_code' ";
	$query = mysqli_query($DBCON, $sql)or sql_error($sql, mysqli_error($DBCON), __FILE__, __LINE__);
	$row = mysqli_fetch_array($query, MYSQLI_ASSOC);

	// Generate table row html
	$html  = "<tr id='" . $row['short_code'] . "-row' >";
	$html .= "    <td>" . $row['short_code'] . "</td>";
	$html .= "    <td>" . $row['organization_name'] . "</td>";
	$html .= "    <td>" . $row['recognition_id'] . "</td>";
	$html .= "    <td>" . $row['website'] . "</td>";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-info recognition-edit-button' ";
	$html .= "                data-short-code='" . $row['short_code'] . "' ";
	$html .= "                data-recognition='" . json_encode($row, JSON_FORCE_OBJECT | JSON_HEX_APOS | JSON_HEX_QUOT) . "' >";
	$html .= "            <i class='fa fa-edit'></i> ";
	$html .= "        </button> ";
	$html .= "    </td> ";
	$html .= "    <td>";
	$html .= "        <button class='btn btn-danger recognition-delete-button' ";
	$html .= "                data-short-code='" . $row['short_code'] . "' ";
	$html .= "                data-recognition='" . json_encode($row, JSON_FORCE_OBJECT | JSON_HEX_APOS | JSON_HEX_QUOT) . "' >";
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
