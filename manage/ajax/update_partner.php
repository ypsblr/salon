<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

function get_partner_data($yearmonth) {

	if (file_exists("../../salons/$yearmonth/blob/partners.json")) {
		// Load Partner Data
		$partner_data = json_decode(file_get_contents("../../salons/$yearmonth/blob/partners.json"), true);
		if (json_last_error() != JSON_ERROR_NONE) {
			$_SESSION['err_msg'] = "Partner Definition garbled";
			return false;
		}
		if (sizeof($partner_data['partners']) == 0)
			return false;		// Nothing to do

		return $partner_data;
	}
	else {
		return false;
	}
}

// Delete the partners that matches the name
function delete_partner_data($partner_data, $partner_idx) {
	if (! isset($partner_data['partners']))
		return $partner_data;

	// Copy everything other than the one that matches partner name
	$tmp_data = array("partners" => []);
	foreach ($partner_data['partners'] as $partner) {
		if ($partner['idx'] != $partner_idx) {
			$tmp_data['partners'][] = $partner;
		}
	}

	return $tmp_data;
}

function save_partner_data($yearmonth, $partner_data) {
	file_put_contents("../../salons/$yearmonth/blob/partners.json", json_encode($partner_data));
}

// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['edit-update']) && isset($_REQUEST['yearmonth']) ) {

	if ($_REQUEST['partner_name'] == "" || $_REQUEST['partner_text'] == "")
		return_error("Partner Name or Role is empty", __FILE__, __LINE__, true);

	if ($_REQUEST['prev_img'] == "" && (! isset($_FILES['partner_img'])))
		return_error("Partner Graphics is required", __FILE__, __LINE__, true);

	$yearmonth = $_REQUEST['yearmonth'];
	$prev_logo = $_REQUEST['prev_logo'];
	$prev_img = $_REQUEST['prev_img'];

	$target_folder = "../../salons/$yearmonth/img/sponsor";

	// Handle Logo Upload
	if (isset($_FILES) && $_FILES['partner_logo']['error'] != UPLOAD_ERR_NO_FILE) {
		if (! ($_FILES['partner_logo']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading logo. Try again", __FILE__, __LINE__, true);

		$target_file = $target_folder . "/" . $_FILES['partner_logo']['name'];

		if (! move_uploaded_file($_FILES['partner_logo']['tmp_name'], $target_file))
			return_error("Error in copying logo", __FILE__, __LINE__, true);

		$partner_logo = $_FILES['partner_logo']['name'];
	}
	else
		$partner_logo = $prev_logo;

	// Handle Image Upload
	if (isset($_FILES) && $_FILES['partner_img']['error'] != UPLOAD_ERR_NO_FILE) {
		if (! ($_FILES['partner_img']['error'] == UPLOAD_ERR_OK))
			return_error("Error in uploading graphics image. Try again", __FILE__, __LINE__, true);

		$target_file = $target_folder . "/" . $_FILES['partner_img']['name'];

		if (! move_uploaded_file($_FILES['partner_img']['tmp_name'], $target_file))
			return_error("Error in copying graphics", __FILE__, __LINE__, true);

		$partner_img = $_FILES['partner_img']['name'];
	}
	else
		$partner_img = $prev_img;

	$partner = array(
		"idx" => $_REQUEST['partner_idx'],
		"sequence" => $_REQUEST['partner_sequence'],
		"name" => $_REQUEST['partner_name'],
		"tagline" => $_REQUEST['partner_tagline'],
		"website" => $_REQUEST['partner_website'],
		"phone" => $_REQUEST['partner_phone'],
		"email" => $_REQUEST['partner_email'],
		"logo" => $partner_logo,
		"img" => $partner_img,
		"text" => $_REQUEST['partner_text']
	);

	if (! $partner_data = get_partner_data($yearmonth))
		$partner_data['partners'] = [];
	elseif (! isset($partner_data['partners']))
		$partner_data['partners'] = [];

	// Delete existing data and append the updated data
	if ($prev_partner_name != "")
		$partner_data = delete_partner_data($partner_data, $prev_partner_name);

	debug_dump("partners", $partner_data, __FILE__, __LINE__);

	$partner_data['partners'][] = $partner;

	save_partner_data($yearmonth, $partner_data);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['partner'] = $partner;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
