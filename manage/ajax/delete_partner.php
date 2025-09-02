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

function delete_partner_data($partner_data, $partner_idx) {
	if (! isset($partner_data['partners']))
		return $partner_data;

	// Copy everything other than the one that matches partner name
	$tmp_data = array("partners" => []);
	foreach ($partner_data['partners'] as $partner) {
		if ($partner['idx'] != $partner_idx)
			$tmp_data['partners'][] = $partner;
	}

	return $tmp_data;
}

function save_partner_data($yearmonth, $partner_data) {
	file_put_contents("../../salons/$yearmonth/blob/partners.json", json_encode($partner_data));
}

// Main Code

$resArray = array();

if( isset($_SESSION['admin_id']) && isset($_REQUEST['yearmonth']) && isset($_REQUEST['partner_idx']) ) {

	$yearmonth = $_REQUEST['yearmonth'];
	$partner_idx = $_REQUEST['partner_idx'];

	if ($partner_idx == "")
		return_error("Invalid Parameters", __FILE__, __LINE__, true);

	if (! $partner_data = get_partner_data($yearmonth))
		$partner_data['partners'] = [];
	elseif (! isset($partner_data['partners']))
		$partner_data['partners'] = [];

	// Delete existing data and append the updated data
	$partner_data = delete_partner_data($partner_data, $partner_idx);

	save_partner_data($yearmonth, $partner_data);

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
