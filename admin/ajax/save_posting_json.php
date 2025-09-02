<?php
include("../inc/session.php");
include "../inc/connect.php";
include_once("ajax_lib.php");

if( isset($_SESSION['admin_id']) && isset($_REQUEST['save-posting-json']) && isset($_REQUEST['yearmonth']) ) {

	$yearmonth = $_REQUEST['yearmonth'];

	$target_folder = "../../salons/$yearmonth/blob";
	$json_file = $target_folder . "/" . "posting.json";

	// Assemble Remitances
	$remittances = [];
	if (isset($_REQUEST['remittance_data']) && sizeof($_REQUEST['remittance_data']) > 0) {
		foreach($_REQUEST['remittance_data'] as $data_json) {
			$remittances[] = json_decode($data_json, true);
		}
	}

	// Assemble Awards
	$awards = [];
	if (isset($_REQUEST['award_data']) && sizeof($_REQUEST['award_data']) > 0) {
		foreach($_REQUEST['award_data'] as $data_json) {
			$awards[] = json_decode($data_json, true);
		}
	}

	// Assemble Catalogs
	$catalogs = [];
	if (isset($_REQUEST['catalog_data']) && sizeof($_REQUEST['catalog_data']) > 0) {
		foreach($_REQUEST['catalog_data'] as $data_json) {
			$catalogs[] = json_decode($data_json, true);
		}
	}

	$posting = ["cash_remittances" => $remittances, "award_mailing" => $awards, "catalog_mailing" => $catalogs];

	// Save JSON File
	if (file_exists($json_file)) {
		$info = pathinfo($json_file);
		rename($json_file, $target_folder . "/" . $info['filename'] . "_" . date("YmdHi") . "." . $info['extension']);
	}
	file_put_contents($json_file, json_encode($posting));

	$resArray = [];
	$resArray['success'] = TRUE;
	$resArray['msg'] = "";
	$resArray['posting'] = $posting;
	echo json_encode($resArray);
	die();
}
else
	return_error("Invalid Update Request", __FILE__, __LINE__, true);

?>
